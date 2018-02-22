<?php

namespace BlueSpice\ConfigManager;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceConfigManager' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-bluespiceconfigmanager-desc' );
	}

	public function getName() {
		return wfMessage( 'bs-bluespiceconfigmanager-admintool-label' );
	}

	public function getClasses() {
		$classes = array(
			'bs-icon-wrench'
		);

		return $classes;
	}

	public function getDataAttributes() {
		return [];
	}

	public function getPermissions() {
		$permissions = array(
			'bluespiceconfigmanager-viewspecialpage'
		);
		return $permissions;
	}

}