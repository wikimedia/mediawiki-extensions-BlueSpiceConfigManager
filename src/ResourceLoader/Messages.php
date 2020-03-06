<?php

namespace BlueSpice\ConfigManager\ResourceLoader;

use BlueSpice\ConfigDefinition;
use BlueSpice\Services;

class Messages extends \ResourceLoaderModule {
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array List of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		$messages = parent::getMessages();
		$cfgDefFactory = Services::getInstance()->getService( 'BSConfigDefinitionFactory' );
		foreach ( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			$cfgDef = $cfgDefFactory->factory( $name );
			if ( !$cfgDef ) {
				continue;
			}
			$this->extractPathMessages( $cfgDef, $messages );
		}
		array_unique( $messages );
		return array_values( $messages );
	}

	/**
	 *
	 * @param ConfigDefinition $cfgDef
	 * @param array &$messages
	 * @return array
	 */
	protected function extractPathMessages( ConfigDefinition $cfgDef, &$messages ) {
		$msgFactory = Services::getInstance()->getService( 'BSSettingPathFactory' );

		foreach ( $cfgDef->getPaths() as $path ) {
			foreach ( explode( '/', $path ) as $section ) {
				$msgKey = $msgFactory->getMessageKey( $section );
				if ( !$msgKey ) {
					continue;
				}
				$messages[] = $msgKey;
			}
		}
		return [];
	}

	/**
	 * Get target(s) for the module, eg ['desktop'] or ['desktop', 'mobile']
	 *
	 * @return array Array of strings
	 */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}

}
