<?php

class SpecialBlueSpicePreferences extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'BlueSpicePreferences', 'bluespicepreferences-viewspecialpage' );

	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param type $sParameter
	 * @return type
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		if ( wfReadOnly() ) {
			throw new ReadOnlyError;
		}

		$this->getOutput()->addModules( 'ext.bluespice.preferences' );
		$this->getOutput()->addHTML( '<br />' );

		$oRequest = $this->getRequest();
		if ( $this->getRequest()->getVal( 'success' ) == true ) {
			$this->getOutput()->wrapWikiMsg(
				'<div class="successbox"><strong>$1</strong></div><div id="mw-pref-clear"></div>'."\n",
				'savedprefs'
			);
		}

		$orig_deliver = BsConfig::deliverUsersSettings( false );

		BsConfig::loadSettings();
		BsExtensionManager::getExtensionInformation();

		$vars = BsConfig::getRegisteredVars();

		$bShowall = $oRequest->getFuzzyBool( 'showall' );
		if ( $bShowall ) {
			$out = '';
			foreach ( $vars as $var ) {
				$out .= $var->getAdapter() . "::";
				if ( $var->getExtension() !== null ) {
					$out .= $var->getExtension() . "::";
				}
				$out .= $var->getName() . "<br>";
			}

			$this->getOutput()->addHTML( $out );
		}

		$preferences = array();
		$aSortedVariables = array();

		foreach ( $vars as $var ) {
			$options = $var->getOptions();
			if ( !( $options & ( BsConfig::LEVEL_PUBLIC | BsConfig::LEVEL_USER ) ) ) {
				continue;
			}
			if ( $options & BsConfig::NO_DEFAULT ) continue;
			$extension = $var->getI18nExtension() ? $var->getI18nExtension() : 'BASE';
			$aSortedVariables[$extension][] = $var;
		}

		foreach ( $aSortedVariables as $sExtensionName => $aExtensions ) {
			if ( !count( $aExtensions ) ) continue;

			foreach ( $aExtensions as $oVariable ) {
				// if continue, then $oAdapterSetView is not added to output
				if ( !count( $oVariable ) ) continue;
				$sSection = $sExtensionName;
				$oExtension = BsExtensionManager::getExtension( $sExtensionName );
				$field = $oVariable->getFieldDefinition( $sSection );

				if ( $oVariable->getOptions() & BsConfig::USE_PLUGIN_FOR_PREFS ) {

					$oExtension = BsExtensionManager::getExtension( $sExtensionName );
					$tmp = $oExtension->runPreferencePlugin( 'MW', $oVariable );

					$field = array_merge( $field, $tmp );
				}
				$preferences[$oVariable->generateFieldId()] = $field;

			}
		}
		BsConfig::deliverUsersSettings( $orig_deliver );

		$oForm = new HTMLFormEx( $preferences, 'prefs' );
		$oForm->setTitle( $this->getTitle() );
		$oForm->addHiddenField( 'mode', 'Preferences' );
		$oForm->setSubmitText( wfMessage( 'bs-extjs-save' )->plain() );
		$oForm->setSubmitName( 'WikiAdminPreferencesSubmit' );
		$oForm->setSubmitCallback( array( $this, 'savePreferences' ) );

		$oForm->show();

		$this->getOutput()->addHTML( '<br />' );
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

		$url = SpecialPage::getTitleFor( 'BlueSpicePreferences' )->getFullURL( array(
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

}
