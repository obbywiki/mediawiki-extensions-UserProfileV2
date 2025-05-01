<?php

namespace Telepedia\UserProfileV2;

use ExtensionRegistry;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use MediaWiki\User\UserOptionsManager;
use Telepedia\UserProfileV2\Avatar\UserProfileV2Avatar;

class UserInformation {

	private static $preferences = [
		'profile-aboutme',
		'profile-show-globalgroups',
		'profile-show-globaledits',
		'profile-roblox',
		'profile-discord',
		'profile-twitter',
		'profile-bsky',
		'profile-github',
		'profile-url'
		// 'profile-mastodon'
	];

	private static $externalLinks = [
		'profile-roblox',
		'profile-twitter',
		'profile-bsky',
		'profile-github',
		'profile-url'
		// 'profile-mastodon'
	];

	private static $externalLinksWithTooltip = [
		'profile-discord'
	];

	/** @var User */
	private User $mUser;

	public function __construct( User $user ) {
		$this->mUser = $user;
	}

	/** this ideally should be changed to use a constructor, and then use those methods appropriately instead
	 * of using static methods.
	 *
	 * @TODO: do that, yeah.
	 *
	 */

	/**
	 * Grab the groups that a user belongs to.
	 * @param User $user
	 * @param bool $global should we pull groups from CentralAuth?
	 * @return array
	 */
	public function getUserGroups( bool $global = false ): array {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$localGroups = $userGroupManager->getUserGroups( $this->mUser ); // the local groups the user belongs to

		$centralAuthLoaded = self::isCentralAuthLoaded();

		if ( $global && $centralAuthLoaded ) {
			$centralAuthUser = CentralAuthUser::getInstance( $this->mUser );
			$globalGroups = $centralAuthUser->getGlobalGroups();

			if ( count( $globalGroups ) > 0 ) {
				// get the array key for the steward group
				$steward = array_search( 'steward', $localGroups );

				// if it exists, unset it so we don't show duplicate user groups (since the global steward will always take precedence)
				if ( $steward ) {
					unset( $localGroups[$steward] );
				}

				return array_merge( $localGroups, $globalGroups );
			}
		}

		return $localGroups;
	}

	/**
	 * @param User $user
	 * @return mixed|null
	 */
	public function getUserBiography(): string|null {
		return MediaWikiServices::getInstance()->getUserOptionsLookup()->getOption( $this->mUser, 'profile-aboutme' );
	}

	public function setPreferences( UserOptionsManager $userOptionsManager, array $profileData ) {
		$data = [];
		foreach ( $profileData as $pair ) {
			list( $key, $value ) = explode( '=', $pair, 2 );
			$data[$key] = urldecode( $value ); // since we got from the URl, it will be encoded, convert it back to human
		}

		// first remove all the invalid elements from the data array (incase someone posts something incongrous here)
		$data = array_filter( $data, static function ( $key ) {
			return in_array( $key, self::$preferences );
		}, ARRAY_FILTER_USE_KEY );

		foreach ( $data as $key => $value ) {
			$userOptionsManager->setOption( $this->mUser, $key, $value );
		}

		$userOptionsManager->saveOptions( $this->mUser );
	}

	/**
	 * Return whether or not a user is blocked
	 * There is no longer any separate check for a global block
	 * If someone is blocked we'll just show the same notice irrelevant of local or global
	 * @param User $user
	 * @return bool
	 */
	public function isBlocked(): bool {
		return (bool)$this->mUser->getBlock();
	}

	/**
	 * Check against CentralAuth if this current user is locked or not
	 * @param User $user
	 * @return bool
	 */
	public function isLocked(): bool {
		// if CA isn't loaded, we can never be locked so return false.
		if ( !self::isCentralAuthLoaded() ) {
			return false;
		}

		return CentralAuthUser::getInstance( $this->mUser )->isLocked();
	}

	/**
	 * @return array
	 */
	public function getProfileLinks(): array {
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		$externalLinks = [];

		foreach ( self::$externalLinks as $externalLink ) {
			$externalLinkValue = $userOptionsLookup->getOption( $this->mUser, $externalLink );

			$externalLink = str_replace( "profile-", '', $externalLink );

			if ( $externalLinkValue ) {
				$externalLinks[$externalLink] = self::generateExternalLink( $externalLink, $externalLinkValue );
			}
		}

		foreach ( self::$externalLinksWithTooltip as $externalLink ) {
			$externalLinkValue = $userOptionsLookup->getOption( $this->mUser, $externalLink );
			$externalLink = str_replace( "profile-", '', $externalLink );

			if ( $externalLinkValue ) {
				$externalLinks[$externalLink] = self::generateExternalLinkWithTooltip( $externalLink, $externalLinkValue );
			}
		}

		return $externalLinks;
	}

	private function generateExternalLink( string $externalLink, string $value ) {
		/**
		 * Icons from iconoir and licensed under the MIT license
		 * 'twitter' => '<?xml version="1.0" encoding="UTF-8"?><svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M16.8198 20.7684L3.75317 3.96836C3.44664 3.57425 3.72749 3 4.22678 3H6.70655C6.8917 3 7.06649 3.08548 7.18016 3.23164L20.2468 20.0316C20.5534 20.4258 20.2725 21 19.7732 21H17.2935C17.1083 21 16.9335 20.9145 16.8198 20.7684Z" stroke="#000000" stroke-width="1.5"></path><path d="M20 3L4 21" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path></svg>'
		 * 'twitter' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="24px" stroke-width="1.5" height="24px" fill="none" viewBox="0 0 24 24" color="#000000"><path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"></path></svg>'
		 * 'mastodon' => '<?xml version="1.0" encoding="UTF-8"?><svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M7 13.5C7 13.5 7 10.7574 7 9C7 5.99998 12 6 12 9C12 10.1716 12 12 12 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17 13.5C17 13.5 17 10.7574 17 9C17 5.99998 12 6 12 9C12 10.1716 12 12 12 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7.99993 17C15.5 18 20.9999 17 20.9999 13L21 9C21.0003 3.5 17.0003 2.5 15 2.5H9C5.99989 2.5 2.93261 3.5 3.13687 9C3.21079 10.987 3.17311 13.3851 3.5 16C4.50007 24 14 21.5 15.5 21V19.5C15.5 19.5 7.5 21 7.99993 17Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
		 * 'profile-bsky'
		 */
		$svgIcons = [
			'twitter' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 16 16" fill="#1DA1F2" class="bi bi-twitter"><path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"></path></svg>',
			'bsky' => '<?xml version="1.0" encoding="UTF-8"?><svg fill="#0085FF" viewBox="0 0 64 57" width="24px" height="21.375px" xmlns="http://www.w3.org/2000/svg"><path fill="#0085FF" d="M13.873 3.805C21.21 9.332 29.103 20.537 32 26.55v15.882c0-.338-.13.044-.41.867-1.512 4.456-7.418 21.847-20.923 7.944-7.111-7.32-3.819-14.64 9.125-16.85-7.405 1.264-15.73-.825-18.014-9.015C1.12 23.022 0 8.51 0 6.55 0-3.268 8.579-.182 13.873 3.805ZM50.127 3.805C42.79 9.332 34.897 20.537 32 26.55v15.882c0-.338.13.044.41.867 1.512 4.456 7.418 21.847 20.923 7.944 7.111-7.32 3.819-14.64-9.125-16.85 7.405 1.264 15.73-.825 18.014-9.015C62.88 23.022 64 8.51 64 6.55c0-9.818-8.578-6.732-13.873-2.745Z"/></svg>',
			'github' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 97.707 98.434" width="24px" height="24px" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z"/></svg>',
			'roblox' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1100 1100" width="24px" height="24px" fill="currentColor"><path d="M228.268 0L82.2345 544.918L386.5 626.459L452.848 388.936L997.765 534.972L1080 228.268L228.268 0ZM627.15 690.951L82.2345 544.918L0 851.732L851.729 1080L997.765 534.972L690.951 452.737L627.15 690.951Z"/></svg>',
			'url' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 480 480" fill="currentColor"><path d="M409.657,32.474c-43.146-43.146-113.832-43.146-156.978,0l-84.763,84.762c29.07-8.262,60.589-6.12,88.129,6.732l44.063-44.064c17.136-17.136,44.982-17.136,62.118,0c17.136,17.136,17.136,44.982,0,62.118l-55.386,55.386l-36.414,36.414c-17.136,17.136-44.982,17.136-62.119,0l-47.43,47.43c11.016,11.017,23.868,19.278,37.332,24.48c36.415,14.382,78.643,8.874,110.467-16.219c3.06-2.447,6.426-5.201,9.18-8.262l57.222-57.222l34.578-34.578C453.109,146.306,453.109,75.926,409.657,32.474z"/><path d="M184.135,320.114l-42.228,42.228c-17.136,17.137-44.982,17.137-62.118,0c-17.136-17.136-17.136-44.981,0-62.118l91.8-91.799c17.136-17.136,44.982-17.136,62.119,0l47.43-47.43c-11.016-11.016-23.868-19.278-37.332-24.48c-38.25-15.3-83.232-8.262-115.362,20.502c-1.53,1.224-3.06,2.754-4.284,3.978l-91.8,91.799c-43.146,43.146-43.146,113.832,0,156.979c43.146,43.146,113.832,43.146,156.978,0l82.927-83.845C230.035,335.719,220.243,334.496,184.135,320.114z"/></svg>'
		];

		switch ( $externalLink ) {
			case 'twitter':
				$value = "https://twitter.com/$value";
				break;
			case 'bsky':
				$value = "https://bsky.app/profile/$value";
				break;
			case 'github':
				$value = "https://github/$value";
				break;
			case 'roblox':
				$value = "https://www.roblox.com/users/profile?username=$value";
				break;
			case 'url':
				$value = "$value";
				break;
			// case 'mastodon':
			// 	$value = "https://mastodon.social/$value";
			// 	break;
			default:
				return $value;
		}

		if ( isset( $svgIcons[$externalLink] ) ) {
			return Html::rawElement(
				'a',
				[ 'href' => $value, 'target' => '__blank' ],
				$svgIcons[$externalLink]
			);
		}

		return null;
	}

	private function generateExternalLinkWithTooltip( string $externalLink, string $value ) {
		/**
		 * Icons from iconoir and licensed under the MIT license
		 * 'discord' => '<?xml version="1.0" encoding="UTF-8"?><svg width="24px" height="24px" viewBox="0 0 24 24" stroke-width="1.5" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M5.5 16C10.5 18.5 13.5 18.5 18.5 16" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.5 17.5L16.5 19.5C16.5 19.5 20.6713 18.1717 22 16C22 15 22.5301 7.85339 19 5.5C17.5 4.5 15 4 15 4L14 6H12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.52832 17.5L7.52832 19.5C7.52832 19.5 3.35699 18.1717 2.02832 16C2.02832 15 1.49823 7.85339 5.02832 5.5C6.52832 4.5 9.02832 4 9.02832 4L10.0283 6H12.0283" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.5 14C7.67157 14 7 13.1046 7 12C7 10.8954 7.67157 10 8.5 10C9.32843 10 10 10.8954 10 12C10 13.1046 9.32843 14 8.5 14Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.5 14C14.6716 14 14 13.1046 14 12C14 10.8954 14.6716 10 15.5 10C16.3284 10 17 10.8954 17 12C17 13.1046 16.3284 14 15.5 14Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
		 */
		$svgIcons = [
			'discord' => '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" id="Discord-Logo" viewBox="0 0 126.644 96" width="24px" height="24px"><path id="Discord-Symbol-Blurple" fill="#5865F2" d="M81.15,0c-1.2376,2.1973-2.3489,4.4704-3.3591,6.794-9.5975-1.4396-19.3718-1.4396-28.9945,0-.985-2.3236-2.1216-4.5967-3.3591-6.794-9.0166,1.5407-17.8059,4.2431-26.1405,8.0568C2.779,32.5304-1.6914,56.3725.5312,79.8863c9.6732,7.1476,20.5083,12.603,32.0505,16.0884,2.6014-3.4854,4.8998-7.1981,6.8698-11.0623-3.738-1.3891-7.3497-3.1318-10.8098-5.1523.9092-.6567,1.7932-1.3386,2.6519-1.9953,20.281,9.547,43.7696,9.547,64.0758,0,.8587.7072,1.7427,1.3891,2.6519,1.9953-3.4601,2.0457-7.0718,3.7632-10.835,5.1776,1.97,3.8642,4.2683,7.5769,6.8698,11.0623,11.5419-3.4854,22.3769-8.9156,32.0509-16.0631,2.626-27.2771-4.496-50.9172-18.817-71.8548C98.9811,4.2684,90.1918,1.5659,81.1752.0505l-.0252-.0505ZM42.2802,65.4144c-6.2383,0-11.4159-5.6575-11.4159-12.6535s4.9755-12.6788,11.3907-12.6788,11.5169,5.708,11.4159,12.6788c-.101,6.9708-5.026,12.6535-11.3907,12.6535ZM84.3576,65.4144c-6.2637,0-11.3907-5.6575-11.3907-12.6535s4.9755-12.6788,11.3907-12.6788,11.4917,5.708,11.3906,12.6788c-.101,6.9708-5.026,12.6535-11.3906,12.6535Z"/></svg>'
		];

		if ( isset( $svgIcons[$externalLink] ) ) {
			return Html::rawElement(
				'span',
				[ 'class' => 'external-link tooltip', 'title' => ucfirst( $externalLink ), 'username' => $value ],
				$svgIcons[$externalLink]
			);
		}

		return null;
	}

	public function getAvatarForUserProfile() {
		$avatar = new UserProfileV2Avatar( $this->mUser->getId() );
		return $avatar->getAvatarUrl( [ 'raw' => true ] );
	}

	/**
	 * Get a global users edit count
	 * @param User $user the local user we're getting a global edit count for.
	 * @return int|null
	 */
	public function getGlobalEditCount(): int|null {
		if ( !self::isCentralAuthLoaded() ) {
			return null;
		}

		$centralAuthUser = CentralAuthUser::getPrimaryInstanceByName( $this->mUser->getName() );

		if ( !$centralAuthUser->exists() ) {
			return null;
		}

		return $centralAuthUser->getGlobalEditCount();
	}

	/**
	 * Should we show the global edit count for this user?
	 * @param User $user
	 * @return bool
	 */
	public function shouldShowGlobalEditCount(): bool {
		if ( !self::isCentralAuthLoaded() ) {
			return false;
		}

		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		$shouldShow = $userOptionsLookup->getOption( $this->mUser, 'profile-show-globaledits' );

		return $shouldShow == 1;
	}

	public function shouldShowGlobalGroups(): bool {
		if ( !self::isCentralAuthLoaded() ) {
			return false;
		}

		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		$shouldShow = $userOptionsLookup->getOption( $this->mUser, 'profile-show-globalgroups' );

		return $shouldShow == 1;
	}

	/**
	 * Helper function to double check if CA is loaded
	 * @return bool
	 */
	private function isCentralAuthLoaded(): bool {
		return ExtensionRegistry::getInstance()->isLoaded( 'CentralAuth' );
	}
}