<?php

namespace BlueSpice\ConfigManager\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\ConfigManager\Data\ConfigManager\Record;
use BlueSpice\ConfigManager\Data\ConfigManager\Store as ConfigManagerStore;
use BlueSpice\Context;
use BlueSpice\Data\Settings\Store;
use FormatJson;
use ManualLogEntry;
use MWStake\MediaWiki\Component\DataStore\Filter\StringValue;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\RecordSet;
use SpecialPage;

class ConfigManager extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'save',
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return type
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'save' => [ 'bluespiceconfigmanager-viewspecialpage' ],
		];
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_save( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();

		$records = [];
		$factory = $this->services->getService( 'BSConfigDefinitionFactory' );
		foreach ( (array)$taskData as $cfgName => $value ) {
			$field = $factory->factory( $cfgName );
			if ( !$field ) {
				continue;
			}
			$record = new Record( (object)[
				Record::NAME => $field->getName(),
				Record::VALUE => $value,
			] );

			$res = $field->getHtmlFormField()->validate(
				$value,
				(array)$taskData
			);
			if ( $res !== true ) {
				$record->getStatus()->fatal( $res );
			}
			$records[] = $record;
		}

		$newRecordSet = new RecordSet( $records );
		$changes = $this->compareRecords( $newRecordSet );
		$recordSet = $this->getStore()->getWriter()->write( $newRecordSet );
		foreach ( $recordSet->getRecords() as $record ) {
			if ( $record->getStatus()->isOK() ) {
				continue;
			}
			$result->message .= $record->get( Record::NAME ) . ': ';
			$result->message .= $record->getStatus()->getHTML( false, false );
			$result->message .= \Html::element( 'br' );
		}
		if ( empty( $result->message ) ) {
			$result->success = true;
			$this->doLog( $changes );
		}
		return $result;
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->services->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return ConfigManagerStore
	 */
	private function getCMStore() {
		return new ConfigManagerStore(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->services->getDBLoadBalancer(),
			$this->services->getService( 'BSConfigDefinitionFactory' )
		);
	}

	/**
	 *
	 * @param RecordSet $recordSet
	 * @return array
	 */
	private function compareRecords( $recordSet ): array {
		$records = $recordSet->getRecords();
		$changes = [];
		foreach ( $records as $record ) {
			$recordName = $record->get( Record::NAME );
			$originalRecordSet = $this->getCMStore()->getReader()->read(
				new ReaderParams( [
					ReaderParams::PARAM_FILTER => [
						[
							'type' => 'string',
							'field' => Record::NAME,
							'value' => $recordName,
							'comparison' => StringValue::COMPARISON_EQUALS
						]
					]
				] )
			);
			$originalRecords = $originalRecordSet->getRecords();
			$recordValue = $record->get( Record::VALUE );
			$originalValue = !empty( $originalRecords ) ?
				$originalRecords[0]->get( Record::VALUE ) : null;
			if ( $originalValue !== $recordValue ) {
				$changes[$recordName] = [
					'configName' => $recordName,
					'originalValue' => $originalValue,
					'newValue' => $recordValue
				];
			}
		}
		return $changes;
	}

	/**
	 *
	 * @param array $changes
	 * @return void
	 */
	private function doLog( $changes ) {
		foreach ( $changes as $name => $change ) {
			if ( in_array( $name, $this->logExcludeList() ) ) {
				continue;
			}

			$this->insertLog(
				'modify',
				[
					'4::configName' => $change['configName'],
					'5::originalValue' => $this->stringifyLogValue( $change['originalValue'] ),
					'6::newValue' => $this->stringifyLogValue( $change['newValue'] )
				]
			);
		}
	}

	/**
	 *
	 * @param string $type
	 * @param array $params
	 */
	private function insertLog( $type, $params ) {
		$targetTitle = SpecialPage::getTitleFor( 'ConfigManager' );
		$user = $this->getUser();

		$logger = new ManualLogEntry( 'bs-config-manager', $type );
		$logger->setPerformer( $user );
		$logger->setTarget( $targetTitle );
		$logger->setParameters( $params );
		$logger->insert( $this->services->getDBLoadBalancer()->getConnection( DB_PRIMARY ) );
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	private function stringifyLogValue( $value ): string {
		$logString = '';
		if ( $value === true ) {
			$logString = 'true';
		} elseif ( $value === false ) {
				$logString = 'false';
		} elseif ( is_numeric( $value ) ) {
			$logString = (string)$value;
		} else {
			$logString = FormatJson::encode( $value );
		}
		return $logString;
	}

	/**
	 *
	 * @return array
	 */
	private function logExcludeList(): array {
		return $this->getContext()->getConfig()->get( 'ConfigManagerLogExcludeList' );
	}
}
