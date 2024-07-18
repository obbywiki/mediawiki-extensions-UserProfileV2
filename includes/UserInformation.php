<?php

namespace Telepedia\UserProfileV2;

use ExtensionRegistry;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use MediaWiki\User\UserOptionsManager;

class UserInformation {

	private static $preferences = [
		'profile-aboutme',
		'profile-show-globalgroups',
		'profile-show-globaledits',
		'profile-discord',
		'profile-twitter'
	];

	/**
	 * Grab the groups that a user belongs to.
	 * @param User $user
	 * @param bool $global should we pull groups from CentralAuth?
	 * @return array
	 */
	public static function getUserGroups(User $user, bool $global = false): array {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$localGroups = $userGroupManager->getUserGroups($user); // the local groups the user belongs to

		$centralAuthLoaded = ExtensionRegistry::getInstance()->isLoaded('CentralAuth');

		if ($global && $centralAuthLoaded) {
			$centralAuthUser = CentralAuthUser::getInstance($user);
			$globalGroups = $centralAuthUser->getGlobalGroups();

			if (count($globalGroups) > 0) {
				// get the array key for the steward group
				$steward = array_search('steward', $localGroups);

				// if it exists, unset it so we don't show duplicate user groups (since the global steward will always take precedence)
				if ($steward) {
					unset($localGroups[$steward]);
				}

				return array_merge($localGroups, $globalGroups);
			}
		}

		return $localGroups;
	}

	/**
	 * @param User $user
	 * @return mixed|null
	 */
	public static function getUserBiography(User $user) {
		$bio = MediaWikiServices::getInstance()->getUserOptionsLookup()->getOption($user, 'profile-aboutme');
		return $bio;
	}

	public static function setPreferences(User $user, UserOptionsManager $userOptionsManager, array $profileData) {

		$data = [];
		foreach ($profileData as $pair) {
			list($key, $value) = explode('=', $pair, 2);
			$data[$key] = urldecode($value); // since we got from the URl, it will be encoded, convert it back to human
		}

		// first remove all the invalid elements from the data array (incase someone posts something incongrous here)
		$data = array_filter($data, function ($key) {
			return in_array($key, self::$preferences);
		}, ARRAY_FILTER_USE_KEY);

		foreach ($data as $key => $value) {
			$userOptionsManager->setOption($user, $key, $value);
		}

		$userOptionsManager->saveOptions($user);

	}

}
