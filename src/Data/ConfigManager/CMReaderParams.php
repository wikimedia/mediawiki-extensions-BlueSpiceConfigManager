<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class CMReaderParams extends ReaderParams {

	/** @var bool */
	protected $forPublic = true;

	/**
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		parent::__construct( $params );
		$this->setIfAvailable( $this->forPublic, $params, 'forPublic' );
	}

	/**
	 * @return bool
	 */
	public function isForPublic(): bool {
		return $this->forPublic;
	}
}
