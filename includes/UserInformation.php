<?php

namespace Telepedia\UserProfileV2;

use ExtensionRegistry;
use MediaWiki\Extension\CentralAuth\User\CentralAuthUser;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class UserInformation {

	/**
	 * Grab the groups that a user belongs to.
	 * @param User $user
	 * @param bool $global should we pull groups from CentralAuth?
	 * @return array
	 */
	public static function getUserGroups( User $user, bool $global = false ): array {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$localGroups = $userGroupManager->getUserGroups( $user ); // the local groups the user belongs to

		$centralAuthLoaded = ExtensionRegistry::getInstance()->isLoaded( 'CentralAuth' );

		if ( $global && $centralAuthLoaded ) {
			$centralAuthUser = CentralAuthUser::getInstance( $user );
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

	public static function getUserBiography( User $user ) {
		$bio = MediaWikiServices::getInstance()->getUserOptionsLookup()->getOption( $user, 'profile-aboutme' );
		return $bio;
	}
}
