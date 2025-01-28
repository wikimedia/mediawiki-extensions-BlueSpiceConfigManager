<?php

namespace BlueSpice\ConfigManager;

use BlueSpice\IAdminTool;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = SpecialPage::getTitleFor( 'BlueSpiceConfigManager' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-bluespiceconfigmanager-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'bs-bluespiceconfigmanager-admintool-label' );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-wrench'
		];

		return $classes;
	}

	/**
	 *
	 * @return array
	 */
	public function getDataAttributes() {
		return [];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getPermissions() {
		$permissions = [
			'bluespiceconfigmanager-viewspecialpage'
		];
		return $permissions;
	}

}
