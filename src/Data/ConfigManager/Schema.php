<?php

namespace BlueSpice\ConfigManager\Data\ConfigManager;

use BlueSpice\Data\FieldType;

class Schema extends \BlueSpice\Data\Settings\Schema {
	public function __construct() {
		parent::__construct( [
			Record::LABEL => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PATHS => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::LISTVALUE
			],
			Record::FORM => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
		]);
	}
}
