<?php

namespace Telepedia\UserProfileV2\Hooks;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticleFromTitleHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use Telepedia\UserProfileV2\UserPage;

class Hooks implements
    ArticleFromTitleHook,
    GetPreferencesHook {

    /**
     * @inheritDoc
     */
    public function onArticleFromTitle($title, &$article, $context) {
        $userNameUtils = MediaWikiServices::getInstance()->getUserNameUtils();
        $pageTitle = $title->getText();

        if ($title->inNamespaces([NS_USER, NS_USER_TALK]) && !$title->isSubpage() &&
            $userNameUtils->isUsable($pageTitle)) {
            $article = new UserPage($title);
        }
    }

    /**
     * Add our preferences to Special:Preferences
     * @param $user
     * @param &$preferences
     * @return void
     */
    public function onGetPreferences($user, &$preferences): void {
        // add the about me preference
        $preferences['profile-aboutme'] = [
            'type' => 'textarea',
            'label-message' => 'aboutme',
            'section' => 'personal/profile',
            'rows' => 6,
            'maxlength' => 200,
            'placeholder' => wfMessage('aboutmeplaceholder')->plain(),
            'help-message' => 'aboutmehelp',
        ];

        $preferences['profile-discord'] = [
            'type' => 'text',
            'label-message' => 'discord',
            'section' => 'personal/profile',
            'placeholder' => wfMessage('discordplaceholder')->plain(),
            'help-message' => 'discordhelp',
        ];

        $preferences['profile-twitter'] = [
            'type' => 'text',
            'label-message' => 'twitter',
            'section' => 'personal/profile',
            'placeholder' => wfMessage('twitterplaceholder')->plain(),
            'help-message' => 'twitterhelp',
        ];

        $preferences['profile-mastodon'] = [
            'type' => 'text',
            'label-message' => 'mastodon',
            'section' => 'personal/profile',
            'placeholder' => wfMessage('mastodonplaceholder')->plain(),
            'help-message' => 'mastodonhelp',
        ];

        if (ExtensionRegistry::getInstance()->isLoaded('CentralAuth')) {

            $preferences['profile-show-globaledits'] = [
                'type' => 'check',
                'label' => "Show my global edit count on my userpage",
                'section' => 'personal/profile'
            ];

            $preferences['profile-show-globalgroups'] = [
                'type' => 'check',
                'label' => "Show my global user groups on my userpage",
                'section' => 'personal/profile'
            ];

        }
    }
}
