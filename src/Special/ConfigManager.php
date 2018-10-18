<?php

namespace BlueSpice\ConfigManager\Special;
use BlueSpice\Services;

class ConfigManager extends \BlueSpice\SpecialPage {

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
		$this->getOutput()->addModules( 'ext.bluespice.configmanager' );
		$this->getOutput()->addModuleStyles(
			'ext.bluespice.configmanager.styles'
		);
		$this->getOutput()->addHTML( \Html::element( 'div', [
			'id' => 'bs-configmanager',
		]));

		$cfgDefFactory = Services::getInstance()
			->getBSConfigDefinitionFactory();
		$pathMessages = [];

		foreach( $cfgDefFactory->getRegisteredDefinitions() as $name ) {
			if( !$cfgDef = $cfgDefFactory->factory( $name ) ) {
				continue;
			}
			$this->extractPathMessageKeys( $cfgDef, $pathMessages );
		}

		$this->getOutput()->addJsConfigVars(
			'ConfigManagerPathMessages',
			$pathMessages
		);
	}

	protected function extractPathMessageKeys( $cfgDef, &$pathMessages ) {
		$msgFactory = Services::getInstance()->getBSSettingPathFactory();
		foreach( $cfgDef->getPaths() as $path ) {
			foreach( explode( '/', $path ) as $section ) {
				if( !$msgKey = $msgFactory->getMessageKey( $section ) ) {
					continue;
				}
				$pathMessages[$section] = $msgKey;
			}
		}
	}

}
