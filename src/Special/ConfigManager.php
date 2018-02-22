<?php

namespace BlueSpice\ConfigManager\Special;

class ConfigManager extends \BsSpecialPage {

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
	}

}
