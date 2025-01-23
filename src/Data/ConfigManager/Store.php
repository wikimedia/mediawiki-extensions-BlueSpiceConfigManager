<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\ConfigDefinitionFactory;
use MediaWiki\Context\IContextSource;

class Store extends \BlueSpice\Data\Settings\Store {

	/**
	 *
	 * @var ConfigDefinitionFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param ConfigDefinitionFactory $factory
	 */
	public function __construct( $context, $loadBalancer, ConfigDefinitionFactory $factory ) {
		parent::__construct( $context, $loadBalancer );
		$this->factory = $factory;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			$this->factory,
			$this->loadBalancer,
			$this->context
		);
	}

	/**
	 *
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer(
			$this->factory,
			$this->getReader(),
			$this->loadBalancer,
			$this->context
		);
	}
}
