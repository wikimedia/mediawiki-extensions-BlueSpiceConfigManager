<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;
use BlueSpice\ConfigDefinitionFactory;

class Reader extends \BlueSpice\Data\Settings\Reader {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	public function __construct( ConfigDefinitionFactory $factory, $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $loadBalancer, $context );
		$this->factory = $factory;
	}

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->factory );
	}

	protected function makeSecondaryDataProvider() {
		return new SecondaryDataProvider( $this->factory );
	}

	public function getSchema() {
		return new Schema();
	}

}
