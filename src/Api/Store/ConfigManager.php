<?php

namespace BlueSpice\ConfigManager\Api\Store;

use BlueSpice\ConfigManager\Data\ConfigManager\CMReaderParams;
use BlueSpice\ConfigManager\Data\ConfigManager\Store;
use BlueSpice\Context;
use MediaWiki\Context\RequestContext;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

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
			new Context( RequestContext::getMain(), $this->getConfig() ),
			$this->services->getDBLoadBalancer(),
			$this->services->getService( 'BSConfigDefinitionFactory' )
		);
	}

	/**
	 *
	 * @return ReaderParams
	 */
	protected function getReaderParams() {
		return new CMReaderParams( [
			'query' => $this->getParameter( 'query', null ),
			'start' => $this->getParameter( 'start', null ),
			'limit' => $this->getParameter( 'limit', null ),
			'filter' => $this->getParameter( 'filter', null ),
			'sort' => $this->getParameter( 'sort', null ),
			'forPublic' => true
		] );
	}

}
