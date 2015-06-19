<?php
namespace Frohland\Ezqueries\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) Florian Rohland <info@florianrohland.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Wizard-Utility
 *
 * Utility class for wizards.
 */
class WizardUtility {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Load TS from page
	 *
	 * @param string $pageUid
	 * @return array $conf
	 */
	private function loadTS($pageUid) {
		$sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$rootLine = $sysPageObj -> getRootLine($pageUid);
		$TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
		$TSObj -> tt_track = 0;
		$TSObj -> init();
		$TSObj -> runThroughTemplates($rootLine);
		$TSObj -> generateConfig();
		return $TSObj -> setup['plugin.']['tx_ezqueries_ezqueriesplugin.']['settings.'];
	}

	/**
	 * Generate a button in the FlexForm which provides 'onClick' all selectable markers for the template
	 *
	 * @param array $config Plugin configuration
	 * @return string $output HTML code for displaying the button in the FlexForm
	 */
	public function generateSelectableMarkersButton($config) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$recordManagement = $this -> objectManager -> get('Frohland\\Ezqueries\\Domain\\Repository\\RecordManagementRepository');
		$output = '';

		// If FlexForm data is available
		if ($config['row']['pi_flexform'] != NULL) {
			// Get FlexForm data from $config array
			$piValues = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);

			// Check template type
			switch($config['wConf']['params']['templateType']) {
				case 'list' :
					$sheet = 'sLIST';
					$columnSettings = 'settings_listColumns';
					break;
				case 'detail' :
					$sheet = 'sDETAIL';
					$columnSettings = 'settings_detailColumns';
					break;
				case 'edit' :
					$sheet = 'sEDIT';
					$columnSettings = 'settings_editColumns';
					break;
				case 'search' :
					$sheet = 'sSEARCH';
					$columnSettings = 'settings_searchColumns';
					break;
				case 'new' :
					$sheet = 'sNEW';
					$columnSettings = 'settings_newColumns';
					break;
				default :
					$sheet = 'sDEF';
					$columnSettings = 'settings_columns';
					break;
			}

			$configData = $piValues['data'][$sheet]['lDEF'];

			// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
			foreach ($configData as $key => $value) {
				$key = str_replace('.', '_', $key);
				$compatibleConfigData[$key] = $value;
			}

			// Get column settings
			$columnSettings = $compatibleConfigData[$columnSettings]['vDEF'];

			// Get FlexForm data from $config array
			$configData = $piValues['data']['sDEF']['lDEF'];

			// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
			foreach ($configData as $key => $value) {
				$key = str_replace('.', '_', $key);
				$compatibleConfigData[$key] = $value;
			}

			// Get tables
			$tables = explode(',', urldecode($compatibleConfigData['settings_tables']['vDEF']));
			foreach ($tables as &$table) {
				$table = substr($table, 0, stripos($table, '|'));
			}
			unset($table);

			if ($tables[0]) {
				// Get TS-Config of the page
				$pid = $config['row']['pid'];
				$tsData = $this -> loadTS($pid);

				// Set database connection
				if (isset($tsData['db.']['server'])) {
					$server = $tsData['db.']['server'];
				} else {
					$server = $compatibleConfigData['settings_server']['vDEF'];
				}
				if (isset($tsData['db.']['database'])) {
					$database = $tsData['db.']['database'];
				} else {
					$database = $compatibleConfigData['settings_database']['vDEF'];
				}
				if (isset($tsData['db.']['username'])) {
					$username = $tsData['db.']['username'];
				} else {
					$username = $compatibleConfigData['settings_username']['vDEF'];
				}
				if (isset($tsData['db.']['password'])) {
					$password = $tsData['db.']['password'];
				} else {
					$password = $compatibleConfigData['settings_password']['vDEF'];
				}
				if (isset($tsData['db.']['useConnection'])) {
					$useConnection = $tsData['db.']['useConnection'];
				} else {
					$useConnection = $compatibleConfigData['settings_useConnection']['vDEF'];
				}
				$recordManagement -> setDatabaseConnection($server, $database, $username, $password, $useConnection);

				$columns = array();
				// If no columns are slected
				if ($columnSettings == NULL) {
					// Get all column names from tables
					if ($config['wConf']['params']['templateType'] == 'edit' || $config['wConf']['params']['templateType'] == 'new') {
						$table[] = $tables[0];
						$allColumns = $recordManagement -> getColumnsFromTables($table);
					} else {
						$allColumns = $recordManagement -> getColumnsFromTables($tables);
					}

					foreach ($allColumns as $allColumn) {
						$columns[$allColumn['name']] = $allColumn['name'];
					}
				} else {
					// Convert $columns string to array
					$columns = explode(',', urldecode($columnSettings));
					foreach ($columns as &$column) {
						$column = substr($column, 0, stripos($column, '|'));
					}
					unset($column);
				}

				if ($columns != FALSE) {
					// Selectable markers
					$formValueMarkerStart = '<formelement>';
					$formValueMarkerEnd = '</formelement>';
					$valueMarkerStart = '<value>';
					$valueMarkerEnd = '</value>';
					$sortMarkerStart = '<sort>';
					$sortMarkerEnd = '</sort>';
					$submitMarkerStart = '<submit>';
					$submitMarkerEnd = '</submit>';
					$detailMarkerStart = '<detail>';
					$detailMarkerEnd = '</detail>';
					$editMarkerStart = '<edit>';
					$editMarkerEnd = '</edit>';
					$previousMarkerStart = '<previous>';
					$previousMarkerEnd = '</previous>';
					$nextMarkerStart = '<next>';
					$nextMarkerEnd = '</next>';
					$closeMarkerStart = '<close>';
					$closeMarkerEnd = '</close>';
					$deleteMarkerStart = '<delete>';
					$deleteMarkerEnd = '</delete>';
					$newMarkerStart = '<new>';
					$newMarkerEnd = '</new>';
					$searchMarkerStart = '<search>';
					$searchMarkerEnd = '</search>';
					$listMarkerStart = '<list>';
					$listMarkerEnd = '</list>';
					$recordMarkerStart = '<record>';
					$recordMarkerEnd = '</record>';
					$parityMarker = '<parity />';
					$recordCountMarker = '<recordcount />';

					// Script for inserting markers
					$output = '<script type="text/javascript">
									<!--
									function tx_ezqueries_insertMarkers' . $config['wConf']['params']['templateType'] . '(aTag, eTag) {
									  var input = document.forms["' . $config['formName'] . '"].elements["' . $config['itemName'] . '"];
									  input.focus();
									  /* für Internet Explorer */
									  if(typeof document.selection != "undefined") {
									    /* Einfügen des Formatierungscodes */
									    var range = document.selection.createRange();
									    var insText = range.text;
									    range.text = aTag + insText + eTag;
									    /* Anpassen der Cursorposition */
									    range = document.selection.createRange();
									    if (insText.length == 0) {
									      range.move("character", -eTag.length);
									    } else {
									      range.moveStart("character", aTag.length + insText.length + eTag.length);
									    }
									    range.select();
									  }
									  /* für neuere auf Gecko basierende Browser */
									  else if(typeof input.selectionStart != "undefined")
									  {
									    /* Einfügen des Formatierungscodes */
									    var start = input.selectionStart;
									    var end = input.selectionEnd;
									    var insText = input.value.substring(start, end);
									    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
									    /* Anpassen der Cursorposition */
									    var pos;
									    if (insText.length == 0) {
									      pos = start + aTag.length;
									    } else {
									      pos = start + aTag.length + insText.length + eTag.length;
									    }
									    input.selectionStart = pos;
									    input.selectionEnd = pos;
									  }
									  /* für die übrigen Browser */
									  else
									  {
									    /* Abfrage der Einfügeposition
									    var pos;
									    var re = new RegExp("^[0-9]{0,3}$");
									    while(!re.test(pos)) {
									      pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
									    }
									    if(pos > input.value.length) {
									      pos = input.value.length;
									    } */
									    /* Einfügen des Formatierungscodes
									    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
									    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos); */
									  }
									}
									//-->
									</script>';

					$output .= '<p style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_selectablemarkers', 'ezqueries') . '</p>';

					// If function parameter (defined in the xml file of the FlexForm) is list
					if ($config['wConf']['params']['templateType'] == 'list') {
						$baseFrame = '';
						// Generate marker list for list template
						$output .= '<table style="float: left; margin-right: 10px;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_columnvalues', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						foreach ($columns as $column) {
							$output .= '<tr><td>';
							$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $valueMarkerStart . $column . $valueMarkerEnd . '\',\'\'); return false;" value="' . $valueMarkerStart . $column . $valueMarkerEnd . '" />';
							$output .= '</td></tr>';
						}
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_other', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $parityMarker . '\',\'\'); return false;" value="' . $parityMarker . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $recordCountMarker . '\',\'\'); return false;" value="' . $recordCountMarker . '" />';
						$output .= '</td></tr>';
						$output .= '</table>';

						$output .= '<table style="float: left;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_options', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_linktext', 'ezqueries');
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $detailMarkerStart . '\',\'' . $detailMarkerEnd . '\'); return false;" value="' . $detailMarkerStart . $text . $detailMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $editMarkerStart . '\',\'' . $editMarkerEnd . '\'); return false;" value="' . $editMarkerStart . $text . $editMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $deleteMarkerStart . '\',\'' . $deleteMarkerEnd . '\'); return false;" value="' . $deleteMarkerStart . $text . $deleteMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $newMarkerStart . '\',\'' . $newMarkerEnd . '\'); return false;" value="' . $newMarkerStart . $text . $newMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $searchMarkerStart . '\',\'' . $searchMarkerEnd . '\'); return false;" value="' . $searchMarkerStart . $text . $searchMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $listMarkerStart . '\',\'' . $listMarkerEnd . '\'); return false;" value="' . $listMarkerStart . $text . $listMarkerEnd . '" />';
						$output .= '</td></tr>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_column', 'ezqueries');
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $sortMarkerStart . '\',\'' . $sortMarkerEnd . '\'); return false;" value="' . $sortMarkerStart . $text . $sortMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_list', 'ezqueries') . '</b>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_listelements', 'ezqueries');
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerslist(\'' . $recordMarkerStart . '\',\'' . $recordMarkerEnd . '\'); return false;" value="' . $recordMarkerStart . $text . $recordMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '</table>';
					}

					// If function parameter (defined in the xml file of the FlexForm) is detail
					if ($config['wConf']['params']['templateType'] == 'detail') {
						// Generate marker list for detail template
						$output .= '<table style="float: left; margin-right: 10px;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_columnvalues', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						foreach ($columns as $column) {
							$output .= '<tr><td>';
							$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $valueMarkerStart . $column . $valueMarkerEnd . '\',\'\'); return false;" value="' . $valueMarkerStart . $column . $valueMarkerEnd . '" />';
							$output .= '</td></tr>';
						}
						$output .= '</table>';

						$output .= '<table style="float: left;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_options', 'ezqueries') . '</b>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_linktext', 'ezqueries');
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $editMarkerStart . '\',\'' . $editMarkerEnd . '\'); return false;" value="' . $editMarkerStart . $text . $editMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $deleteMarkerStart . '\',\'' . $deleteMarkerEnd . '\'); return false;" value="' . $deleteMarkerStart . $text . $deleteMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $listMarkerStart . '\',\'' . $listMarkerEnd . '\'); return false;" value="' . $listMarkerStart . $text . $listMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $nextMarkerStart . '\',\'' . $nextMarkerEnd . '\'); return false;" value="' . $nextMarkerStart . $text . $nextMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $previousMarkerStart . '\',\'' . $previousMarkerEnd . '\'); return false;" value="' . $previousMarkerStart . $text . $previousMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersdetail(\'' . $closeMarkerStart . '\',\'' . $closeMarkerEnd . '\'); return false;" value="' . $closeMarkerStart . $text . $closeMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '</table>';
					}

					// If function parameter (defined in the xml file of the FlexForm) is edit
					if ($config['wConf']['params']['templateType'] == 'edit') {
						// Generate marker list for edit template
						$output .= '<table style="float: left; margin-right: 10px;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_formelements', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_buttontext', 'ezqueries');
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $submitMarkerStart . '\',\'' . $submitMarkerEnd . '\'); return false;" value="' . $submitMarkerStart . $text . $submitMarkerEnd . '" />';
						$output .= '</td></tr>';
						foreach ($columns as $column) {
							$output .= '<tr><td>';
							$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $formValueMarkerStart . $column . $formValueMarkerEnd . '\',\'\'); return false;" value="' . $formValueMarkerStart . $column . $formValueMarkerEnd . '" />';
							$output .= '</td></tr>';
						}
						$output .= '</table>';

						$output .= '<table style="float: left;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_options', 'ezqueries') . '</b>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_linktext', 'ezqueries');
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $detailMarkerStart . '\',\'' . $detailMarkerEnd . '\'); return false;" value="' . $detailMarkerStart . $text . $detailMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $deleteMarkerStart . '\',\'' . $deleteMarkerEnd . '\'); return false;" value="' . $deleteMarkerStart . $text . $deleteMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $listMarkerStart . '\',\'' . $listMarkerEnd . '\'); return false;" value="' . $listMarkerStart . $text . $listMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $nextMarkerStart . '\',\'' . $nextMarkerEnd . '\'); return false;" value="' . $nextMarkerStart . $text . $nextMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $previousMarkerStart . '\',\'' . $previousMarkerEnd . '\'); return false;" value="' . $previousMarkerStart . $text . $previousMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersedit(\'' . $closeMarkerStart . '\',\'' . $closeMarkerEnd . '\'); return false;" value="' . $closeMarkerStart . $text . $closeMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '</table>';
					}

					// If function parameter (defined in the xml file of the FlexForm) is new
					if ($config['wConf']['params']['templateType'] == 'new') {
						// Generate marker list for new template
						$output .= '<table style="float: left; margin-right: 10px;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_formelements', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_buttontext', 'ezqueries');
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersnew(\'' . $submitMarkerStart . '\',\'' . $submitMarkerEnd . '\'); return false;" value="' . $submitMarkerStart . $text . $submitMarkerEnd . '" />';
						$output .= '</td></tr>';
						foreach ($columns as $column) {
							$output .= '<tr><td>';
							$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersnew(\'' . $formValueMarkerStart . $column . $formValueMarkerEnd . '\',\'\'); return false;" value="' . $formValueMarkerStart . $column . $formValueMarkerEnd . '" />';
							$output .= '</td></tr>';
						}
						$output .= '</table>';

						$output .= '<table style="float: left;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_options', 'ezqueries') . '</b>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_linktext', 'ezqueries');
						$output .= '</td></tr>';
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkersnew(\'' . $closeMarkerStart . '\',\'' . $closeMarkerEnd . '\'); return false;" value="' . $closeMarkerStart . $text . $closeMarkerEnd . '" />';
						$output .= '</td></tr>';
						$output .= '</table>';
					}

					// If function parameter (defined in the xml file of the FlexForm) is search
					if ($config['wConf']['params']['templateType'] == 'search') {
						// Generate marker list for search template
						$output .= '<table style="float: left; margin-right: 10px;">';
						$output .= '<tr><td>';
						$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_formelements', 'ezqueries') . '</b>';
						$output .= '</td></tr>';
						$text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_buttontext', 'ezqueries');
						$output .= '<tr><td>';
						$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerssearch(\'' . $submitMarkerStart . '\',\'' . $submitMarkerEnd . '\'); return false;" value="' . $submitMarkerStart . $text . $submitMarkerEnd . '" />';
						$output .= '</td></tr>';
						foreach ($columns as $column) {
							$output .= '<tr><td>';
							$output .= '<input style="clear: both; float: left; margin: 5px; width: 100%;" type="submit" onclick="tx_ezqueries_insertMarkerssearch(\'' . $formValueMarkerStart . $column . $formValueMarkerEnd . '\',\'\'); return false;" value="' . $formValueMarkerStart . $column . $formValueMarkerEnd . '" />';
							$output .= '</td></tr>';
						}
						$output .= '</table>';
					}
				} else {
					$output .= '<table style="float: left; margin-right: 10px;">';
					$output .= '<tr><td>';
					$output .= '<b style="margin: 5px;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_wizard_nomarkersavailable', 'ezqueries') . '</b>';
					$output .= '</td></tr>';
					$output .= '</table>';
				}
			}
		}

		return $output;
	}

	/**
	 * Generate a form in the FlexForm to add additional columns
	 *
	 * @param array $config Plugin configuration
	 * @return string $output HTML code
	 */
	public function additionalColumns($config) {
		$output = '';

		// If FlexForm data is available
		if ($config['row']['pi_flexform'] != NULL) {
			// Get FlexForm data from $PA array
			$piValues = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);
			$configData = $piValues['data']['sCOLUMNS']['lDEF'];

			// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
			foreach ($configData as $key => $value) {
				$key = str_replace('.', '_', $key);
				$compatibleConfigData[$key] = $value;
			}

			$additionalColumns = explode(',', urldecode($compatibleConfigData['settings_additionalColumns']['vDEF']));
			array_pop($additionalColumns);

			// Hide config item
			$config['item'] = '<div style="display: none">' . $config['item'] . '</div>';

			// Script
			$output .= '<script>function setAdditionalColumns(){';
			$output .= 'var columns = "";
							var column = document.getElementById("tx_ezqueries_additional_column").value;
							document.getElementById("tx_ezqueries_additional_columns").value += column + ",";
							columns += document.getElementById("tx_ezqueries_additional_columns").value;
							document.getElementById("tx_ezqueries_additional_columns_info_text").innerHTML += "Die Spalte <b>" + column + "</b> wird nach dem Speichern hinzugefügt.<br />"
							return columns;';
			$output .= '}';
			$output .= 'function deleteAdditionalColumn(object){';
			$output .= 'var parentObject = object.parentNode;
							column = parentObject.getAttribute("class");
							document.getElementById("tx_ezqueries_additional_columns_list").removeChild(parentObject);
							var oldValue = document.getElementById("tx_ezqueries_additional_columns").value;
							var replaceString = column + ",";
							var newValue = oldValue.replace(replaceString, "");
							document.getElementById("tx_ezqueries_additional_columns").value = newValue;
							return newValue;
				';
			$output .= '}</script>';

			$onClickAdd = "document." . $config['formName'] . "['" . $config['itemName'] . "'].value=setAdditionalColumns(); return false;";
			$onClickDelete = "document." . $config['formName'] . "['" . $config['itemName'] . "'].value=deleteAdditionalColumn(this); return false;";

			// Output
			$output .= '<ul id="tx_ezqueries_additional_columns_list" style="list-style: none; padding: 0; margin: 0; float: left;">';
			foreach ($additionalColumns as $additionalColumn) {
				$output .= '<li class="' . trim($additionalColumn) . '" style="background: #fff; border: 1px solid #999; padding: 5px; margin-right: 10px; margin-bottom: 10px; float: left;"><span style="float: left;">' . trim($additionalColumn) . '</span><a style="float: left; margin-left: 10px;" href="#" onclick="' . htmlspecialchars($onClickDelete) . '">X</a></li>';
			}
			$output .= '</ul>';
			$output .= '<div id="tx_ezqueries_additional_columns_info_text" style="float: left; clear: both; margin-bottom: 10px;"></div>';
			$output .= '<input type="hidden" id="tx_ezqueries_additional_columns" style="width: 400px; float: left; clear: both; padding: 5px; margin-bottom: 10px;" value="';
			foreach ($additionalColumns as $additionalColumn) {
				$output .= trim($additionalColumn) . ',';
			}
			$output .= '" readonly="readonly" disabled="disabled" />';

			$output .= '<input id="tx_ezqueries_additional_column" type="text" style="float: left; clear: both; height: 20px; padding: 5px; width: 200px; margin-right: 10px;" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_insertcolumnname', 'ezqueries') . '" />';
			$output .= '<input type="submit" style="float: left; height: 30px; padding: 5px; line-height: 30px;" onclick="' . htmlspecialchars($onClickAdd) . '" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_addcolumn', 'ezqueries') . '" />';

		}
		return $output;
	}

	/**
	 * Generate a form in the FlexForm to configure the tables columns
	 *
	 * @param array $config Plugin configuration
	 * @return string $output HTML code
	 */
	public function configColumns($config) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$recordManagement = $this -> objectManager -> get('Frohland\\Ezqueries\\Domain\\Repository\\RecordManagementRepository');
		$output = '';

		// If FlexForm data is available
		if ($config['row']['pi_flexform'] != NULL) {
			// Get FlexForm data from $PA array
			$piValues = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);
			$configData = $piValues['data']['sDEF']['lDEF'];

			// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
			foreach ($configData as $key => $value) {
				$key = str_replace('.', '_', $key);
				$compatibleConfigData[$key] = $value;
			}

			// Get tables
			$tables = explode(',', urldecode($compatibleConfigData['settings_tables']['vDEF']));
			foreach ($tables as &$table) {
				$table = substr($table, 0, stripos($table, '|'));
			}
			unset($table);

			if ($tables[0]) {
				// Get TS-Config of the page
				$pid = $config['row']['pid'];
				$tsData = $this -> loadTS($pid);

				// Set database connection
				if (isset($tsData['db.']['server'])) {
					$server = $tsData['db.']['server'];
				} else {
					$server = $compatibleConfigData['settings_server']['vDEF'];
				}
				if (isset($tsData['db.']['database'])) {
					$database = $tsData['db.']['database'];
				} else {
					$database = $compatibleConfigData['settings_database']['vDEF'];
				}
				if (isset($tsData['db.']['username'])) {
					$username = $tsData['db.']['username'];
				} else {
					$username = $compatibleConfigData['settings_username']['vDEF'];
				}
				if (isset($tsData['db.']['password'])) {
					$password = $tsData['db.']['password'];
				} else {
					$password = $compatibleConfigData['settings_password']['vDEF'];
				}
				if (isset($tsData['db.']['useConnection'])) {
					$useConnection = $tsData['db.']['useConnection'];
				} else {
					$useConnection = $compatibleConfigData['settings_useConnection']['vDEF'];
				}
				$recordManagement -> setDatabaseConnection($server, $database, $username, $password, $useConnection);

				// Get all column names from tables
				$allColumns = $recordManagement -> getColumnsFromTables($tables);
				$columns = array();

				foreach ($allColumns as $allColumn) {
					$columns[$allColumn['name']] = $allColumn['name'];
				}

				$configDataColumns = $piValues['data']['sCOLUMNS']['lDEF'];

				if ($configDataColumns == NULL) {
					$compatibleConfigDataColumns = $compatibleConfigData;
				} else {
					// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
					foreach ($configDataColumns as $key => $value) {
						$key = str_replace('.', '_', $key);
						$compatibleConfigDataColumns[$key] = $value;
					}
				}

				// Get column configuration
				$columnTypes = $recordManagement -> getColumnTypes($tables);
				$columnConfiguration = $compatibleConfigDataColumns['settings_columnConfiguration']['vDEF'];

				// Get additonal columns
				$additionalColumnsList = $compatibleConfigDataColumns['settings_additionalColumns']['vDEF'];
				$additionalColumns = explode(',', $additionalColumnsList);
				array_pop($additionalColumns);

				foreach ($additionalColumns as $additionalColumn) {
					if (trim($additionalColumn) !== NULL && trim($additionalColumn) !== '') {
						$columns[$additionalColumn] = trim($additionalColumn);
					}
				}

				if ($columnConfiguration === '') {
					$columnConfiguration = $compatibleConfigData['settings_columnConfiguration']['vDEF'];
				}

				if ($columnConfiguration != "") {
					$configuration = explode('-->', $columnConfiguration);
					array_pop($configuration);

					foreach ($configuration as $conf) {
						$confColumn = trim(substr($conf, 0, strpos($conf, '<--')));
						$confValues = trim(substr($conf, strpos($conf, '<--') + 3, strlen($conf) - (strpos($conf, '<--') + 3)));

						$confValues = explode(';', $confValues);
						$confValue = array_shift($confValues);
						$confValue = trim(substr($confValue, strpos($confValue, ':') + 1, strlen($confValue) - (strpos($confValue, ':') + 1)));

						$additionalConfig = implode(';', $confValues);
						$columnTypes[$confColumn]['additionalConfig'] .= $additionalConfig;

						if ($columnTypes[$confColumn]['type'] == 'varchar' || $columnTypes[$confColumn]['type'] == 'text' || $columnTypes[$confColumn]['type'] == 'boolean') {
							$columnTypes[$confColumn]['render'] = $confValue;
						}
						if ($columnTypes[$confColumn]['type'] == 'int' || $columnTypes[$confColumn]['type'] == 'numeric') {
							$columnTypes[$confColumn]['numberformat'] = $confValue;
							$columnTypes[$confColumn]['decimals'] = intval($confValue[0]);
							if ($confValue[1] == 'x') {
								$columnTypes[$confColumn]['dec_point'] = '';
							} else {
								if ($confValue[1] == '_') {
									$columnTypes[$confColumn]['dec_point'] = ' ';
								} else {
									$columnTypes[$confColumn]['dec_point'] = $confValue[1];
								}
							}
							if ($confValue[2] == 'x') {
								$columnTypes[$confColumn]['thousands_sep'] = '';
							} else {
								if ($confValue[2] == '_') {
									$columnTypes[$confColumn]['thousands_sep'] = ' ';
								} else {
									$columnTypes[$confColumn]['thousands_sep'] = $confValue[2];
								}
							}
						}
						if ($columnTypes[$confColumn]['type'] == 'date') {
							$columnTypes[$confColumn]['dateformat'] = $confValue;
						}
						if ($columnTypes[$confColumn]['type'] == 'year') {
							$columnTypes[$confColumn]['yearformat'] = $confValue;
						}
						if (!isset($columnTypes[$confColumn]['type'])) {
							$columnTypes[$confColumn]['type'] = 'additional';
						}
					}
				}

				// Hide config item
				$config['item'] = '<div style="display: none;">' . $config['item'] . '</div>';

				// JavaScript to convert column configuration into a string
				$output .= '<script>
					function showHideConfig(element){
						if(document.getElementById("config" + element.id).style.display == "none"){
							document.getElementById("config" + element.id).style.display = "";
							document.getElementById(element.id).style.background = "#333";
							document.getElementById(element.id).style.border = "1px solid #333";
							document.getElementById(element.id).style.color = "#fff";
						}else{
							document.getElementById("config" + element.id).style.display = "none";
							document.getElementById(element.id).style.background = "#ececec";
							document.getElementById(element.id).style.border = "1px solid #bbb";
							document.getElementById(element.id).style.color = "#000";
						}
						if(document.getElementById("additionalconfig" + element.id).style.display == "none"){
							document.getElementById("additionalconfig" + element.id).style.display = "";
						}else{
							document.getElementById("additionalconfig" + element.id).style.display = "none";
						}
					}

					function getColumnsConfig(){
						var config = "";';

				foreach ($columns as $column) {
					$output .= 'config += "' . $column . ' <-- ";';
					$output .= 'config += "config:" + document.getElementById("' . $column . '_config").value + ";";';
					$output .= 'config += document.getElementById("' . $column . '_additionalconfig").value;';
					$output .= 'config += "--> ";';
				}

				$output .= 'alert("' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_savesuccess', 'ezqueries') . '"); return config;
					}
				</script>';
				$onClick = "document." . $config['formName'] . "['" . $config['itemName'] . "'].value=getColumnsConfig(); return false;";
				$output .= '<input type="submit" style="margin: 20px 0; width: 70px;" onclick="' . htmlspecialchars($onClick) . '" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_submit', 'ezqueries') . '" />';

				// Create configuration table
				$color = '#ececec';
				$output .= '<table style="empty-cells: show; min-width: 500px; border-collapse: collapse; border-spacing: 0; border: none; clear: both;">';

				$id = 0;

				foreach ($columns as $column) {
					$output .= '<tr id="' . $id . '" onclick="showHideConfig(this);" style="cursor: pointer; border: 1px solid #bbb; background: #ececec">';
					$output .= '<td style="padding: 0 10px 0 10px; height: 30px; vertical-align: middle;">';
					$output .= '<span style="font-size: 1.2em; font-weight: bold;">' . $column . '</span></td>';
					$output .= '<td colspan="2" style="padding: 0 10px; height: 30px; vertical-align: middle; text-align: right; font-size: 0.8em">';
					if ($columnTypes[$column]['type'] == '' || $columnTypes[$column]['type'] == 'additional') {
						$output .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_additionalcolumn', 'ezqueries');
					} else {
						$output .= $columnTypes[$column]['type'];
					}

					if ($columnTypes[$column]['not_null'] == TRUE || $columnTypes[$column]['primary_key'] == TRUE) {
						$output .= ' (';
						if ($columnTypes[$column]['not_null'] == TRUE && $columnTypes[$column]['primary_key'] == TRUE) {
							$output .= 'not null - primary key';
						} else {
							if ($columnTypes[$column]['not_null'] == TRUE) {
								$output .= 'not null';
							}
							if ($columnTypes[$column]['primary_key'] == TRUE) {
								$output .= 'primary key';
							}
						}
						$output .= ')';
					}
					$output .= '</td>';
					$output .= '<td style="padding: 0 10px 0 10px; height: 30px; vertical-align: middle; font-size: 0.8em;">';
					if ($columnTypes[$column]['additionalConfig'] !== '') {
						$preview = strip_tags(substr($columnTypes[$column]['additionalConfig'], 0, 30));
						$output .= '[' . $preview . ' ...]';
					}
					$output .= '</td>';
					$output .= '</tr>';

					$output .= '<tr id="config' . $id . '" style="background: ' . $color . '; display: none; border-top: 1px solid #666; border-left: 1px solid #666; border-right: 1px solid #666;">';
					$output .= '<td style="padding: 5px 10px; height: 30px; vertical-align: middle;">';
					$output .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_configuration', 'ezqueries') . ' </td>';
					$output .= '<td></td>';

					// Output for varchar column
					if ($columnTypes[$column]['type'] == 'varchar') {
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_showdataas', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;"><select style="width: 100%;" id="' . $column . '_config" size="1">';
						$output .= '<option selected="selected" value="' . $columnTypes[$column]['render'] . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_' . $columnTypes[$column]['render'] . '', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text')
							$output .= '<option value="text">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text_long')
							$output .= '<option value="text_long">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text_long', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text_editor')
							$output .= '<option value="text_editor">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text_editor', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'email')
							$output .= '<option value="email">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_email', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'link')
							$output .= '<option value="link">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_link', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'image')
							$output .= '<option value="image">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_image', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'document')
							$output .= '<option value="document">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_document', 'ezqueries') . '</option>';
						$output .= '</select></td>';
					}
					// Output for text column
					if ($columnTypes[$column]['type'] == 'text') {
						//$output .= '<td colspan="2" style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_noconfiguration','ezqueries') . '<input id="' . $column . '_config" type="hidden" value="" /></td>';
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_showdataas', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;"><select style="width: 100%;" id="' . $column . '_config" size="1">';
						$output .= '<option selected="selected" value="' . $columnTypes[$column]['render'] . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_' . $columnTypes[$column]['render'] . '', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text')
							$output .= '<option value="text">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text_long')
							$output .= '<option value="text_long">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text_long', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'text_editor')
							$output .= '<option value="text_editor">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_text_editor', 'ezqueries') . '</option>';
						$output .= '</select></td>';
					}
					// Output for boolean column
					if ($columnTypes[$column]['type'] == 'boolean') {
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_showas', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;"><select style="width: 100%;" id="' . $column . '_config" size="1">';
						$output .= '<option selected="selected" value="' . $columnTypes[$column]['render'] . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_' . $columnTypes[$column]['render'] . '', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'checkbox')
							$output .= '<option value="checkbox">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_checkbox', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'yesno')
							$output .= '<option value="yesno">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_yesno', 'ezqueries') . '</option>';
						if ($columnTypes[$column]['render'] != 'number')
							$output .= '<option value="number">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_number', 'ezqueries') . '</option>';
						$output .= '</select></td>';
					}
					// Output for int column
					if ($columnTypes[$column]['type'] == 'int') {
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_numberformat', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;"><select style="width: 100%;" id="' . $column . '_config" size="1">';
						$output .= '<option selected="selected" value="' . $columnTypes[$column]['numberformat'] . '">' . number_format(1000.00, $columnTypes[$column]['decimals'], $columnTypes[$column]['dec_point'], $columnTypes[$column]['thousands_sep']) . '</option>';
						if ($columnTypes[$column]['numberformat'] != '0xx')
							$output .= '<option value="0xx">1000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x_')
							$output .= '<option value="0x_">1 000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x.')
							$output .= '<option value="0x.">1.000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x,')
							$output .= '<option value="0x,">1,000</option>';
						$output .= '</select></td>';
					}
					// Output for numeric column
					if ($columnTypes[$column]['type'] == 'numeric') {
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_numberformat', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;"><select style="width: 100%;" id="' . $column . '_config" size="1">';
						$output .= '<option selected="selected" value="' . $columnTypes[$column]['numberformat'] . '">' . number_format(1000.00, $columnTypes[$column]['decimals'], $columnTypes[$column]['dec_point'], $columnTypes[$column]['thousands_sep']) . '</option>';
						if ($columnTypes[$column]['numberformat'] != '0xx')
							$output .= '<option value="0xx">1000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x_')
							$output .= '<option value="0x_">1 000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x.')
							$output .= '<option value="0x.">1.000</option>';
						if ($columnTypes[$column]['numberformat'] != '0x,')
							$output .= '<option value="0x,">1,000</option>';
						if ($columnTypes[$column]['numberformat'] != '1.x')
							$output .= '<option value="1.x">1000.0</option>';
						if ($columnTypes[$column]['numberformat'] != '1,x')
							$output .= '<option value="1,x">1000,0</option>';
						if ($columnTypes[$column]['numberformat'] != '1._')
							$output .= '<option value="1._">1 000.0</option>';
						if ($columnTypes[$column]['numberformat'] != '1,_')
							$output .= '<option value="1,_">1 000,0</option>';
						if ($columnTypes[$column]['numberformat'] != '1,.')
							$output .= '<option value="1,.">1.000,0</option>';
						if ($columnTypes[$column]['numberformat'] != '1.,')
							$output .= '<option value="1.,">1,000.0</option>';
						if ($columnTypes[$column]['numberformat'] != '2.x')
							$output .= '<option value="2.x">1000.00</option>';
						if ($columnTypes[$column]['numberformat'] != '2,x')
							$output .= '<option value="2,x">1000,00</option>';
						if ($columnTypes[$column]['numberformat'] != '2._')
							$output .= '<option value="2._">1 000.00</option>';
						if ($columnTypes[$column]['numberformat'] != '2,_')
							$output .= '<option value="2,_">1 000,00</option>';
						if ($columnTypes[$column]['numberformat'] != '2,.')
							$output .= '<option value="2,.">1.000,00</option>';
						if ($columnTypes[$column]['numberformat'] != '2.,')
							$output .= '<option value="2.,">1,000.00</option>';
						if ($columnTypes[$column]['numberformat'] != '3.x')
							$output .= '<option value="3.x">1000.000</option>';
						if ($columnTypes[$column]['numberformat'] != '3,x')
							$output .= '<option value="3,x">1000,000</option>';
						if ($columnTypes[$column]['numberformat'] != '3._')
							$output .= '<option value="3._">1 000.000</option>';
						if ($columnTypes[$column]['numberformat'] != '3,_')
							$output .= '<option value="3,_">1 000,000</option>';
						if ($columnTypes[$column]['numberformat'] != '3,.')
							$output .= '<option value="3,.">1.000,000</option>';
						if ($columnTypes[$column]['numberformat'] != '3.,')
							$output .= '<option value="3.,">1,000.000</option>';
						if ($columnTypes[$column]['numberformat'] != '4.x')
							$output .= '<option value="4.x">1000.0000</option>';
						if ($columnTypes[$column]['numberformat'] != '4,x')
							$output .= '<option value="4,x">1000,0000</option>';
						if ($columnTypes[$column]['numberformat'] != '4._')
							$output .= '<option value="4._">1 000.0000</option>';
						if ($columnTypes[$column]['numberformat'] != '4,_')
							$output .= '<option value="4,_">1 000,0000</option>';
						if ($columnTypes[$column]['numberformat'] != '4,.')
							$output .= '<option value="4,.">1.000,0000</option>';
						if ($columnTypes[$column]['numberformat'] != '4.,')
							$output .= '<option value="4.,">1,000.0000</option>';
						$output .= '</select></td>';
					}
					// Output for date column
					if ($columnTypes[$column]['type'] == 'date') {
						$output .= '<td style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_dateformat', 'ezqueries') . '</td>';
						$output .= '<td style="vertical-align: middle; padding: 0 10px 0 0;">';
						$output .= '<input style="height: 20px; margin: 5px;" id="' . $column . '_config" value="' . $columnTypes[$column]['dateformat'] . '" />';
						$output .= $this -> showDateFormatOptions();
						$output .= '</td>';
					}
					// Output for year column
					if ($columnTypes[$column]['type'] == 'year') {
						$output .= '<td colspan="2" style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_noconfiguration', 'ezqueries') . '<input id="' . $column . '_config" type="hidden" value="" /></td>';
					}
					// Output for timestamp column
					if ($columnTypes[$column]['type'] == 'timestamp') {
						$output .= '<td colspan="2" style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_noconfiguration', 'ezqueries') . '<input id="' . $column . '_config" type="hidden" value="" /></td>';
					}
					// Output for time column
					if ($columnTypes[$column]['type'] == 'time') {
						$output .= '<td colspan="2" style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_noconfiguration', 'ezqueries') . '<input id="' . $column . '_config" type="hidden" value="" /></td>';
					}
					// Output for additional column
					if ($columnTypes[$column]['type'] == 'additional' || $columnTypes[$column]['type'] == '') {
						$output .= '<td colspan="2" style="padding: 0 10px 0 0; vertical-align: middle; text-align: right;">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_noconfiguration', 'ezqueries') . '<input id="' . $column . '_config" type="hidden" value="additional" /></td>';
					}
					$output .= '</td></tr>';

					// Additional configuration
					$output .= '<tr id="additionalconfig' . $id . '" style="background: ' . $color . '; display: none;  border-left: 1px solid #666; border-right: 1px solid #666;">';
					$output .= '<td colspan="4" style="padding: 5px 10px 10px 10px; height: 30px; vertical-align: top; border-bottom: 1px solid #666;">';
					$output .= '<textarea style="height: 80px; width: 100%; margin: 0;" id="' . $column . '_additionalconfig">' . $columnTypes[$column]['additionalConfig'] . '</textarea>';
					$output .= '</td></tr>';
					#$output .= '<tr style="background: #bbb; border: 1px solid #bbb;"><td colspan="4"></td></tr>';

					// Color change for rows
					if ($color == '#dadada') {$color = '#bfbfbf';
					} else {$color = '#ececec';
					}
					$id++;
				}
				$output .= '</table>';
				$output .= '<input type="submit" style="margin-top: 20px; width: 70px;" onclick="' . htmlspecialchars($onClick) . '" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('column_configuration_submit', 'ezqueries') . '" />';

				return $output;
			}
		}
	}

	/**
	 * Generate a button in the FlexForm which provides 'onClick' all slectable markers for the date format
	 *
	 * @return string $output HTML code for displaying the button in the FlexForm
	 */
	public function showDateFormatOptions() {
		$code = '';
		$code .= 'd' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_d', 'ezqueries') . '\n';
		$code .= 'j' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_j', 'ezqueries') . '\n';
		$code .= 'D' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_D', 'ezqueries') . '\n';
		$code .= 'l' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_l', 'ezqueries') . '\n';
		$code .= 'z' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_z', 'ezqueries') . '\n\n';
		$code .= 'M' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_M', 'ezqueries') . '\n';
		$code .= 'F' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_F', 'ezqueries') . '\n';
		$code .= 'm' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_m', 'ezqueries') . '\n';
		$code .= 'n' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_n', 'ezqueries') . '\n\n';
		$code .= 'Y' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_Y', 'ezqueries') . '\n';
		$code .= 'y' . '\t\t\t' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_dateformatcharacter_y', 'ezqueries') . '\n';

		// Create output code
		$onclick = 'alert("' . $code . '");';
		$output = '<a href="#" style="position: relative; margin: 0 0 0 5px; top: 4px;" onclick="' . htmlspecialchars($onclick) . '" alt="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_showselectabledateformats', 'ezqueries') . '"/><img style="margin-top: 5px" src="' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath("ezqueries") . 'Resources/Public/Icons/help.gif" title="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('wizard_showselectabledateformats', 'ezqueries') . '" /></a>';

		return $output;
	}

}
?>