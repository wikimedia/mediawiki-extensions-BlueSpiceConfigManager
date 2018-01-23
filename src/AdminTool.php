<?php

namespace BlueSpice\ConfigManager;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'ConfigManager' );
		return $tool->getLocalURL();
	}

	public function getDescription() {
		return wfMessage( 'bs-bluespicepreferences-desc' );
	}

	public function getName() {
		return wfMessage( 'bs-bluespicepreferences-label' );
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
			'bluespicepreferences-viewspecialpage'
		);
		return $permissions;
	}

}