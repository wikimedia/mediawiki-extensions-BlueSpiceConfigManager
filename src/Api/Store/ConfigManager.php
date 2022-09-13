<?php

namespace BlueSpice\ConfigManager\Api\Store;

use BlueSpice\ConfigManager\Data\ConfigManager\Store;
use BlueSpice\Context;

class ConfigManager extends \BlueSpice\Api\Store {

	/**
	 *
	 * @return string[]
	 */
	protected function getRequiredPermissions() {
		return [ 'bluespiceconfigmanager-viewspecialpage' ];
	}

	/**
	 *
	 * @return Store
	 */
	protected function makeDataStore() {
		return new Store(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			$this->services->getDBLoadBalancer(),
			$this->services->getService( 'BSConfigDefinitionFactory' )
		);
	}
}
