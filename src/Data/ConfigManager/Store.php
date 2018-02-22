<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;
use BlueSpice\ConfigDefinitionFactory;

class Store extends \BlueSpice\Data\Settings\Store {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param \IContextSource $context
	 */
	public function __construct( $context, $loadBalancer, ConfigDefinitionFactory $factory ) {
		parent::__construct( $context, $loadBalancer );
		$this->factory = $factory;
	}

	public function getReader() {
		return new Reader(
			$this->factory,
			$this->loadBalancer,
			$this->context
		);
	}

	public function getWriter() {
		return new Writer(
			$this->factory,
			$this->getReader(),
			$this->loadBalancer,
			$this->context
		);
	}
}
