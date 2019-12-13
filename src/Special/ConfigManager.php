<?php

namespace BlueSpice\ConfigManager\Special;

use BlueSpice\Services;
use BlueSpice\Special\ManagerBase;
use BlueSpice\ConfigDefinition;

class ConfigManager extends ManagerBase {

	public function __construct() {
		parent::__construct(
			'BlueSpiceConfigManager',
			'bluespiceconfigmanager-viewspecialpage'
		);
	}

	/**
	 *
	 * @param string $param
	 * @return type
	 */
	public function execute( $param ) {
		parent::execute( $param );

		if ( wfReadOnly() ) {
			throw new \ReadOnlyError;
		}
		$this->getOutput()->enableOOUI();
	}

	/**
	 *
	 * @param ConfigDefinition $cfgDef
	 * @param array &$pathMessages
	 */
	protected function extractPathMessageKeys( $cfgDef, &$pathMessages ) {
		$msgFactory = Services::getInstance()->getBSSettingPathFactory();
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

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-configmanager';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.configmanager',
			'ext.bluespice.configmanager.styles'
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$cfgDefFactory = Services::getInstance()
			->getBSConfigDefinitionFactory();
		$pathMessages = [];

		foreach ( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			$cfgDef = $cfgDefFactory->factory( $name );
			if ( !$cfgDef ) {
				continue;
			}
			$this->extractPathMessageKeys( $cfgDef, $pathMessages );
		}

		return [
			'ConfigManagerPathMessages' => $pathMessages
		];
	}
}
