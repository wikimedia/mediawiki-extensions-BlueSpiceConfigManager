<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;
use BlueSpice\ConfigDefinitionFactory;

class Writer extends \BlueSpice\Data\Settings\Writer {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param \BlueSpice\Data\IReader $reader
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param \IContextSource $context
	 */
	public function __construct( ConfigDefinitionFactory $factory, \BlueSpice\Data\IReader $reader, $loadBalancer, \IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
		$this->factory = $factory;
	}
}
