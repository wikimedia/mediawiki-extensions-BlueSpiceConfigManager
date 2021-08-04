<?php

namespace BlueSpice\ConfigManager\HookHandler;

use BlueSpice\ConfigManager\GlobalActionsManager;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class Main implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsManager',
			[
				'special-bluespice-configmanager' => [
					'factory' => function () {
						return new GlobalActionsManager();
					}
				]
			]
		);
	}
}
