<?php

namespace BlueSpice\ConfigManager\Special;

use BlueSpice\Services;
use BlueSpice\Special\ManagerBase;

class ConfigManager extends ManagerBase {

	public function __construct() {
		parent::__construct(
			'BlueSpiceConfigManager',
			'bluespiceconfigmanager-viewspecialpage'
		);
	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
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

	protected function extractPathMessageKeys( $cfgDef, &$pathMessages ) {
		$msgFactory = Services::getInstance()->getBSSettingPathFactory();
		foreach ( $cfgDef->getPaths() as $path ) {
			foreach ( explode( '/', $path ) as $section ) {
				if ( !$msgKey = $msgFactory->getMessageKey( $section ) ) {
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

	protected function getJSVars() {
		$cfgDefFactory = Services::getInstance()
			->getBSConfigDefinitionFactory();
		$pathMessages = [];

		foreach ( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			if ( !$cfgDef = $cfgDefFactory->factory( $name ) ) {
				continue;
			}
			$this->extractPathMessageKeys( $cfgDef, $pathMessages );
		}

		return [
			'ConfigManagerPathMessages' => $pathMessages
		];
	}
}
