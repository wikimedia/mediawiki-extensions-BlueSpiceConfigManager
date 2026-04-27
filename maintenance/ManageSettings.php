<?php

use BlueSpice\ConfigDefinition;
use MediaWiki\Json\FormatJson;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: __DIR__ . '/../../../maintenance/Maintenance.php';

class ManageSettings extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'List or set BlueSpice configuration settings.' );
		$this->addArg( 'command', 'Command to execute: "list" or "set"' );
		$this->addArg( 'name', 'Setting name (required for "set")', false );
		$this->addArg( 'value', 'Setting value (required for "set"). Use JSON for complex values.', false );
		$this->requireExtension( 'BlueSpiceFoundation' );
		$this->requireExtension( 'BlueSpiceConfigManager' );
	}

	public function execute() {
		$command = $this->getArg( 0 );
		switch ( $command ) {
			case 'list':
				$this->doList();
				break;
			case 'set':
				$this->doSet();
				break;
			default:
				$this->fatalError( "Unknown command \"$command\". Use \"list\" or \"set\"." );
		}
	}

	private function doList(): void {
		$factory = MediaWikiServices::getInstance()->getService( 'BSConfigDefinitionFactory' );
		$definitionNames = $factory->getRegisteredDefinitions();
		sort( $definitionNames );

		$dbSettings = $this->getDbSettings();

		$nameWidth = 10;
		foreach ( $definitionNames as $name ) {
			$nameWidth = max( $nameWidth, strlen( $name ) );
		}
		$nameWidth += 2;

		$header = sprintf(
			"%-{$nameWidth}s %-6s %s",
			'SETTING', 'IN DB', 'VALUE'
		);
		$this->output( $header . "\n" );
		$this->output( str_repeat( '-', strlen( $header ) + 20 ) . "\n" );

		foreach ( $definitionNames as $name ) {
			$definition = $factory->factory( $name );
			if ( !$definition ) {
				continue;
			}

			$inDb = isset( $dbSettings[$name] ) ? 'yes' : 'no';
			$value = $definition->getValue();
			$valueStr = $this->formatValue( $value );

			$this->output( sprintf(
				"%-{$nameWidth}s %-6s %s\n",
				$name, $inDb, $valueStr
			) );
		}
	}

	private function doSet(): void {
		$name = $this->getArg( 1 );
		$rawValue = $this->getArg( 2 );

		if ( $name === null || $rawValue === null ) {
			$this->fatalError( 'Usage: set <name> <value>' );
		}

		$factory = MediaWikiServices::getInstance()->getService( 'BSConfigDefinitionFactory' );
		$definition = $factory->factory( $name );
		if ( !$definition ) {
			$this->fatalError( "Unknown config definition: \"$name\"" );
		}

		$value = $this->parseValue( $rawValue, $definition );

		$validationResult = $definition->getHtmlFormField()->validate( $value, [] );
		if ( $validationResult !== true ) {
			$this->fatalError( "Validation failed for \"$name\": $validationResult" );
		}

		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$exists = $db->selectRow( 'bs_settings3', 's_name', [ 's_name' => $name ], __METHOD__ );

		$encodedValue = FormatJson::encode( $value );
		if ( $exists ) {
			$db->update(
				'bs_settings3',
				[ 's_value' => $encodedValue ],
				[ 's_name' => $name ],
				__METHOD__
			);
		} else {
			$db->insert(
				'bs_settings3',
				[ 's_name' => $name, 's_value' => $encodedValue ],
				__METHOD__
			);
		}

		$this->invalidateCache();
		$this->output( "Setting \"$name\" saved successfully.\n" );
	}

	/**
	 * @param string $rawValue
	 * @param ConfigDefinition $definition
	 * @return mixed
	 */
	private function parseValue( string $rawValue, ConfigDefinition $definition ) {
		$currentValue = $definition->getValue();

		if ( is_bool( $currentValue ) ) {
			$lower = strtolower( $rawValue );
			if ( in_array( $lower, [ 'true', '1', 'yes' ] ) ) {
				return true;
			}
			if ( in_array( $lower, [ 'false', '0', 'no' ] ) ) {
				return false;
			}
			$this->fatalError( "Invalid boolean value: \"$rawValue\". Use true/false, 1/0, or yes/no." );
		}

		if ( is_int( $currentValue ) ) {
			if ( !ctype_digit( ltrim( $rawValue, '-' ) ) ) {
				$this->fatalError( "Invalid integer value: \"$rawValue\"" );
			}
			return (int)$rawValue;
		}

		if ( is_array( $currentValue ) ) {
			$decoded = FormatJson::decode( $rawValue, true );
			if ( $decoded === null && $rawValue !== 'null' ) {
				$this->fatalError( "Invalid JSON value: \"$rawValue\". Arrays must be provided as JSON." );
			}
			return $decoded;
		}

		return $rawValue;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getDbSettings(): array {
		$db = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
		if ( !$db->tableExists( 'bs_settings3', __METHOD__ ) ) {
			return [];
		}
		$res = $db->select( 'bs_settings3', '*', '', __METHOD__ );
		$settings = [];
		foreach ( $res as $row ) {
			$settings[$row->s_name] = FormatJson::decode( $row->s_value, true );
		}
		return $settings;
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	private function formatValue( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}
		if ( is_array( $value ) ) {
			return FormatJson::encode( $value );
		}
		if ( $value === null ) {
			return 'null';
		}
		return (string)$value;
	}

	private function invalidateCache(): void {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$key = $cache->makeKey( 'BlueSpiceFoundation', 'bs_settings3' );
		$cache->delete( $key );
	}
}

$maintClass = ManageSettings::class;
require_once RUN_MAINTENANCE_IF_MAIN;
