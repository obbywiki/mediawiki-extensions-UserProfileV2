<?php

namespace Telepedia\UserProfileV2\Avatar;

use Exception;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use ObjectCache;
use UploadFromFile;

class UploadAvatar extends UploadFromFile {

    public $mExtension;

    public function performUpload($comment, $pageText, $watch, $user, $tags = [], ?string $watchlistExpiry = null) {
        $wgAvatarKey = 'avatar';

        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('UserProfileV2');

        $cacheType = $config->get('UserProfileV2CacheType');

        $cache = $cacheType ? ObjectCache::getInstance($cacheType) : ObjectCache::getLocalClusterInstance();

        /**
         * The file is empty, return a fatal
         */
        $imageInfo = getimagesize($this->mTempPath);
        if (empty($imageInfo[2])) {
            return Status::newFatal('empty-file');
        }

        switch ($imageInfo[2]) {
            case 1:
                $ext = 'gif';
                break;
            case 2:
                $ext = 'jpg';
                break;
            case 3:
                $ext = 'png';
                break;
            default:
                return Status::newFatal('filetype-banned');
        }

        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('UserProfileV2');

        if ($config->get('UserProfileV2UseGlobalAvatars')) {
            $caUser = CentralAuthUser::getPrimaryInstanceByName($user->getName());
            $userId = $caUser->getId();
        } else {
            $userId = $user->getId();
        }


        $userProfileAvatar = new UserProfileV2Avatar($userId);

        $this->createThumbnail($this->mTempPath, $imageInfo, $wgAvatarKey . '_' . $userId, 138);

        $extensions = ['jpg', 'gif', 'png', 'jpeg', 'webp'];

        foreach ($extensions as $fileExt) {
            if ($ext != $fileExt) {
                $filePath = wfTempDir() . "/{$wgAvatarKey}_{$userId}.{$fileExt}";
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $avatarBackend = new UserProfileV2AvatarBackend('upv2avatars');

        foreach (['gif', 'jpg', 'jpeg', 'png'] as $fileExtension) {
            if ($fileExtension === $ext) {
                // Our brand new avatar; skip over it in order to _not_ delete it, obviously
            } else {
                if ($avatarBackend->fileExists($wgAvatarKey . '_', $userId, $fileExtension)) {
                    $avatarBackend->getFileBackend()->quickDelete([
                        'src' => $avatarBackend->getPath($wgAvatarKey . '_', $userId, $fileExtension)
                    ]);
                }
            }
        }

        if ($config->get('UserProfileV2UseGlobalAvatars')) {
            $key = $cache->makeGlobalKey('user', 'userprofilev2', 'avatar', $userId);
        } else {
            $key = $cache->makeKey('user', 'userprofilev2', 'avatar', $userId);
        }

        $cache->delete($key);

        $this->mExtension = $ext;

        return Status::newGood();
    }

    public function checkWarnings($user = null) {
        return [];
    }

    private function createThumbnail($imageSrc, $imageInfo, $imgDest, $thumbWidth) {
        $backend = new UserProfileV2AvatarBackend('upv2avatars');
        $fname = $backend->getContainerStoragePath();

        $fileBackend = $backend->getFileBackend();
        $status = $fileBackend->prepare(['dir' => $fname]);

        if (!$status->isOK()) {
            throw new Exception(
                wfMessage('backend-fail-internal', Status::wrap($status)->getWikitext())
            );
        }

        // shamefully copied from SocialProfile

        global $wgUseImageMagick, $wgImageMagickConvertCommand;

        if ($wgUseImageMagick) { // ImageMagick is enabled
            [$origWidth, $origHeight, $typeCode] = $imageInfo;

            if ($origWidth < $thumbWidth) {
                $thumbWidth = $origWidth;
            }
            $thumbHeight = ($thumbWidth * $origHeight / $origWidth);
            $border = ' -bordercolor white  -border  0x';
            if ($thumbHeight < $thumbWidth) {
                $border = ' -bordercolor white  -border  0x' . (($thumbWidth - $thumbHeight) / 2);
            }
            if ($typeCode == 2) {
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0   -quality 100 ' . $border . ' ' .
                    $imageSrc . ' ' . wfTempDir() . '/' . $imgDest . '.jpg'
                );

                $status = $fileBackend->quickStore([
                    'src' => wfTempDir() . '/' . $imgDest . '.jpg',
                    'dst' => $fname . '/' . $imgDest . '.jpg'
                ]);

                if (!$status->isOK()) {
                    throw new Exception(
                        wfMessage('backend-fail-internal', Status::wrap($status)->getWikitext())
                    );
                }
            }
            if ($typeCode == 1) {
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0 ' . $imageSrc . ' ' . $border . ' ' .
                    wfTempDir() . '/' . $imgDest . '.gif'
                );

                $status = $fileBackend->quickStore([
                    'src' => wfTempDir() . '/' . $imgDest . '.gif',
                    'dst' => $fname . '/' . $imgDest . '.gif'
                ]);

                if (!$status->isOK()) {
                    throw new Exception(
                        wfMessage('backend-fail-internal', Status::wrap($status)->getWikitext())
                    );
                }
            }
            if ($typeCode == 3) {
                exec(
                    $wgImageMagickConvertCommand . ' -size ' . $thumbWidth . 'x' . $thumbWidth .
                    ' -resize ' . $thumbWidth . ' -crop ' . $thumbWidth . 'x' .
                    $thumbWidth . '+0+0 ' . $imageSrc . ' ' .
                    wfTempDir() . '/' . $imgDest . '.png'
                );

                $status = $fileBackend->quickStore([
                    'src' => wfTempDir() . '/' . $imgDest . '.png',
                    'dst' => $fname . '/' . $imgDest . '.png'
                ]);

                if (!$status->isOK()) {
                    throw new Exception(
                        wfMessage('backend-fail-internal', Status::wrap($status)->getWikitext())
                    );
                }
            }
        } else { // ImageMagick is not enabled, so fall back to PHP's GD library
            // Get the image size, used in calculations later.
            [$origWidth, $origHeight, $typeCode] = getimagesize($imageSrc);

            $fullImage = '';
            $ext = '';

            switch ($typeCode) {
                case '1':
                    $fullImage = imagecreatefromgif($imageSrc);
                    $ext = 'gif';
                    break;
                case '2':
                    $fullImage = imagecreatefromjpeg($imageSrc);
                    $ext = 'jpg';
                    break;
                case '3':
                    $fullImage = imagecreatefrompng($imageSrc);
                    $ext = 'png';
                    break;
            }

            $scale = ($thumbWidth / $origWidth);

            // Create our thumbnail size, so we can resize to this, and save it.
            $tnImage = imagecreatetruecolor(
                $origWidth * $scale,
                $origHeight * $scale
            );

            // Resize the image.
            imagecopyresampled(
                $tnImage,
                $fullImage,
                0, 0, 0, 0,
                $origWidth * $scale,
                $origHeight * $scale,
                $origWidth,
                $origHeight
            );

            // Create a new image thumbnail.
            if ($typeCode == 1) {
                imagegif($tnImage, $imageSrc);
            } elseif ($typeCode == 2) {
                imagejpeg($tnImage, $imageSrc);
            } elseif ($typeCode == 3) {
                imagepng($tnImage, $imageSrc);
            }

            // Clean up.
            imagedestroy($fullImage);
            imagedestroy($tnImage);

            // Copy the thumb
            copy(
                $imageSrc,
                wfTempDir() . '/' . $imgDest . '.' . $ext
            );

            $status = $fileBackend->quickStore([
                'src' => wfTempDir() . '/' . $imgDest . '.' . $ext,
                'dst' => $fname . '/' . $imgDest . '.' . $ext
            ]);

            if (!$status->isOK()) {
                throw new Exception(
                    wfMessage('backend-fail-internal', Status::wrap($status)->getWikitext())
                );
            }
        }
    }

}
