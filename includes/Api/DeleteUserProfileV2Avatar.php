<?php

namespace Telepedia\UserProfileV2\Api;

use ApiMain;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use ObjectCache;
use Telepedia\UserProfileV2\Avatar\UserProfileV2AvatarBackend;
use Wikimedia\ParamValidator\ParamValidator;

class DeleteUserProfileV2Avatar extends \ApiBase {

	public function __construct(ApiMain $apiMain, $moduleName, UserFactory $userFactory, PermissionManager $permissionManager) {
		parent::__construct($apiMain, $moduleName);
		$this->userFactory = $userFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$params = $this->extractRequestParams();

		$targetUser = $this->getTargetUser($params['username']);

		$user = $this->getUser();

		if (!$user->isRegistered()) {
			$this->dieWithError(['apierror-notregistered']);
		}

		$canRemoveAvatar = $this->checkPermissions($user, $targetUser);

		if (!$canRemoveAvatar) {
			$this->dieWithError(['apierror-cannotremoveavatar', wfEscapeWikiText($targetUser->getName())]);
		}

		$avatarKey = 'avatar';

		$backend = new UserProfileV2AvatarBackend('avatars');

		$extensions = ['png', 'gif', 'jpg', 'jpeg', 'webp'];

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('UserProfileV2');

		if ($config->get('UserProfileV2UseGlobalAvatars')) {
			$caUser = CentralAuthUser::getPrimaryInstanceByName($targetUser->getName());
			$userId = $caUser->getId();
		} else {
			$userId = $user->getId();
		}


		foreach ($extensions as $ext) {
			if ($backend->fileExists($avatarKey . '_', $userId, $ext)) {
				$backend->getFileBackend()->quickDelete([
					'src' => $backend->getPath($avatarKey . '_', $userId, $ext)
				]);
			}
		}

		// delete all the data from the cache for this user, so that the default avatar is loaded on next profile
		// view
		$cache = ObjectCache::getLocalClusterInstance();
		$key = $cache->makeKey('user', 'profile', 'avatar', $userId);
		$cache->delete($key);

		$this->getResult()->addValue(null, $this->getModuleName(), ['status' => 'OK']);
	}

	/**
	 * Check whether a user can remove an avatar
	 * This should work but my head is going to explode thinking about it so
	 * @param User $user the user performing the action
	 * @param User $targetUser the user they're trying to perform it on
	 * @return bool
	 */
	private function checkPermissions(User $user, User $targetUser): bool {
		$userIsSame = $user->getId() == $targetUser->getId();

		// if the user has the profilemanager permission, they can remove an avatar
		// lets not bother with any of the other checks, it doesn't matter
		if ($this->permissionManager->userHasRight($targetUser, 'profilemanager')) {
			return true;
		}

		// if the user is trying to remove their own avatar, that is fine.
		// as long as they are not blocked.
		if ($userIsSame && !$user->getBlock()) {
			return true;
		}

		return false;
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'username' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			],
		];
	}

	private function getTargetUser(string $username): User {
		$user = $this->userFactory->newFromName($username);

		if (!$user->isRegistered()) {
			$this->dieWithError(['apierror-invalidusername', wfEscapeWikiText($username)]);
		}

		return $user;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return true;
	}
}
