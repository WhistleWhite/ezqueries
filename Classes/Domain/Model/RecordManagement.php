<?php
namespace Frohland\Ezqueries\Domain\Model;

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
 * RecordManagement
 */
class RecordManagement extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository
	 */
	private $recordManagementRepository;

	/**
	 * The table object
	 *
	 * @var \Frohland\Ezqueries\Domain\Model\Table
	 */
	private $table;

	/**
	 * Array of record objects
	 *
	 * @var array \Frohland\Ezqueries\Domain\Model\Record
	 */
	private $records;

	/**
	 * The conditions object
	 *
	 * @var \Frohland\Ezqueries\Domain\Model\Conditions
	 */
	private $conditions;

	/**
	 * The primary keys of all records
	 *
	 * @var array
	 */
	private $primaryKeys;

	/**
	 * The POST or GET parameters
	 *
	 * @var array
	 */
	private $arguments;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Init
	 *
	 * @param \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository
	 * @return void
	 */
	public function __construct($recordManagementRepository, $tableType) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		$this -> recordManagementRepository = $recordManagementRepository;
		$this -> table = new \Frohland\Ezqueries\Domain\Model\Table($tableType);
		$this -> conditions = new \Frohland\Ezqueries\Domain\Model\Conditions();
	}

	/**
	 * Get the table object
	 *
	 * @return \Frohland\Ezqueries\Domain\Model\Table $table
	 */
	public function getTable() {
		return $this -> table;
	}

	/**
	 * Set the array of record objects
	 *
	 * @return string $error
	 */
	public function setRecords() {
		$recordsData = $this -> recordManagementRepository -> getRecordsFromTables($this -> table -> getColumns(), $this -> table -> getTableNames(), $this -> conditions);
		$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($this -> table -> getTableNames());

		if (!isset($recordsData['error'])) {
			foreach ($recordsData as $data) {
				$primaryKeys = array();
				foreach ($primaryKeyColumns as $primaryKeyColumn) {
					$primaryKeys[$primaryKeyColumn['name']] = $data[$primaryKeyColumn['name']];
				}
				$this -> records[] = new \Frohland\Ezqueries\Domain\Model\Record($data, $primaryKeys);
			}
		}

		// Change column labels with the value of the labelFrom column (for detail and edit view)
		if ($this -> table -> getTableType() == 'detail' || $this -> table -> getTableType() == 'edit') {
			$columnTypes = $this -> table -> getColumnTypes();
			$columns = $this -> table -> getSelectedColumns();
			foreach ($columns as &$column) {
				if (isset($columnTypes[$column['name']]['labelFrom'])) {
					$data = $this -> records[0] -> getData();
					$columnName = $data[$columnTypes[$column['name']]['labelFrom']];
					if ($columnName !== NULL && $columnName !== '') {
						$column['columnName'] = $data[$columnTypes[$column['name']]['labelFrom']];
					} else {
						unset($columns[$column['name']]);
					}
				}
			}
			unset($column);
			$this -> table -> setSelectedColumns($columns);
		}

		if (isset($recordsData['error'])) {
			return $recordsData['error'];
		} else {
			return 'noError';
		}
	}

	/**
	 * Get the array of record objects
	 *
	 *
	 * @param int $i Array index
	 * @return array \Frohland\Ezqueries\Domain\Model\Record $records
	 */
	public function getRecords($i = -1) {
		if ($i === -1) {
			return $this -> records;
		} else {
			return $this -> records[$i];
		}
	}

	/**
	 * Set the primary keys of all records
	 *
	 * @param array $arguments The POST or GET parameters
	 * @return void
	 */
	public function setPrimaryKeys($arguments = array()) {
		// Set primary keys of the record (for detail and edit view)
		if ($this -> table -> getTableType() == 'detail' || $this -> table -> getTableType() == 'edit') {
			$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($this -> table -> getTableNames());
			$this -> primaryKeys = $this -> recordManagementRepository -> getRecordsFromTables($primaryKeyColumns, $this -> table -> getTableNames(), $this -> conditions, TRUE);
			if (isset($arguments['primaryKeys'])) {
				$primaryKeys = $arguments['primaryKeys'];
			} else {
				$primaryKeys = $this -> primaryKeys[0];
			}
			$this -> conditions -> setPrimaryKeys($primaryKeys);
			$this -> conditions -> setStartRecord(0);
			$this -> conditions -> setRecordsPerPage(1);
		} else {
			if ($this -> table -> getTableType() == 'list') {
				$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($this -> table -> getTableNames());
				$this -> primaryKeys = $this -> recordManagementRepository -> getRecordsFromTables($primaryKeyColumns, $this -> table -> getTableNames(), $this -> conditions, FASLE);
				//$this -> conditions -> setPrimaryKeys($primaryKeys);
			}
		}
	}

	/**
	 * Get the primary keys of all records
	 *
	 * @return array $primaryKeys
	 */
	public function getPrimaryKeys() {
		return $this -> primaryKeys;
	}

	/**
	 * Set table names
	 *
	 * @param array $tableNames The names of the selected tables
	 * @return void
	 */
	public function setTableNames($tableNames) {
		$this -> table -> setTableNames($tableNames);
	}

	/**
	 * Set columns (for the queries)
	 *
	 * @param array $flexFormSettings The Settings from the FlexForm
	 * @return void
	 */
	public function setColumns($flexFormSettings) {
		switch($this->table->getTableType()) {
			case 'list' :
				$selectedColumnsSettings = 'listColumns';
				break;
			case 'detail' :
				$selectedColumnsSettings = 'detailColumns';
				break;
			case 'edit' :
				$selectedColumnsSettings = 'editColumns';
				break;
			case 'new' :
				$selectedColumnsSettings = 'newColumns';
				break;
			case 'search' :
				$selectedColumnsSettings = 'searchColumns';
				break;
			case 'relation' :
				$selectedColumnsSettings = 'relationColumns';
				break;
			default :
				$selectedColumnsSettings = NULL;
				break;
		}

		$columns = array();

		// Get selected columns and all columns
		if ($flexFormSettings[$selectedColumnsSettings]) {
			$selectedColumns = explode(',', $flexFormSettings[$selectedColumnsSettings]);
			foreach ($selectedColumns as $column) {
				$columns[$column]['cssName'] = str_replace('.', '_', $column);
				$columns[$column]['columnName'] = substr($column, strpos($column, '.') + 1, strlen($column) - strpos($column, '.') - 1);
				$columns[$column]['tableName'] = substr($column, 0, strpos($column, '.'));
				$columns[$column]['name'] = $column;
			}

			// Get primary key columns
			$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($this -> table -> getTableNames());
			foreach ($primaryKeyColumns as $primaryKeyColumn) {
				$columns[$primaryKeyColumn['name']]['name'] = $primaryKeyColumn['name'];
			}

			// Get additional columns (for links, labels, ...)
			$columnTypes = $this -> table -> getColumnTypes();
			foreach ($columnTypes as $column => $name) {
				if (isset($columnTypes[$column]['filterColumn'])) {
					$columns[$columnTypes[$column]['filterColumn']]['name'] = $columnTypes[$column]['filterColumn'];
				}
				if ($this -> table -> getTableType() == 'detail' || $this -> table -> getTableType() == 'edit') {
					if (isset($columnTypes[$column]['labelFrom'])) {
						$columns[$columnTypes[$column]['labelFrom']]['name'] = $columnTypes[$column]['labelFrom'];
					}
				}

				if ($this -> table -> getTableType() == 'relation') {
					if ($column == $this -> table -> getForeignKeyColumn()) {
						$columns[$column]['name'] = $this -> table -> getForeignKeyColumn();
					}
					if ($column == $this -> table -> getForeignKeyRelationColumn()) {
						$columns[$column]['name'] = $this -> table -> getForeignKeyRelationColumn();
					}
				}
			}

			// Get additional columns (parameters, additional select, ...)
			foreach ($columns as $column => $name) {
				if (isset($columnTypes[$column]['parameters'])) {
					$parameters = explode(',', $columnTypes[$column]['parameters']);
					foreach ($parameters as $parameter) {
						$parameter = trim($parameter);
						$parameterValue = trim(substr($parameter, strpos($parameter, '=') + 1, strlen($parameter) - strpos($parameter, '=') - 1));
						if ($parameterValue[0] != '"') {
							$columns[$parameterValue]['name'] = $parameterValue;
						}
					}
				}

				if ($columnTypes[$column]['type'] == 'additional') {
					unset($columns[$column]);
					if (isset($columnTypes[$column]['select'])) {
						$columns[$column]['cssName'] = str_replace('.', '_', $column);
						$columns[$column]['name'] = $column;
						$columns[$column]['query'] = $columnTypes[$column]['select'];
					}
				}
			}
		} else {
			$columns = $this -> recordManagementRepository -> getColumnsFromTables($this -> table -> getTableNames());
		}

		$this -> table -> setColumns($columns);
	}

	/**
	 * Set config for the selected columns
	 *
	 * @param array $flexFormSettings The Settings from the FlexForm
	 * @param array $arguments The POST or GET parameters
	 * @return boolean $useAllColumns
	 */
	public function setSelectedColumns($flexFormSettings, $arguments) {
		$this -> arguments = $arguments;

		switch($this->table->getTableType()) {
			case 'list' :
				$selectedColumnsSettings = 'listColumns';
				break;
			case 'detail' :
				$selectedColumnsSettings = 'detailColumns';
				break;
			case 'edit' :
				$selectedColumnsSettings = 'editColumns';
				break;
			case 'new' :
				$selectedColumnsSettings = 'newColumns';
				break;
			case 'search' :
				$selectedColumnsSettings = 'searchColumns';
				break;
			case 'relation' :
				$selectedColumnsSettings = 'relationColumns';
				break;
			default :
				$selectedColumnsSettings = NULL;
				break;
		}

		// Get selected columns
		if ($flexFormSettings[$selectedColumnsSettings]) {
			$columns = explode(',', $flexFormSettings[$selectedColumnsSettings]);
			foreach ($columns as $column) {
				if (strpos($column, '.')) {
					$selectedColumns[$column]['cssName'] = str_replace('.', '_', $column);
					$selectedColumns[$column]['columnName'] = substr($column, strpos($column, '.') + 1, strlen($column) - strpos($column, '.') - 1);
					$selectedColumns[$column]['tableName'] = substr($column, 0, strpos($column, '.'));
					$selectedColumns[$column]['name'] = $column;
				} else {
					$selectedColumns[$column]['cssName'] = $column;
					$selectedColumns[$column]['columnName'] = $column;
					$selectedColumns[$column]['tableName'] = '';
					$selectedColumns[$column]['name'] = $column;
				}
			}
			$useAllColumns = FALSE;
		} else {
			if ($this -> table -> getTableType() == 'edit' || $this -> table -> getTableType() == 'new') {
				$tableNames = $this -> table -> getTableNames();
				$mainTable[0] = $tableNames[0];
				$selectedColumns = $this -> recordManagementRepository -> getColumnsFromTables($mainTable);
			} else {
				$selectedColumns = $this -> recordManagementRepository -> getColumnsFromTables($this -> table -> getTableNames());
			}
			$useAllColumns = TRUE;
		}

		$columnTypes = $this -> table -> getColumnTypes();

		// Get alternative labels for columns
		foreach ($selectedColumns as &$column) {
			if (isset($columnTypes[$column['name']]['label']) && $columnTypes[$column['name']]['label'] !== '_') {
				$column['columnName'] = $columnTypes[$column['name']]['label'];
			} else {
				if ($columnTypes[$column['name']]['label'] == '_') {
					$column['columnName'] = '';
				}
			}
			if (isset($columnTypes[$column['name']]['list_label']) && $this -> table -> getTableType() == 'list') {
				$column['columnName'] = $columnTypes[$column['name']]['list_label'];
			}
			if (isset($columnTypes[$column['name']]['detail_label']) && $this -> table -> getTableType() == 'detail') {
				$column['columnName'] = $columnTypes[$column['name']]['detail_label'];
			}
			if (isset($columnTypes[$column['name']]['edit_label']) && $this -> table -> getTableType() == 'edit') {
				$column['columnName'] = $columnTypes[$column['name']]['edit_label'];
			}
			if (isset($columnTypes[$column['name']]['new_label']) && $this -> table -> getTableType() == 'new') {
				$column['columnName'] = $columnTypes[$column['name']]['new_label'];
			}
			if (isset($columnTypes[$column['name']]['search_label']) && $this -> table -> getTableType() == 'search') {
				$column['columnName'] = $columnTypes[$column['name']]['search_label'];
			}
		}
		unset($column);

		// Set values for elements in edit and new form
		if ($this -> table -> getTableType() == 'edit' || $this -> table -> getTableType() == 'new') {
			// Select items by SQL-Query
			foreach ($selectedColumns as $column => $values) {
				if (isset($columnTypes[$column]['dropDownListSQL'])) {
					$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
					$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');

					$sqlQuery = $templateUtility -> fillMarkersInSQLStatement($columnTypes[$column]['dropDownListSQL'], $arguments, NULL, $this -> recordManagementRepository);
					$dropDownListItems = $this -> recordManagementRepository -> getRecordsBySQLQuery($sqlQuery);

					if ($dropDownListItems !== NULL && $dropDownListItems !== FALSE) {
						$counter = 0;
						foreach ($dropDownListItems as $item) {
							if ($counter != 0) {
								$columnTypes[$column]['dropDownList'] .= '###';
							}
							if (isset($item[1])) {
								$itemValueCounter = count($item);
								$template = $columnTypes[$column]['dropDownListSQLTemplate'];
								for ($i = 0; $i < $itemValueCounter; $i++) {
									$template = str_replace($i . '', '<###>' . $i . '<###>', $template);
								}
								for ($i = 0; $i < $itemValueCounter; $i++) {
									if ($i !== $itemValueCounter - 1) {
										if (isset($columnTypes[$column]['dropDownListSQLTemplate'])) {
											$template = str_replace('<###>' . $i . '<###>', $languageUtility -> translateValue($item[$i]) . '', $template);
										} else {
											$columnTypes[$column]['dropDownList'] .= $languageUtility -> translateValue($item[$i]) . ' ';
										}
									}
								}
								if (isset($columnTypes[$column]['dropDownListSQLTemplate'])) {
									$columnTypes[$column]['dropDownList'] .= $template;
								}
								$columnTypes[$column]['dropDownList'] .= ' [' . $item[$itemValueCounter - 1] . ']';
							} else {
								$columnTypes[$column]['dropDownList'] .= $languageUtility -> translateValue($item[0]) . '[' . $item[0] . ']';
							}
							$counter++;
						}
					} else {
						$columnTypes[$column]['dropDownList'] .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('no_dropdown_items', 'ezqueries') . '[' . 'NULL' . ']';
					}
				}
			}

			// User authentication
			$userGroup = $GLOBALS['TSFE'] -> fe_user -> user['usergroup'];
			$userID = $GLOBALS['TSFE'] -> fe_user -> user['uid'];

			foreach ($selectedColumns as $column) {
				if ($column['columnName'] == $flexFormSettings['userIDColumn'] && $userGroup !== $flexFormSettings['adminUserGroup']) {
					$columnTypes[$column['name']]['userID'] = $userID;
				}
			}

			// Default values
			foreach ($selectedColumns as $column => $value) {
				if (isset($columnTypes[$column]['defaultValue'])) {
					$defaultValue = trim($columnTypes[$column]['defaultValue']);
					if ($defaultValue[0] == '"') {
						$defaultValue = substr($defaultValue, 1, strlen($defaultValue) - 2);
					} else {
						// Get maximum or minimum value
						if (strpos($defaultValue, 'max(') !== FALSE || strpos($defaultValue, 'min(') !== FALSE) {
							$defaultValueQuery = 'SELECT ' . $defaultValue . ' FROM ' . $value['tableName'];
							if (isset($columnTypes[$column]['defaultValueWhereColumn'])) {
								if (isset($columnTypes[$column]['defaultValueWhereValue'])) {
									$defaultValueWhereValue = trim($columnTypes[$column]['defaultValueWhereValue']);
									if ($defaultValueWhereValue[0] != '"') {
										$whereValue = $arguments[$defaultValueWhereValue];
									} else {
										$whereValue = '"' . $arguments[$defaultValueWhereValue] . '"';
										//$whereValue = trim(substr($defaultValueWhereValue, 1, strpos($defaultValueWhereValue, '"',1) -1));
									}
								} else {
									$whereValue = '';
								}
								$defaultValueQuery .= ' WHERE ' . $columnTypes[$column]['defaultValueWhereColumn'] . '=' . $whereValue;
							}
							$columnTypes[$column]['defaultValueQuery'] = $defaultValueQuery;
							$defaultValue = $this -> recordManagementRepository -> getRecordsBySQLQuery($columnTypes[$column]['defaultValueQuery']);
							if ($defaultValue !== FALSE) {
								$defaultValue = $defaultValue[0][0];
							} else {
								$defaultValue = '';
							}

							if ($defaultValue == '' || $defaultValue == NULL) {
								$defaultValue = 1;
							}
						} else {
							// Get other default values
							switch($defaultValue) {
								case 'userID' :
									$defaultValue = $GLOBALS['TSFE'] -> fe_user -> user['uid'];
									break;
								case 'userName' :
									$defaultValue = $GLOBALS['TSFE'] -> fe_user -> user['name'];
									break;
								case 'userEmail' :
									$defaultValue = $GLOBALS['TSFE'] -> fe_user -> user['email'];
									break;
								case 'date' :
									$timestamp = time();
									$defaultValue = date("Y-m-d", $timestamp);
									break;
								case 'time' :
									$timestamp = time();
									$defaultValue = date("H:i:s", $timestamp);
									break;
								case 'year' :
									$timestamp = time();
									$defaultValue = date("Y", $timestamp);
									break;
								case 'timestamp' :
									$timestamp = time();
									$defaultValue = date("Y-m-d H:i:s", $timestamp);
									break;
								default :
									if (isset($arguments[$defaultValue])) {$defaultValue = $arguments[$defaultValue];
									} else {$defaultValue = NULL;
									}
							}
						}
					}
					$columnTypes[$column]['defaultValue'] = $defaultValue;
				}
			}
		}

		// Additonal values for search filteres
		if ($this -> table -> getTableType() == 'search') {
			// Select items by SQL-Query
			foreach ($selectedColumns as $column => $values) {
				if (isset($columnTypes[$column]['filterDropDownListSQL'])) {
					$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
					$sqlQuery = $templateUtility -> fillMarkersInSQLStatement($columnTypes[$column]['filterDropDownListSQL'], $arguments, NULL, $this -> recordManagementRepository);
					$dropDownListItems = $this -> recordManagementRepository -> getRecordsBySQLQuery($sqlQuery);

					if ($dropDownListItems !== NULL && $dropDownListItems !== FALSE) {
						$counter = 0;
						foreach ($dropDownListItems as $item) {
							if ($counter != 0) {
								$columnTypes[$column]['filterDropDownList'] .= '###';
							}
							if (isset($item[1])) {
								$itemValueCounter = count($item);
								$template = $columnTypes[$column]['filterDropDownListSQLTemplate'];
								for ($i = 0; $i < $itemValueCounter; $i++) {
									$template = str_replace($i . '', '<###>' . $i . '<###>', $template);
								}
								for ($i = 0; $i < $itemValueCounter; $i++) {
									if ($i !== $itemValueCounter - 1) {
										if (isset($columnTypes[$column]['filterDropDownListSQLTemplate'])) {
											$template = str_replace('<###>' . $i . '<###>', $item[$i] . '', $template);
										} else {
											$columnTypes[$column]['filterDropDownList'] .= $item[$i] . ' ';
										}
									}
								}
								if (isset($columnTypes[$column]['filterDropDownListSQLTemplate'])) {
									$columnTypes[$column]['filterDropDownList'] .= $template;
								}
								$columnTypes[$column]['filterDropDownList'] .= ' [' . $item[$itemValueCounter - 1] . ']';
							} else {
								$columnTypes[$column]['filterDropDownList'] .= $item[0] . '[' . $item[0] . ']';
							}
							$counter++;
						}
					} else {
						$columnTypes[$column]['filterDropDownList'] .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('no_dropdown_items', 'ezqueries') . '[' . 'NULL' . ']';
					}
				}
			}
		}

		// Values
		foreach ($selectedColumns as $column => $value) {
			if (isset($columnTypes[$column]['value'])) {
				$newValue = trim($columnTypes[$column]['value']);
				if (isset($columnTypes[$column]['valueType'])) {
					if ($columnTypes[$column]['valueType'] == 'parameter') {
						if (isset($arguments[$newValue])) {
							$newValue = $arguments[$newValue];
						} else {
							$newValue = '';
						}
					}
					if ($columnTypes[$column]['valueType'] == 'string') {
						$newValue = $newValue;
					}
				}
				if ($newValue[0] == '"') {
					$newValue = substr($newValue, 1, strlen($newValue) - 2);
				}

				$columnTypes[$column]['value'] = $newValue;
			}
		}

		$this -> table -> setColumnTypes($columnTypes);
		$this -> table -> setSelectedColumns($selectedColumns);

		return $useAllColumns;
	}

	/**
	 * Set column types
	 *
	 * @param array $flexFormSettings The Settings from the FlexForm
	 * @return void
	 */
	public function setColumnTypes($flexFormSettings) {
		$columnTypes = $this -> recordManagementRepository -> getColumnTypes($this -> table -> getTableNames());
		$columnConfig = $flexFormSettings['columnConfiguration'];
		$types = $this -> mergeColumnTypesWithColumnConfig($columnTypes, $columnConfig);

		// Sortable columns?
		if ($this -> table -> getTableType() == 'list' && $flexFormSettings['useSortableColumns']) {
			$sortableColumns = explode(',', $flexFormSettings['sortableColumns']);
			foreach ($sortableColumns as $sortableColumn) {
				$types[$sortableColumn]['sortable'] = TRUE;
			}
		}

		// Include hook to set custom column types
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagement']['hookSetColumnTypes'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagement']['hookSetColumnTypes'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$types = $_procObj -> hookSetColumnTypes($types, $this -> table -> getTableType());
			}
		}
		$this -> table -> setColumnTypes($types);
	}

	/**
	 * Set conditions
	 *
	 * @param array $flexFormSettings The Settings from the FlexForm
	 * @param array $arguments The POST or GET parameters
	 * @return void
	 */
	public function setConditions($flexFormSettings, $arguments) {

		$columnTypes = $this -> table -> getColumnTypes();

		// Include hook to edit arguments
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagement']['hookEditArguments'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagement']['hookEditArguments'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$arguments = $_procObj -> hookEditArguments($arguments);
			}
		}

		if ($this -> table -> getTableType() !== 'relation') {
			// Set where, orderby ,order and groupby
			if (isset($arguments['orderBy']) && $arguments['orderBy'] !== '' && isset($arguments['order'])) {
				$orderBy = $arguments['orderBy'];
				$order = $arguments['order'];
				$orderByValue = NULL;
			} else {
				if (isset($flexFormSettings['orderByValue']) && $flexFormSettings['orderByValue'] !== '') {
					$orderByValue = $flexFormSettings['orderByValue'];
				} else {
					$orderBy = $flexFormSettings['orderBy'];
					$orderByValue = NULL;
				}
				$order = $flexFormSettings['order'];
			}

			$groupBy = $flexFormSettings['groupBy'];

			$where = '';
			if (isset($flexFormSettings['where'])) {
				$where .= $flexFormSettings['where'];
			}

			// User authentication
			$userGroup = $GLOBALS['TSFE'] -> fe_user -> user['usergroup'];
			$userID = $GLOBALS['TSFE'] -> fe_user -> user['uid'];

			if (isset($flexFormSettings['userIDColumn']) && $userGroup !== $flexFormSettings['adminUserGroup']) {
				$tables = $this -> table -> getTableNames();
				$column = '';
				if (strpos($flexFormSettings['userIDColumn'], '.') !== false) {
					$column = $flexFormSettings['userIDColumn'];
				} else {
					$column = $tables[0] . '.' . $flexFormSettings['userIDColumn'];
				}
				if ($where !== '') {
					$where .= ' AND ' . $column . '="' . $userID . '"';
				} else {
					$where .= '' . $column . '="' . $userID . '"';
				}
			}

			$this -> conditions -> setWhere($where);
			$this -> conditions -> setOrderBy($orderBy);
			$this -> conditions -> setOrderByValue($orderByValue);
			$this -> conditions -> setOrder($order);
			$this -> conditions -> setGroupBy($groupBy);

			// Set search
			if (isset($arguments['search'])) {
				foreach ($arguments['search'] as $column => $value) {
					if ($column !== 'highlighting' && $column !== 'fullTextSearch') {
						if ($arguments['search'][$column]['value'] !== NULL && $arguments['search'][$column]['value'] !== "") {
							$connection = $this -> recordManagementRepository -> connect();
							$search[$column]['value'] = substr($connection -> qstr($arguments['search'][$column]['value']), 1, -1);
							$connection -> Close();
							$search[$column]['operation'] = $arguments['search'][$column]['operation'];
							$search[$column]['columnType'] = $columnTypes[$column]['type'];
							if (isset($arguments['search'][$column]['searchMode'])) {
								$search[$column]['searchMode'] = $arguments['search'][$column]['searchMode'];
							} else {
								$search[$column]['searchMode'] = $columnTypes[$column]['searchMode'];
							}
							$search[$column]['searchType'] = $arguments['search'][$column]['searchType'];
						}
					}
				}
				if (!isset($arguments['search']['highlighting'])) {
					if ($flexFormSettings['enableSearchMarks']) {
						$search['highlighting'] = 'true';
					} else {
						$search['highlighting'] = 'false';
					}
				} else {
					$search['highlighting'] = $arguments['search']['highlighting'];
				}
				if (isset($arguments['search']['fullTextSearch'])) {
					$search['fullTextSearch'] = $arguments['search']['fullTextSearch'];
				}
			} else {
				$search = array();
			}
			$this -> conditions -> setSearch($search);

			// Set filters
			if (isset($arguments['filters'])) {
				$filters = $arguments['filters'];
			} else {
				$filters = array();
			}
			$this -> conditions -> setFilters($filters);
		} else {
			// Set where for relation view
			$where = $flexFormSettings['relationForeignKeyColumn'] . '="' . $arguments['foreignKey'] . '"';
			$this -> conditions -> setWhere($where);
			$this -> table -> setForeignKeyColumn($flexFormSettings['relationForeignKeyColumn']);
			$this -> table -> setForeignKeyRelationColumn($flexFormSettings['relationForeignKeyRelationColumn']);
		}

		// Set column relations
		if ($flexFormSettings['columnRelations']) {
			$this -> conditions -> setColumnRelations(explode(',', $flexFormSettings['columnRelations']));
		}

		// Set records count
		$recordsCount = $this -> recordManagementRepository -> countRecordsFromTable($this -> table -> getColumns(), $this -> table -> getTableNames(), $this -> conditions);
		$this -> conditions -> setRecordsCount($recordsCount);

		// Set page browser stuff if list view
		if ($this -> table -> getTableType() == 'list') {
			// Set number of records per page
			if ($arguments['recordsPerPage']) {
				$recordsPerPage = $recordsCount;
			} else {
				if ($flexFormSettings['recordsPerPage']) {
					$recordsPerPage = $flexFormSettings['recordsPerPage'];
				} else {
					$recordsPerPage = $recordsCount;
				}
			}
			$recordsPerPage = (int)$recordsPerPage;
			$this -> conditions -> setRecordsPerPage($recordsPerPage);

			// Set maximum count of pages
			if ($flexFormSettings['maxPages']) {
				$maxPages = $flexFormSettings['maxPages'];
			} else {
				$maxPages = 0;
			}
			$maxPages = (int)$maxPages;
			$this -> conditions -> setMaxPages($maxPages);

			// Set start record
			if ($arguments['startRecord']) {
				$startRecord = $arguments['startRecord'];
			} else {
				$startRecord = 0;
			}
			$this -> conditions -> setStartRecord($startRecord);
		}
	}

	/**
	 * Get the conditions object
	 *
	 * @return \Frohland\Ezqueries\Domain\Model\Conditions $conditions
	 */
	public function getConditions() {
		return $this -> conditions;
	}

	/**
	 * Get recordManagementRepository
	 *
	 * @return \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository
	 */
	public function getRecordManagementRepository() {
		return $this -> recordManagementRepository;
	}

	/**
	 * Get arguments
	 *
	 * @return array $arguments
	 */
	public function getArguments() {
		return $this -> arguments;
	}

	/**
	 * Merge $ColumnTypes with $ColumnConfig
	 *
	 * @param array $columnTypes Column types
	 * @param string $columnConfiguration Column configuration
	 * @return array $types
	 */
	private function mergeColumnTypesWithColumnConfig($columnTypes, $columnConfiguration) {
		$languageUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\LanguageUtility');

		$types = $columnTypes;
		$config = explode('-->', $columnConfiguration);
		array_pop($config);

		foreach ($config as $conf) {
			$confColumn = trim(substr($conf, 0, strpos($conf, '<--')));
			$confValues = trim(substr($conf, strpos($conf, '<--') + 3, strlen($conf) - (strpos($conf, '<--') + 3)));

			$confValues = explode(';', $confValues);
			$confValue = array_shift($confValues);
			$confValue = trim(substr($confValue, strpos($confValue, ':') + 1, strlen($confValue) - (strpos($confValue, ':') + 1)));

			if ($types[$confColumn]['type'] == 'varchar' || $types[$confColumn]['type'] == 'text' || $types[$confColumn]['type'] == 'boolean') {
				$types[$confColumn]['render'] = $confValue;
			}
			if ($types[$confColumn]['type'] == 'int' || $types[$confColumn]['type'] == 'numeric') {
				$types[$confColumn]['numberformat'] = $confValue;
				$types[$confColumn]['decimals'] = intval($confValue[0]);
				if ($confValue[1] == 'x') {
					$types[$confColumn]['dec_point'] = '';
				} else {
					if ($confValue[1] == '_') {
						$types[$confColumn]['dec_point'] = ' ';
					} else {
						$types[$confColumn]['dec_point'] = $confValue[1];
					}
				}
				if ($confValue[2] == 'x') {
					$types[$confColumn]['thousands_sep'] = '';
				} else {
					if ($confValue[2] == '_') {
						$types[$confColumn]['thousands_sep'] = ' ';
					} else {
						$types[$confColumn]['thousands_sep'] = $confValue[2];
					}
				}
			}
			if ($types[$confColumn]['type'] == 'date') {
				$types[$confColumn]['dateformat'] = $confValue;
			}
			if ($types[$confColumn]['type'] == 'year') {
				$types[$confColumn]['yearformat'] = $confValue;
			}

			if (!isset($types[$confColumn]['type'])) {
				$types[$confColumn]['type'] = $confValue;
			}

			foreach ($confValues as $additionalConfig) {
				$additionalConfigName = trim(substr($additionalConfig, 0, strpos($additionalConfig, ':')));
				$additionalConfigValue = trim(substr($additionalConfig, strpos($additionalConfig, ':') + 1, strlen($additionalConfig) - (strpos($additionalConfig, ':') + 1)));
				if ($additionalConfigValue !== '') {
					$additionalConfigValue = $languageUtility -> translateValue($additionalConfigValue, TRUE);
					$types[$confColumn][$additionalConfigName] = $additionalConfigValue;
				}
			}
		}

		return $types;
	}

}
?>