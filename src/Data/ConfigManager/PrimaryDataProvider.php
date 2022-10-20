<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends \BlueSpice\Data\Settings\PrimaryDataProvider {

	/**
	 *
	 * @var ReaderParams
	 */
	protected $readerParams = null;

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param IDatabase $db
	 * @param ConfigDefinitionFactory $factory
	 */
	public function __construct( $db, ConfigDefinitionFactory $factory ) {
		parent::__construct( $db );
		$this->factory = $factory;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->readerParams = $params;
		$this->data = [];

		foreach ( $this->factory->getRegisteredDefinitions() as $key ) {
			$this->appendRowToData( (object)[ Record::NAME => $key ] );
		}

		return $this->data;
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( \stdClass $row ) {
		$cfgDfn = $this->factory->factory( $row->{Record::NAME} );
		if ( !$cfgDfn ) {
			return;
		}
		if ( $cfgDfn->isHidden() ) {
			return;
		}
		if ( !empty( $this->readerParams->getQuery() ) ) {
			$res = \BsStringHelper::filter(
				'ct',
				$cfgDfn->getLabelMessageKey(),
				$this->readerParams->getQuery()
			);
			if ( !$res ) {
				return;
			}
		}
		$this->data[] = new Record( (object)[
			Record::NAME => $row->{Record::NAME},
			Record::VAR_NAME => $cfgDfn->getVariableName(),
			Record::VALUE => $cfgDfn->getValue(),
			Record::LABEL => \Message::newFromKey( $cfgDfn->getLabelMessageKey() )->plain(),
			Record::PATHS => $cfgDfn->getPaths(),
		] );
	}
}
