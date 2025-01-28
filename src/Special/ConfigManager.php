<?php

namespace BlueSpice\ConfigManager\Special;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class ConfigManager extends SpecialPage {

	public function __construct() {
		parent::__construct(
			'BlueSpiceConfigManager',
			'bluespiceconfigmanager-viewspecialpage'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $param ) {
		parent::execute( $param );
		$this->getOutput()->addModuleStyles( [ 'ext.bluespice.configmanager.styles' ] );
		$this->getOutput()->addModules(
			'ext.bluespice.configmanager'
		);

		$this->getOutput()->addHTML(
			Html::element( 'div', [
				'id' => 'bs-configmanager'
			] )
		);
	}
}
