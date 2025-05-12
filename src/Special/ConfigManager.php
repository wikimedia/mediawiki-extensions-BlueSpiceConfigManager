<?php

namespace BlueSpice\ConfigManager\Special;

use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use OOJSPlus\Special\OOJSSpecialPage;

class ConfigManager extends OOJSSpecialPage {

	public function __construct() {
		parent::__construct(
			'BlueSpiceConfigManager',
			'bluespiceconfigmanager-viewspecialpage'
		);

		$this->templateParser = new TemplateParser(
			dirname( __DIR__, 2 ) . '/resources/templates'
		);
	}

	/**
	 *
	 * @return void
	 */
	protected function buildSkeleton() {
		$this->getOutput()->enableOOUI();
		$this->getOutput()->addModuleStyles( [ 'ext.bluespice.configManager.skeleton' ] );
		$skeleton = $this->templateParser->processTemplate(
			'skeleton-configmanager',
			[]
		);
		$skeletonCnt = Html::openElement( 'div', [
			'id' => 'bs-configManager-skeleton-cnt'
		] );
		$skeletonCnt .= $skeleton;
		$skeletonCnt .= Html::closeElement( 'div' );
		$this->getOutput()->addHTML( $skeletonCnt );
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $param ) {
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
