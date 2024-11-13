<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use MediaWiki\Output\OutputPage;
use MWStake\MediaWiki\Component\DataStore\IRecord;

class SecondaryDataProvider extends \MWStake\MediaWiki\Component\DataStore\SecondaryDataProvider {

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
		OutputPage::setupOOUI(
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
