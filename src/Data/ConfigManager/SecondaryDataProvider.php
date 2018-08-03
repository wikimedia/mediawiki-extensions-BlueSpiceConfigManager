<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;
use BlueSpice\ConfigDefinitionFactory;

class SecondaryDataProvider extends \BlueSpice\Data\SecondaryDataProvider {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	public function __construct( ConfigDefinitionFactory $factory ) {
		$this->factory = $factory;
	}

	protected function doExtend( &$dataSet ){
		$cfgDfn = $this->factory->factory( $dataSet->get( Record::NAME ) );
		if( !$cfgDfn ) {
			return;
		}
		$form = '';
		\OutputPage::setupOOUI(
			'default',
			'ltr'
		);

		if( !$formField = $cfgDfn->getHtmlFormField() ) {
			return;
		}

		if( $formField instanceof \OOUI\Element ) {
			$form .= (string) $formField;
		} else {
			$form .= $formField->getOOUI( $cfgDfn->getValue() );
		}

		$dataSet->set(
			Record::FORM,
			$form
		);
	}
}
