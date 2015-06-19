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
 * FlexForm-Utility
 *
 * Utility class for FlexForms. Provides functions to fill FlexForm select elements.
 */

class FlexFormUtility {

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
		$sysPageObj = $this -> objectManager -> get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$rootLine = $sysPageObj -> getRootLine($pageUid);
		$TSObj = $this -> objectManager -> get('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
		$TSObj -> tt_track = 0;
		$TSObj -> init();
		$TSObj -> runThroughTemplates($rootLine);
		$TSObj -> generateConfig();
		return $TSObj -> setup['plugin.']['tx_ezqueries_ezqueriesplugin.']['settings.'];
	}

	/**
	 * Displays all database tables (for table select in FlexForm)
	 *
	 * @param array $config Plugin configuration
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	public function showDatabaseTables($config) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$recordManagement = $this -> objectManager -> get('Frohland\\Ezqueries\\Domain\\Repository\\RecordManagementRepository');

		if ($config['row']['pi_flexform'] != NULL) {
			// Get FlexForm config (sheet database -> sDB)
			$piValues = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);
			$configData = $piValues['data']['sDEF']['lDEF'];

			// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
			foreach ($configData as $key => $value) {
				$key = str_replace('.', '_', $key);
				$compatibleConfigData[$key] = $value;
			}

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

			// Get options (tables) for table select
			$options = $recordManagement -> getTablesFromDatabase();

			// Set options for table select
			if ($options != FALSE) {
				for ($i = 0; $i <= count($options); $i++) {
					if ($i == 0) {
						$optionList[] = array(0 => '', 1 => 0);
					} else {
						$optionList[] = array(0 => $options[$i - 1], 1 => $options[$i - 1]);
					}
				}
				$config['items'] = array_merge($config['items'], $optionList);
			}
			return $config;
		}
	}

	/**
	 * Displays all columns of the selected tables (for columns select in FlexForm)
	 *
	 * @param array $config Plugin configuration
	 * @param boolean $withAdditionalColumns Show additional columns?
	 * @param boolean $allColumns Show all columns?
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	private function showColumns($config, $withAdditionalColumns, $allColumns) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$recordManagement = $this -> objectManager -> get('Frohland\\Ezqueries\\Domain\\Repository\\RecordManagementRepository');

		if ($config['row']['pi_flexform'] != NULL) {
			// Get FlexForm config (sheet default -> sDEF)
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
				if (stripos($table, '|')) {
					$table = substr($table, 0, stripos($table, '|'));
				}
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

				// Get options (columns) for columns select
				if ($allColumns) {
					$columns = $recordManagement -> getColumnsFromTables($tables);
				} else {
					$table[] = $tables[0];
					$columns = $recordManagement -> getColumnsFromTables($table);
				}

				if ($withAdditionalColumns) {
					$configDataColumns = $piValues['data']['sCOLUMNS']['lDEF'];

					// Replace dots with underscores for compatibility (e.g. settings.mysetting -> settings_mysetting)
					foreach ($configDataColumns as $key => $value) {
						$key = str_replace('.', '_', $key);
						$compatibleConfigDataColumns[$key] = $value;
					}

					// Get additional columns
					$additionalColumnsList = $compatibleConfigDataColumns['settings_additionalColumns']['vDEF'];
					$additionalColumns = explode(',', $additionalColumnsList);

					foreach ($additionalColumns as $additionalColumn) {
						if (trim($additionalColumn) !== NULL && trim($additionalColumn) !== '') {
							$columns[$additionalColumn]['name'] = trim($additionalColumn);
						}
					}
				}

				// Set options for columns select
				if ($columns) {
					foreach ($columns as $column) {
						$optionList[] = array(0 => $column['name'], 1 => $column['name']);
					}
					$config['items'] = array_merge($config['items'], $optionList);
				}
				return $config;
			}
		}
	}

	/**
	 * Displays all columns of the selected tables (for columns select in FlexForm)
	 *
	 * @param array $config Plugin configuration
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	public function showTablesColumns($config) {
		return $this -> showColumns($config, TRUE, TRUE);
	}

	/**
	 * Displays all columns of the selected tables (for columns select in FlexForm) except the additional columns
	 *
	 * @param array $config Plugin configuration
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	public function showTablesColumnsWithoutAdditionalColumns($config) {
		return $this -> showColumns($config, FALSE, TRUE);
	}

	/**
	 * Displays all columns of the selected table (for columns select in FlexForm)
	 *
	 * @param array $config Plugin configuration
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	public function showTableColumns($config) {
		return $this -> showColumns($config, TRUE, FALSE);
	}

	/**
	 * Displays all columns of the selected table (for columns select in FlexForm) except the additional columns
	 *
	 * @param array $config Plugin configuration
	 * @return array $config Plugin configuration & options for FlexForm select element
	 */
	public function showTableColumnsWithoutAdditionalColumns($config) {
		return $this -> showColumns($config, FALSE, FALSE);
	}
}
?>