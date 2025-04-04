<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\IReader;

class Writer extends \BlueSpice\Data\Settings\Writer {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 * @param ConfigDefinitionFactory $factory
	 * @param IReader $reader
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct(
		ConfigDefinitionFactory $factory, IReader $reader,
		$loadBalancer, ?IContextSource $context = null
	) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
		$this->factory = $factory;
	}
}
