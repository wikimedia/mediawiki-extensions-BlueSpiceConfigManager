<?php

namespace BlueSpice\ConfigManager\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\ConfigManager\Data\ConfigManager\Record;
use BlueSpice\Context;
use BlueSpice\Data\RecordSet;
use BlueSpice\Data\Settings\Store;
use BlueSpice\Services;

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
		$factory = $this->getServices()->getBSConfigDefinitionFactory();
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

		$recordSet = $this->getStore()->getWriter()->write(
			new RecordSet( $records )
		);
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
		}
		return $result;
	}

	/**
	 *
	 * @return Services
	 */
	protected function getServices() {
		return Services::getInstance();
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			$this->getServices()->getDBLoadBalancer()
		);
	}
}
