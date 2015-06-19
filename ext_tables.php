<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Frohland.' . $_EXTKEY,
	'ezqueriesplugin',
	'EasyQueries'
);

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_ezqueriesplugin'] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_ezqueriesplugin', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_ezqueriesplugin.xml');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'EasyQueries');
?>