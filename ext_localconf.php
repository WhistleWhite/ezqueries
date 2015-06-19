<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Frohland.' . $_EXTKEY,
	'ezqueriesplugin',
	array(
		'RecordManagement' => 'search, list, detail, new, create, edit, update, delete, empty, error',

	),
	array(
		'RecordManagement' => 'search, list, detail, new, create, edit, update, delete, empty, error',

	)
);

?>