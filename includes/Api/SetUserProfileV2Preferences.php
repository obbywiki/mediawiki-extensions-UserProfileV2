<?php

namespace Telepedia\UserProfileV2\Api;

use ApiBase;
use ApiMain;
use Exception;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserOptionsManager;
use Telepedia\UserProfileV2\UserInformation;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

class SetUserProfileV2Preferences extends ApiBase {

	/** @var UserOptionsManager */
	private UserOptionsManager $userOptionsManager;

	/** @var UserFactory */
	private UserFactory $userFactory;

	public function __construct( ApiMain $apiMain, $moduleName, UserOptionsManager $userOptionsManager, UserFactory $userFactory ) {
		parent::__construct( $apiMain, $moduleName );
		$this->userOptionsManager = $userOptionsManager;
		$this->userFactory = $userFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$params = $this->extractRequestParams();

		$userName = $params['user_name'];
		$body = $params['profile_data'];

		$actionUser = $this->getUser();

		$user = $this->getTargetUser( $userName );

		if ( !$user->isRegistered() ) {
			$this->dieWithError( [ 'userprofilev2-apierror-invalidusername', wfEscapeWikiText( $userName ) ] );
		}

		// if the user is not trying to change their own preferences, and isn't a profile manager, die.
		if ( $actionUser->getId() !== $user->getId() && !$this->getPermissionManager()->userHasRight( $actionUser, 'profile-manage' ) ) {
			$this->dieWithError( [ 'userprofilev2-apierror-invalidpermission', wfEscapeWikiText( $userName ) ] );
		}

		$userInformation = new UserInformation( $user );

		try {
			$userInformation->setPreferences( $this->userOptionsManager, $body );
			$this->getResult()->addValue( null, 'result', 'success' );
			return;
		} catch ( Exception $e ) {
			$this->getResult()->addValue( null, 'result', 'failure' );
			$this->getResult()->addValue( null, 'errormsg', $e->getMessage() );
			return;
		}
	}

	/**
	 * @param string $userName
	 * @return User
	 */
	private function getTargetUser( string $userName ): User {
		return $this->userFactory->newFromName( $userName );
	}

	/**
	 * @return bool
	 */
	public function mustBePosted(): bool {
		return true;
	}

	/**
	 * @return string
	 */
	public function needsToken(): string {
		return 'csrf';
	}

	/**
	 * @return bool
	 */
	public function isWriteMode(): bool {
		return true;
	}

	/**
	 * @return array[]
	 */
	public function getAllowedParams() {
		return [
			'user_name' => [
				ParamValidator::PARAM_TYPE => 'string',
				IntegerDef::PARAM_MIN => 1,
				ParamValidator::PARAM_REQUIRED => true,
			],
			'profile_data' => [
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}