<?php
$specialPageAliases = [];

/** English */
$specialPageAliases['en'] = [
	'BlueSpiceConfigManager' => [
		'BlueSpiceConfigManager',
		'BlueSpice Config Manager',
]];
//Backwards compatibillity
$specialPageAliases['en']['BlueSpiceConfigManager'][] = 'BlueSpicePreferences';
$specialPageAliases['en']['BlueSpiceConfigManager'][] = 'BlueSpice preferences';


/** German (Deutsch) */
$specialPageAliases['de'] = [
	'BlueSpiceConfigManager' => [
		'BlueSpiceConfigManager',
		'BlueSpice Konfigurationsverwaltung',
]];
//Backwards compatibillity
$specialPageAliases['de']['BlueSpiceConfigManager'][] = 'BlueSpice Einstellungen';
$specialPageAliases['de']['BlueSpiceConfigManager'][] = 'BlueSpicePreferences';
