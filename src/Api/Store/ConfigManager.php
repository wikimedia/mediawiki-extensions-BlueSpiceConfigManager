<?php

namespace BlueSpice\ConfigManager\Api\Store;
use BlueSpice\Context;
use BlueSpice\ConfigManager\Data\ConfigManager\Store;
use BlueSpice\Services;

class ConfigManager extends \BlueSpice\StoreApiBase {

	protected function getRequiredPermissions() {
		return [ 'bluespiceconfigmanager-viewspecialpage' ];
	}

	protected function makeDataStore() {
		$services = Services::getInstance();
		return new Store(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			$services->getDBLoadBalancer(),
			$services->getBSConfigDefinitionFactory()
		);
	}
}
