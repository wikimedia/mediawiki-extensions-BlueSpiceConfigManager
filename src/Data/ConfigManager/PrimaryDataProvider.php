<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;
use BlueSpice\ConfigDefinitionFactory;

class PrimaryDataProvider extends \BlueSpice\Data\Settings\PrimaryDataProvider {

	/**
	 *
	 * @var \BlueSpice\Data\ReaderParams
	 */
	protected $readerParams = null;

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	public function __construct( $db, ConfigDefinitionFactory $factory ) {
		parent::__construct( $db );
		$this->factory = $factory;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 */
	public function makeData( $params ) {
		$this->readerParams = $params;
		$this->data = [];

		foreach( $this->factory->getRegisteredDefinitions() as $key ) {
			$this->appendRowToData( (object) [ Record::NAME => $key ] );
		}

		return $this->data;
	}

	protected function appendRowToData( $row ) {
		$cfgDfn = $this->factory->factory( $row->{Record::NAME} );
		if( !$cfgDfn ) {
			return;
		}
		if( $cfgDfn->isHidden() ) {
			return;
		}
		if( !empty( $this->readerParams->getQuery() ) ) {
			$res = \BsStringHelper::filter(
				'ct',
				$cfgDfn->getLabelMessageKey(),
				$this->readerParams->getQuery()
			);
			if( !$res ) {
				return;
			}
		}
		$this->data[] = new Record( (object) [
			Record::NAME => $row->{Record::NAME},
			Record::VALUE => $cfgDfn->getValue(),
			Record::LABEL => \Message::newFromKey( $cfgDfn->getLabelMessageKey() )->plain(),
			Record::PATHS => $cfgDfn->getPaths(),
		]);
	}
}
