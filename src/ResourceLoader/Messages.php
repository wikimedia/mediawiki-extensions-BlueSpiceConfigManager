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
		$cfgDefFactory = Services::getInstance()->getBSConfigDefinitionFactory();
		foreach( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			if( !$cfgDef = $cfgDefFactory->factory( $name ) ) {
				continue;
			}
			$this->extractPathMessages( $cfgDef, $messages );
		}
		array_unique( $messages );
		return array_values( $messages );
	}

	protected function extractPathMessages( ConfigDefinition $cfgDef, &$messages ) {
		$msgFactory = Services::getInstance()->getBSSettingPathFactory();

		foreach( $cfgDef->getPaths() as $path ) {
			foreach( explode( '/', $path ) as $section ) {
				if( !$msgKey = $msgFactory->getMessageKey( $section ) ) {
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
