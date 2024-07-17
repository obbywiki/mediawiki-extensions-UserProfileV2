<?php

namespace Telepedia\UserProfileV2;

use Article;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Override;

class UserPage extends Article {

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
		$this->mUserProfile = $userFactory->newFromName($title);

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

		// set the page title
		$output->setPageTitle($this->getTitle()->getPrefixedText());

		$output->addHTML(self::getProfileLayout());
	}

	private function getProfileLayout() {

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
			["class" => "profile-avatar-image", "src" => 'https://placehold.co/400x400']
		);
		$html .= Html::closeElement(
			"div",
		);
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

		$groups = UserInformation::getUserGroups($this->mUserProfile);

		if (count($groups) > 0) {
			foreach ($groups as $group) {
				$html .= Html::element(
					"span",
					['class' => 'profile-user-group'],
					$this->context->msg("group-{$group}-member")
				);
			}
		}

		$html .= Html::closeElement(
			"div",
		);

		// edit button
		$html .= Html::openElement(
			"div",
			["class" => "profile-header-actions"]
		);
		$html .= Html::closeElement(
			"div",
		);

		// edit count etc
		$html .= Html::openElement(
			"ul",
			['class' => 'profile-header-statistics']
		);
		$html .= Html::element(
			"li",
			[],
			"{$this->mUserProfile->getEditCount()} edits"
		);
		$html .= Html::closeElement(
			"ul",
			[],
		);
		$html .= Html::closeElement(
			"div",
		);
		$html .= Html::closeElement(
			"div",
		);
		// END Profile header

		$html .= Html::closeElement(
			"div",
		);

		// END profile information section
		$html .= Html::closeElement(
			"section",
		);
		$html .= Html::closeElement(
			"div",
		);
		$html .= Html::closeElement(
			"div",
		);

		return $html;
	}
}
