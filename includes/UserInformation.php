<?php

namespace Telepedia\UserProfileV2;

use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class UserInformation {

	/**
	 * Grab the groups that a user belongs to.
	 * @param User $user
	 * @param bool $global should we pull groups from CentralAuth?
	 * @return array
	 */
	public static function getUserGroups(User $user, bool $global = false): array {

		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$localGroups = $userGroupManager->getUserGroups($user); // the local groups the user belongs to

		return $localGroups;
	}
}
