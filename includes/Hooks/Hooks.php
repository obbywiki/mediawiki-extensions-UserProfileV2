<?php

namespace Telepedia\UserProfileV2\Hooks;

use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticleFromTitleHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use Telepedia\UserProfileV2\UserPage;

class Hooks implements
	ArticleFromTitleHook,
	GetPreferencesHook
{

	/**
	 * @inheritDoc
	 */
	public function onArticleFromTitle( $title, &$article, $context ) {
		$userNameUtils = MediaWikiServices::getInstance()->getUserNameUtils();
		$pageTitle = $title->getText();

		if ( $title->inNamespaces( [ NS_USER, NS_USER_TALK ] ) && !$title->isSubpage() &&
			$userNameUtils->isUsable( $pageTitle ) ) {
			$article = new UserPage( $title );
		}
	}

	/**
	 * Add our preferences to Special:Preferences
	 * @param $user
	 * @param &$preferences
	 * @return void
	 */
	public function onGetPreferences( $user, &$preferences ): void {
		// add the about me preference
		$preferences['profile-aboutme'] = [
			'class' => 'HTMLTextAreaField',
			'label-message' => 'aboutme',
			'section' => 'profile',
			'rows' => 6,
			'maxlength' => 200,
			'placeholder' => wfMessage( 'aboutmeplaceholder' )->plain(),
			'help-message' => 'aboutmehelp',
		];

		$preferences['profile-discord'] = [
			'class' => 'HTMLTextField',
			'label-message' => 'discord',
			'section' => 'profile',
			'placeholder' => wfMessage( 'discordplaceholder' )->plain(),
			'help-message' => 'discordhelp',
		];

		$preferences['profile-twitter'] = [
			'class' => 'HTMLTextField',
			'label-message' => 'twitter',
			'section' => 'profile',
			'placeholder' => wfMessage( 'twitterplaceholder' )->plain(),
			'help-message' => 'twitterhelp',
		];

		$preferences['profile-mastodon'] = [
			'class' => 'HTMLTextField',
			'label-message' => 'mastodon',
			'section' => 'profile',
			'placeholder' => wfMessage( 'mastodonplaceholder' )->plain(),
			'help-message' => 'mastodonhelp',
		];

		$preferences['profile-show-globaledits'] = [
			'class' => 'HTMLCheckField',
			'label' => "Show my global edit count on my userpage",
			'section' => 'profile'
		];

		$preferences['profile-show-globalgroups'] = [
			'class' => 'HTMLCheckField',
			'label' => "Show my global user groups on my userpage",
			'section' => 'profile'
		];
	}
}
