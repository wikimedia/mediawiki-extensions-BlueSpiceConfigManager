<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\LoadBalancer;

class Reader extends \BlueSpice\Data\Settings\Reader {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param ConfigDefinitionFactory $factory
	 * @param LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct(
		ConfigDefinitionFactory $factory, $loadBalancer, ?IContextSource $context = null
	) {
		parent::__construct( $loadBalancer, $context );
		$this->factory = $factory;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->factory );
	}

	/**
	 *
	 * @return SecondaryDataProvider
	 */
	protected function makeSecondaryDataProvider() {
		return new SecondaryDataProvider( $this->factory );
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

}
