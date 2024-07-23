<?php

namespace Telepedia\UserProfileV2;

use Article;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Override;

class UserPage extends Article {

    /** @var string */
    const USER_PROFILE_EDIT_PENCIL = '<?xml version="1.0" encoding="UTF-8"?><svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M14.3632 5.65156L15.8431 4.17157C16.6242 3.39052 17.8905 3.39052 18.6716 4.17157L20.0858 5.58579C20.8668 6.36683 20.8668 7.63316 20.0858 8.41421L18.6058 9.8942M14.3632 5.65156L4.74749 15.2672C4.41542 15.5993 4.21079 16.0376 4.16947 16.5054L3.92738 19.2459C3.87261 19.8659 4.39148 20.3848 5.0115 20.33L7.75191 20.0879C8.21972 20.0466 8.65806 19.8419 8.99013 19.5099L18.6058 9.8942M14.3632 5.65156L18.6058 9.8942" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

    /** @var User|null */
    public User $mUserProfile;

    /** @var User */
    public User $mViewer;

    /** @var bool */
    private bool $mIsOwner;

    /** @var mixed|\IContextSource */
    private mixed $context;

    public function __construct(Title $title) {
        $userFactory = MediaWikiServices::getInstance()->getUserFactory();
        $this->mUserProfile = $userFactory->newFromName($title->getBaseText());

        $this->context = $this->getContext();
        $this->mViewer = $this->context->getUser();

        $this->mIsOwner = ($this->mUserProfile->getId() == $this->mViewer->getId());
        parent::__construct($title);
    }

    /**
     * is the user viewing their own profile?
     * @return bool
     */
    public function isOwner(): bool {
        return $this->mIsOwner;
    }

    #[Override]
    /**
     * The main function to show our page
     * @return void
     * @throws \PermissionsError
     */
    public function view() {
        $con = $this->getContext();
        $output = $con->getOutput();
        $output->enableOOUI();

        // if this user doesn't exist, bail early without loading our css or js
        // and just show the regular 'user doesn't exist' message from core.
        if (!$this->mUserProfile->getUser()->isRegistered()) {
            parent::view();
            return;
        }


        $userInformation = new UserInformation($this->mUserProfile);

        // set the page title
        $output->setPageTitle($this->getTitle()->getPrefixedText());
        $output->addModuleStyles(['ext.userProfileV2.styles']);
        $output->addModules(['ext.userProfileV2.edit']);

        $output->addHTML($this->getProfileLayout($userInformation));
        parent::view(); // add the default contents of the userpage back under the header
    }

    /**
     * Get the actual layout for the profile
     *
     * @param $userInformation
     * @return string
     */
    private function getProfileLayout(UserInformation $userInformation) {
        $html = Html::openElement(
            "div",
            ["class" => "profile-masthead"]
        );
        $html .= Html::openElement(
            "div",
            ["class" => "profile-wrapper"]
        );
        $html .= Html::openElement(
            "section",
            ["class" => "profile-identitybox"]
        );
        $html .= Html::openElement(
            "div",
            ["class" => "profile-avatar"]
        );
        $html .= Html::element(
            "img",
            ["class" => "profile-avatar-image", "src" => $userInformation->getAvatarForUserProfile()]
        );
        if ($this->canEditProfile()) {
            $html .= Html::openElement(
                "div",
                ["class" => "profile-avatar-edit-action"]
            );
            $html .= Html::rawElement(
                "button",
                ["class" => "profile-avatar-edit-button"],
                self::USER_PROFILE_EDIT_PENCIL
            );
            $html .= Html::closeElement(
                'div'
            );
        }
        $html .= Html::closeElement("div");
        $html .= Html::openElement(
            "div",
            ["class" => "profile-information"]
        );

        // begin the header (where the username, groups etc are)
        $html .= Html::openElement(
            "div",
            ["class" => "profile-header"]
        );
        // user groups etc
        $html .= Html::openElement(
            "div",
            ["class" => "profile-header-attributes"]
        );
        $html .= Html::element(
            "h1",
            [],
            $this->mUserProfile->getName()
        );

        if ($this->mUserProfile->getRealName()) {
            $html .= Html::element(
                "h2",
                [],
                "aka {$this->mUserProfile->getRealName()}"
            );
        }

        $groups = $userInformation->getUserGroups($userInformation->shouldShowGlobalGroups());

        if (count($groups) > 0) {
            foreach ($groups as $group) {
                $html .= Html::element(
                    "span",
                    ['class' => 'profile-user-group'],
                    $this->context->msg("group-{$group}-member")
                );
            }
        }

        $html .= Html::closeElement("div");

        // edit button
        $html .= Html::openElement(
            "div",
            ["class" => "profile-header-actions"]
        );
        if ($this->canEditProfile()) {
            $html .= Html::element(
                "button",
                ["class" => "mw-ui-button mw-ui-progressive", "id" => 'userProfileV2-edit'],
                "Edit"
            );
        }
        $html .= Html::closeElement("div");
        $html .= Html::closeElement("div");

        // edit count etc
        $html .= Html::openElement(
            "ul",
            ['class' => 'profile-header-statistics']
        );
        $html .= Html::rawElement(
            "li",
            [],
            "<strong>{$this->mUserProfile->getEditCount()}</strong> edits"
        );

        $globalEditCount = $userInformation->getGlobalEditCount();

        if ($userInformation->shouldShowGlobalEditCount() && !is_null($globalEditCount)) {
            $html .= Html::rawElement(
                "li",
                [],
                "<strong>{$globalEditCount}</strong> global edits"
            );
        }

        $html .= Html::closeElement("ul");

        if (count($userInformation->getProfileLinks()) > 0) {

            $html .= Html::openElement(
                "div",
                ["class" => 'profile-externalLinks'],
            );

            foreach ($userInformation->getProfileLinks() as $externalService => $link) {
                $html .= $link;
            }

            $html .= Html::closeElement("div");
        }
        $html .= Html::element(
            "div",
            ['class' => 'profile-header-about'],
            $userInformation->getUserBiography()
        );
        $html .= Html::closeElement("div");
        $html .= Html::closeElement("div");
        $html .= Html::closeElement("div");
        // END Profile header
        $html .= Html::closeElement("div");

        // END profile information section
        $html .= Html::closeElement("section");

        if ($userInformation->isLocked()) {
            $html .= Html::warningBox(
                $this->getContext()->msg('userprofilev2-user-locked')->plain()
            );
        }

        /**
         * Run our hook which modifies the output after the masthead and before the contents.
         */
        $hookRunner = MediaWikiServices::getInstance()->get('UserProfileV2HookRunner');
        $hookRunner->onUserProfileV2OnProfileAfterMasthead($this->mUserProfile, $html);

        return $html;
    }

    /**
     * Can the current user edit this profile?
     * @return bool
     */
    private function canEditProfile(): bool {
        if ($this->mIsOwner) {
            return true;
        }

        $permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
        $userHasPermission = $permissionManager->userHasRight($this->mViewer, 'profilemanager');

        if ($userHasPermission) {
            return true;
        }

        return false;
    }
}
