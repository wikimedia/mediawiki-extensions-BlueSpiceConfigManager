<?php

namespace BlueSpice\ConfigManager;

use MediaWiki\MediaWikiServices;

class ClientConfig {

	/**
	 *
	 * @return array
	 */
	public static function getPathNames() {
		$services = MediaWikiServices::getInstance();

		$cfgDefFactory = $services->getService( 'BSConfigDefinitionFactory' );
		$pathMessages = [];

		foreach ( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			$cfgDef = $cfgDefFactory->factory( $name );
			if ( !$cfgDef ) {
				continue;
			}
			$msgFactory = $services->getService( 'BSSettingPathFactory' );
			foreach ( $cfgDef->getPaths() as $path ) {
				foreach ( explode( '/', $path ) as $section ) {
					$msgKey = $msgFactory->getMessageKey( $section );
					if ( !$msgKey ) {
						continue;
					}
					$pathMessages[$section] = $msgKey;
				}
			}
		}
		return [
			'pathnames' => $pathMessages
		];
	}

	/**
	 *
	 * @return array
	 */
	public static function getToolbarOffset() {
		$services = MediaWikiServices::getInstance();
		$bsgConfig = $services->getConfigFactory()->makeConfig( 'bsg' );

		return [
			'offset' => $bsgConfig->get( 'ConfigManagerToolbarOffset' )
		];
	}

}
