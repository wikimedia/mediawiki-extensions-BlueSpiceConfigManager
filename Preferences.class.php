<?php
/**
 * This is the Preferences class.
 *
 * The Preferences offers an easy way to manage the settings of BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    2.23.1
 * @package    Bluespice_Extensions
 * @subpackage Preferences
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (01.07.11 13:56)

/**
 * the Preferences class
 * @package BlueSpice_Extensions
 * @subpackage Preferences
 */
class BsPreferences extends BsExtensionMW {

	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;

		WikiAdmin::registerModule( 'BlueSpicePreferences', [
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_einstellungen_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-bluespicepreferences-label',
			'iconCls' => 'bs-icon-wrench',
			'permissions' => [ 'bluespicepreferences-viewspecialpage' ],
		]);

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	protected function initExt() {
		$this->mCore->registerPermission( 'bluespicepreferences-viewspecialpage', array( 'sysop' ), array( 'type' => 'global' ) );
	}

	/**
	 * saves the settings to the database
	 * @param array $aData an associative array of fieldnames and values
	 */
	public function savePreferences( $aData ) {
		if ( wfReadOnly() ) {
			$url = SpecialPage::getTitleFor( 'WikiAdmin' )->getFullURL( array(
				'mode' => 'Preferences',
				'success' => 0
			) );
			$this->getOutput()->redirect( $url );

			return false;
		}

		$vars = BsConfig::getRegisteredVars();
		foreach ( $vars as $var ) {
			$options = $var->getOptions();
			if ( !( $options & ( BsConfig::LEVEL_PUBLIC | BsConfig::LEVEL_USER ) ) ) {
				continue;
			}
			if ( $options & BsConfig::NO_DEFAULT ) continue;

			$name = $this->generateFieldId( $var );
			$value = isset( $aData[$name] ) ? $aData[$name] : NULL;
			if ( ( $var->getOptions() & BsConfig::TYPE_BOOL ) && $value == NULL ) {
				BsConfig::set($var->getKey(), 0, true);
			}

			$bReturn = true;

			Hooks::run( 'BSWikiAdminPreferencesBeforeSetVariable', array( $this, &$var, &$value, &$bReturn ) );

			if ( $value !== null && $bReturn !== false ) {
				BsConfig::set( $var->getKey(), $value, true );
			}
		}

		BsConfig::saveSettings();

		$url = SpecialPage::getTitleFor( 'WikiAdmin' )->getFullURL( array(
			'mode' => 'Preferences',
			'success' => 1
		) );
		$this->getOutput()->redirect( $url );

		return false;
	}

	/**
	 * generates an id string for a BsCOnfig instance for usage in html code
	 * @param BsConfig $var
	 * @return string the field id
	 */
	protected function generateFieldId( $var ) {
		return $var->getAdapter() . "_" . $var->getExtension() . "_" . $var->getName();
	}

	public static function onBeforePageDisplay( OutputPage &$out, &$skin ) {
		if( !$out->getTitle()->isSpecial( 'WikiAdmin' ) ) {
			return true;
		}
		if( strtolower( $out->getRequest()->getVal( 'mode' )  ) != 'preferences' ){
			return true;
		}

		$out->addInlineStyle(
			'.bs-prefs legend{cursor:pointer;}'
		);

		return true;
	}

}