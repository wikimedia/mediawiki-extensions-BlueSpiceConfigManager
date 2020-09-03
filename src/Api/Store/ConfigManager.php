<?php

namespace BlueSpice\ConfigManager\Api\Store;

use BlueSpice\ConfigManager\Data\ConfigManager\Store;
use BlueSpice\Context;
use MediaWiki\MediaWikiServices;

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
		$services = MediaWikiServices::getInstance();
		return new Store(
			new Context( \RequestContext::getMain(), $this->getConfig() ),
			$services->getDBLoadBalancer(),
			$services->getService( 'BSConfigDefinitionFactory' )
		);
	}
}
