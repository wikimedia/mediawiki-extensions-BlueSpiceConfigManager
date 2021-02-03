<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use BlueSpice\Data\IRecord;

class SecondaryDataProvider extends \BlueSpice\Data\SecondaryDataProvider {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param ConfigDefinitionFactory $factory
	 */
	public function __construct( ConfigDefinitionFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 *
	 * @param IRecord &$dataSet
	 */
	protected function doExtend( &$dataSet ) {
		$cfgDfn = $this->factory->factory( $dataSet->get( Record::NAME ) );
		if ( !$cfgDfn ) {
			return;
		}
		$form = '';
		\OutputPage::setupOOUI(
			'default',
			'ltr'
		);

		$formField = $cfgDfn->getHtmlFormField();
		if ( !$formField ) {
			return;
		}

		if ( $formField instanceof \OOUI\Element ) {
			$form .= (string)$formField;
		} else {
			$form .= $formField->getOOUI( $cfgDfn->getValue() );
		}

		$dataSet->set(
			Record::FORM,
			$form
		);
	}
}
