<?php

namespace BlueSpice\ConfigManager\Api\Task;
use BlueSpice\Services;
use BlueSpice\ConfigManager\Data\ConfigManager\Record;
use BlueSpice\Data\Settings\Store;
use BlueSpice\Data\RecordSet;
use BlueSpice\Context;

class ConfigManager extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'save',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array('taskname' => array('read', 'edit'))
	 * @return type
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'save' => [ 'bluespiceconfigmanager-viewspecialpage' ],
		);
	}

	public function task_save( $taskData, $aParams ) {
		$result = $this->makeStandardReturn();

		$records = [];
		$factory = $this->getServices()->getBSConfigDefinitionFactory();
		foreach( (array)$taskData as $cfgName => $value ) {
			if( !$field = $factory->factory( $cfgName ) ) {
				continue;
			}
			$record = new Record( (object)[
				Record::NAME => $field->getName(),
				Record::VALUE => $value,
			]);

			$res = $field->getHtmlFormField()->validate(
				$value,
				(array)$taskData
			);
			if( $res !== true ) {
				$record->getStatus()->fatal( $res );
			}
			$records[] = $record;
		}

		$recordSet = $this->getStore()->getWriter()->write(
			new RecordSet( $records )
		);
		foreach( $recordSet->getRecords() as $record ) {
			if( $record->getStatus()->isOK() ) {
				continue;
			}
			$result->message .= $record->get( Record::NAME ) . ': ';
			$result->message .= $record->getStatus()->getHTML( false, false );
			$result->message .= \Html::element( 'br' );
		}
		if( empty( $result->message ) ) {
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
