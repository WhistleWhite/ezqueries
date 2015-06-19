<?php
namespace Frohland\Ezqueries\Domain\Repository;

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

require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('adodb') . 'adodb/adodb.inc.php');

/**
 * Repository for RecordManagement
 */
class RecordManagementRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Server for database connection
	 *
	 * @var string
	 */
	private $server;

	/**
	 * Database name for database connection
	 *
	 * @var string
	 */
	private $database;

	/**
	 * Username for database connection
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password for database connection
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Use database connection?
	 *
	 * @var boolean
	 */
	private $useConnection;

	/**
	 * Use debug function?
	 *
	 * @var boolean
	 */
	private $useDebug;

	/**
	 * Set the database connection
	 *
	 * @param string $server The name of the server
	 * @param string $database The name of the database
	 * @param string $username The database user
	 * @param string $password User password
	 * @param boolean $useConnection Use database connection?
	 * @param boolean $useDebug Use debug function?
	 * @return void
	 */
	public function setDatabaseConnection($server, $database, $username, $password, $useConnection, $useDebug = 'false') {
		if (($useConnection == TRUE || $useConnection == '1') && $server != NULL && $database != NULL && $username != NULL && $password != NULL) {
			$this -> server = $server;
			$this -> database = $database;
			$this -> username = $username;
			$this -> password = $password;
		} else {
			$this -> server = TYPO3_db_host;
			$this -> database = TYPO3_db;
			$this -> username = TYPO3_db_username;
			$this -> password = TYPO3_db_password;
		}
		$this -> useConnection = $useConnection;
		$this -> useDebug = $useDebug;
	}

	/**
	 * Establishs a connection to the Database
	 *
	 * @return ADONewConnection $connection Database connection
	 */
	public function connect() {
		$connection = ADONewConnection('mysqli');
		if ($connection -> NConnect($this -> server, $this -> username, $this -> password, $this -> database)) {
			$connection -> EXECUTE("set names 'utf8'");
			return $connection;
		} else {
			$connection -> Close();
			$fallbackConnection = ADONewConnection('mysqli');
			$fallbackConnection -> NConnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password, TYPO3_db);
			$fallbackConnection -> EXECUTE("set names 'utf8'");
			return $fallbackConnection;
		}
	}

	/**
	 * Establishs a connection to the TYPO3-Database
	 *
	 * @return ADONewConnection $connection Database connection
	 */
	public function connectTypo3() {
		$connection = ADONewConnection('mysqli');
		$connection = ADONewConnection('mysqli');
		$connection -> NConnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password, TYPO3_db);
		$connection -> EXECUTE("set names 'utf8'");
		return $connection;
	}

	/**
	 * Get record(s) from the selected tables (connected by left outer join)
	 *
	 * @param array $columns Selected columns
	 * @param array $tables Selected tables
	 * @param array $conditions
	 * @param boolean $getPrimaryKeys
	 * @return array $records Array of records
	 */
	public function getRecordsFromTables($columns, $tables, $conditions, $getPrimaryKeys = FALSE) {
		if ($tables) {
			// Get number of records in the table
			if ($conditions) {
				$recordsCount = $conditions -> getRecordsCount();
				$recordsPerPage = $conditions -> getRecordsPerPage();
				$startRecord = $conditions -> getStartRecord();

				if ($recordsPerPage == 0) {
					$startRecord = 0;
					$recordsPerPage = $recordsCount;
				}

				// Correction of $recordsPerPage if $startRecord + $recordsPerPage is greater than the number of records in the table
				if (($startRecord + $recordsPerPage) > $recordsCount) {
					$recordsPerPage = $recordsCount - $startRecord;
				}
			} else {
				$recordsPerPage = -1;
				$startRecord = -1;
			}

			// If getPrimaryKeys true
			if ($getPrimaryKeys) {
				$recordsPerPage = $recordsCount;
				$startRecord = 0;
			}

			// Get columns
			$selectedColumns = $this -> checkSelectedColumns($columns, $tables);

			// Set query
			$query = $this -> createSQLQuery($selectedColumns, $tables, $conditions);
			if ($this -> useDebug == 'true') {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($query, "ezqueries");
			}

			// Connect to database and execute the query
			$connection = $this -> connect();
			$connection -> SetFetchMode(ADODB_FETCH_NUM);
			if ($recordsPerPage === -1 && $startRecord === -1) {
				$result = $connection -> Execute($query);
			} else {
				$result = $connection -> SelectLimit($query, $recordsPerPage, $startRecord);
			}
			$error = $connection -> ErrorMsg();
			$connection -> Close();

			// Get records and return
			if ($result !== FALSE) {
				$recs = array();
				while (!$result -> EOF) {
					$recs[] = $result -> FetchRow();
				}

				// Set index with column names
				$counterRecs = 0;
				foreach ($recs as $rec) {
					$counterColumns = 0;
					foreach ($columns as $column => $value) {
						$records[$counterRecs][$column] = $rec[$counterColumns];
						$counterColumns++;
					}
					$counterRecs++;
				}

				if ($recs) {
					return $records;
				} else {
					$records['error'] = $error;
					if ($this -> useDebug == 'true') {
						\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
					}
					return $records;
				}
			} else {
				$records['error'] = '(' . $error . ')';
				if ($this -> useDebug == 'true') {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
				}
				return $records;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Get record(s) by SQL-Query
	 *
	 * @param array $query Query
	 * @return array $records Array of records
	 */
	public function getRecordsBySQLQuery($query) {
		if ($query) {
			// Connect to database and execute the query
			$connection = $this -> connect();
			$connection -> SetFetchMode(ADODB_FETCH_NUM);
			$result = $connection -> Execute($query);
			$connection -> Close();

			// Get records and return
			if ($result !== FALSE) {
				while (!$result -> EOF) {
					$records[] = $result -> FetchRow();
				}
				return $records;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Get record(s) by SQL-Query // From TYPO3 database
	 *
	 * @param array $query Query
	 * @return array $records Array of records
	 */
	public function getRecordsBySQLQueryTypo3($query) {
		if ($query) {
			// Connect to database and execute the query
			$connection = $this -> connectTypo3();
			$connection -> SetFetchMode(ADODB_FETCH_ASSOC);
			$result = $connection -> Execute($query);
			$connection -> Close();

			// Get records and return
			if ($result !== FALSE) {
				while (!$result -> EOF) {
					$records[] = $result -> FetchRow();
				}
				return $records;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Count records from the result table
	 *
	 * @param array $columns Selected columns
	 * @param array $tables The names of the selected tables
	 * @param array $conditions
	 * @return int $recordCount Number of records
	 */
	public function countRecordsFromTable($columns, $tables, $conditions) {
		if ($tables) {
			// Get columns
			$selectedColumns = $this -> checkSelectedColumns($columns, $tables);

			// Set query
			$query = $this -> createSQLQuery($selectedColumns, $tables, $conditions);

			// Connect to database and execute the query
			$connection = $this -> connect();
			$connection -> SetFetchMode(ADODB_FETCH_NUM);
			$result = $connection -> Execute($query);
			$connection -> Close();

			// Get record count and return
			if ($result !== FALSE) {
				$recordCount = $result -> RecordCount();
				return $recordCount;
			} else {
				return FALSE;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get all tables from the database
	 *
	 * @return array $tables Array of table names
	 */
	public function getTablesFromDatabase() {
		// Connect to database and get tables
		$connection = $this -> connect();
		$tables = $connection -> MetaTables('TABLES');
		$connection -> Close();

		return $tables;
	}

	/**
	 * Get all columns from selected database tables
	 *
	 * @param array $tables The names of the tables
	 * @return array $columnNames Array of column names
	 */
	public function getColumnsFromTables($tables) {
		if ($tables) {
			// Connect to database and get column information
			$connection = $this -> connect();
			$columnNames = array();

			foreach ($tables as $table) {
				$columnInfo = $connection -> MetaColumns($table);
				#debug($columnInfo);
				if (isset($columnInfo)) {
					foreach ($columnInfo as $columnInfoObject) {
						$columnNames[$table . '.' . $columnInfoObject -> name]['cssName'] = $table . '_' . $columnInfoObject -> name;
						$columnNames[$table . '.' . $columnInfoObject -> name]['columnName'] = $columnInfoObject -> name;
						$columnNames[$table . '.' . $columnInfoObject -> name]['tableName'] = $table;
						$columnNames[$table . '.' . $columnInfoObject -> name]['name'] = $table . '.' . $columnInfoObject -> name;
					}
				}
			}
			$connection -> Close();

			if ($columnNames != NULL) {
				return $columnNames;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Get the primary key columns from the selected database tables
	 *
	 * @param array $tables The names of the selected tables
	 * @return array $primaryKeyColumns
	 */
	public function getPrimaryKeyColumns($tables) {
		if ($tables) {
			// Connect to database and get primary key columns
			$connection = $this -> connect();
			$primaryKeyColumns = array();

			$primaryKeys = $connection -> MetaPrimaryKeys($tables[0]);

			foreach ($primaryKeys as $primaryKey) {
				$primaryKeyColumns[$tables[0] . '.' . $primaryKey]['cssName'] = $tables[0] . '_' . $primaryKey;
				$primaryKeyColumns[$tables[0] . '.' . $primaryKey]['columnName'] = $primaryKey;
				$primaryKeyColumns[$tables[0] . '.' . $primaryKey]['tableName'] = $tables[0];
				$primaryKeyColumns[$tables[0] . '.' . $primaryKey]['name'] = $tables[0] . '.' . $primaryKey;
			}

			$connection -> Close();

			return $primaryKeyColumns;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get column types from the database tables
	 *
	 * @param array $tables The names of the tables
	 * @return array $columnTypes Array of column types with column name as key
	 */
	public function getColumnTypes($tables) {
		if ($tables) {
			// Connect to database and get column information
			$connection = $this -> connect();
			$columnTypes = array();

			// Get column types and return
			foreach ($tables as $table) {
				$columnInfo = $connection -> MetaColumns($table);

				foreach ($columnInfo as $columnInfoObject) {
					$name = $table . '.' . $columnInfoObject -> name;
					$type = $connection -> MetaType($columnInfoObject -> type);

					// Convert type and set column types
					switch($type) {
						case 'C' :
							$type = 'varchar';
							$columnTypes[$name]['render'] = 'text';
							break;
						case 'X' :
							$type = 'text';
							$columnTypes[$name]['render'] = 'text_long';
							break;
						case 'I' :
							if ($columnInfoObject -> max_length == 1) {
								$type = 'boolean';
								$columnTypes[$name]['render'] = 'checkbox';
							} else {
								$type = 'int';
								$columnTypes[$name]['numberformat'] = '0xx';
								$columnTypes[$name]['decimals'] = 0;
								$columnTypes[$name]['dec_point'] = '';
								$columnTypes[$name]['thousands_sep'] = '';
							}
							break;
						case 'L' :
							$type = 'boolean';
							$columnTypes[$name]['render'] = 'checkbox';
							break;
						case 'N' :
							$type = 'numeric';
							$columnTypes[$name]['numberformat'] = '2.x';
							$columnTypes[$name]['decimals'] = 2;
							$columnTypes[$name]['dec_point'] = '.';
							$columnTypes[$name]['thousands_sep'] = '';
							break;
						case 'D' :
							if ($columnInfoObject -> max_length == -1) {
								$type = 'date';
								$columnTypes[$name]['dateformat'] = 'Y-m-d';
							} else {
								$type = 'year';
								$columnTypes[$name]['yearformat'] = 'Y';
							}
							break;
						case 'T' :
							if ($columnInfoObject -> max_length == 19) {
								$type = 'timestamp';
							} else {
								$type = 'time';
							}
							break;
					}
					$columnTypes[$name]['type'] = $type;
					$columnTypes[$name]['max_length'] = $columnInfoObject -> max_length;
					$columnTypes[$name]['primary_key'] = $columnInfoObject -> primary_key;
					$columnTypes[$name]['auto_increment'] = $columnInfoObject -> auto_increment;
					$columnTypes[$name]['unsigned'] = $columnInfoObject -> unsigned;
					$columnTypes[$name]['not_null'] = $columnInfoObject -> not_null;
				}
			}
			$connection -> Close();
			return $columnTypes;
		} else {
			return FALSE;
		}
	}

	/**
	 * Update a record
	 *
	 * @param string $tableName The name of the table
	 * @param array $primaryKeys The primary keys of the record
	 * @param array $data The update data
	 * @return string Status
	 */
	public function updateRecord($tableName, $primaryKeys, $data) {
		if ($tableName) {
			// Set clause (where to update)
			foreach ($primaryKeys as $column => $primaryKey) {
				$clauses[] = $this -> makeColumnNameSQLConform($column) . '="' . $primaryKey . '"';
			}
			$clause = implode(' AND ', $clauses);

			// Connect to database
			$connection = $this -> connect();

			// Set query
			$query = 'UPDATE `' . $tableName . '` SET ';
			$i = 1;
			foreach ($data as $column => $value) {
				if ($i != 1) {
					$query .= ',';
				}
				if ($value['type'] == 'text') {
					$queryDataValue = $connection -> qstr($value['value']);
				} else {
					$queryDataValue = $value['value'];
				}
				$query .= $this -> makeColumnNameSQLConform($column) . '=' . $queryDataValue;
				$i++;
			}

			$query .= ' WHERE ' . $clause;

			// Update record and return the status
			$result = $connection -> Execute($query);
			$error = $connection -> ErrorMsg();
			$connection -> Close();

			if ($result !== FALSE) {
				return 'success';
			} else {
				if ($this -> useDebug == 'true') {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
				}
				return $error;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Create a record
	 *
	 * @param string $tableName The name of the table
	 * @param array $data The created data
	 * @return string Status
	 */
	public function createRecord($tableName, $data) {
		if ($tableName) {
			// Connect to database
			$connection = $this -> connect();

			// Set query
			$query = 'INSERT INTO `' . $tableName . '` (';
			$i = 1;
			foreach ($data as $column => $value) {
				if ($i != 1) {
					$query .= ',';
				}
				$query .= $this -> makeColumnNameSQLConform($column);
				$i++;
			}
			$query .= ') VALUES (';
			$i = 1;
			foreach ($data as $column => $value) {
				if ($i != 1) {
					$query .= ',';
				}
				if ($value['type'] == 'text') {
					$queryDataValue = $connection -> qstr($value['value']);
				} else {
					$queryDataValue = $value['value'];
				}
				$query .= $queryDataValue;
				$i++;
			}
			$query .= ')';

			// Create record and return the status
			$result = $connection -> Execute($query);
			$error = $connection -> ErrorMsg();
			$insertID = $connection -> Insert_ID();
			$connection -> Close();

			if ($result !== FALSE) {
				$returnValues['status'] = 'success';
				$returnValues['insertID'] = $insertID;
				return $returnValues;
			} else {
				if ($this -> useDebug == 'true') {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
				}
				$returnValues['status'] = $error;
				return $returnValues;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Delete a record
	 *
	 * @param string $tableName The name of the table
	 * @param string $primaryKeys The primary key(s) of the record
	 * @return string Status
	 */
	public function deleteRecord($tableName, $primaryKeys) {
		if ($tableName) {
			// Set clause (where to delete)
			foreach ($primaryKeys as $column => $primaryKey) {
				$clauses[] = $this -> makeColumnNameSQLConform($column) . '="' . $primaryKey . '"';
			}
			$clause = implode(' AND ', $clauses);

			// Set query
			$query = 'DELETE FROM `' . $tableName . '` WHERE ' . $clause;

			if ($this -> useDebug == 'true') {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($query, "ezqueries");
			}

			// Connect to database and execute the query
			$connection = $this -> connect();
			$result = $connection -> Execute($query);
			$error = $connection -> ErrorMsg();
			$connection -> Close();

			// Return the status
			if ($result !== FALSE) {
				return 'success';
			} else {
				if ($this -> useDebug == 'true') {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
				}
				return $error;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Executes a SQL statement
	 *
	 * @param string $statement SQL statement
	 * @return string Status
	 */
	public function executeSQLStatement($statement) {
		// Connect to database and execute the statement
		$connection = $this -> connect();
		$result = $connection -> Execute($statement);
		$error = $connection -> ErrorMsg();
		$connection -> Close();

		// Return the status
		if ($result !== FALSE) {
			return 'success';
		} else {
			if ($this -> useDebug == 'true') {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devlog($error, "ezqueries");
			}
			return $error;
		}
	}

	/**
	 * Create SQL query
	 *
	 * @param string $selectedColumns Selected columns
	 * @param array $tables The selected tables
	 * @param array $conditions
	 * @return string $query
	 */
	private function createSQLQuery($selectedColumns, $tables, $conditions) {
		if (count($tables) > 1) {
			$query = 'SELECT ' . $selectedColumns . ' FROM ';
			$counter = 0;
			$columnRelations = $conditions -> getColumnRelations();
			foreach ($tables as $table) {
				if ($counter == 0) {
					$query .= '`' . $table . '`';
				} else {
					$query .= ' LEFT JOIN `' . $table . '`';
					$query .= ' ON ' . $columnRelations[$counter - 1] . ' ';
				}
				$counter++;
			}
			$query .= $this -> generateClauses($conditions);
		} else {
			$query = 'SELECT ' . $selectedColumns . ' FROM `' . $tables[0] . '` ' . $this -> generateClauses($conditions);
		}

		return $query;
	}

	/**
	 * Check selected columns
	 *
	 * @param array $columns Selected columns
	 * @param array $tables Selected tables
	 * @return string $selectedColumns
	 */
	private function checkSelectedColumns($columns, $tables) {
		if ($columns) {
			// Get selected columns
			$selectedColumns = array();
			foreach ($columns as $column) {
				if (isset($column['query'])) {
					$selectedColumns[] = $column['query'];
				} else {
					$selectedColumns[] = $this -> makeColumnNameSQLConform($column['name']);
				}
			}
			$selectedColumns = implode(',', $selectedColumns);
			return $selectedColumns;
		} else {
			// Use all columns
			if ($this -> getColumnsFromTables($tables) === FALSE) {
				return FALSE;
			}
			$columns = $this -> getColumnsFromTables($tables);
			foreach ($columns as $column) {
				$selectedColumns[] = $this -> makeColumnNameSQLConform($column['name']);
			}
			$selectedColumns = implode(',', $selectedColumns);
			return $selectedColumns;
		}
	}

	/**
	 * Generates the clauses for the query
	 *
	 * @param array $conditions
	 * @return string $generatedClauses
	 */
	private function generateClauses($conditions) {
		$generatedClauses = '';
		$clauses = array();
		if ($conditions) {
			$clauses['where'] = $conditions -> getWhere();
			$clauses['primaryKeys'] = $conditions -> getPrimaryKeys();
			$clauses['filters'] = $conditions -> getFilters();
			$clauses['search'] = $conditions -> getSearch();
			$clauses['orderBy'] = $conditions -> getOrderBy();
			$clauses['orderByValue'] = $conditions -> getOrderByValue();
			$clauses['order'] = $conditions -> getOrder();
			$clauses['groupBy'] = $conditions -> getGroupBy();
			$clauses['having'] = '';
		}

		// Include hook to manipulate the clauses
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementRepository']['hookSetClauses'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementRepository']['hookSetClauses'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$clauses = $_procObj -> hookSetClauses($clauses);
			}
		}

		if ($clauses) {
			$generatedClauses .= 'WHERE ';
			if ($clauses['where']) {
				$generatedClauses .= $clauses['where'];
			} else {
				$generatedClauses .= '""=""';
			}
			if ($clauses['primaryKeys']) {
				foreach ($clauses['primaryKeys'] as $column => $value) {
					$generatedClauses .= ' AND ' . $this -> makeColumnNameSQLConform($column) . '="' . $value . '"';
				}
			}
			if ($clauses['filters']) {
				if (isset($clauses['filters']['filterType'])) {
					$filterType = $clauses['filters']['filterType'];
				}
				foreach ($clauses['filters'] as $column => $value) {
					if ($column != 'filterType' && $column != 'having') {
						$operation = 'AND';

						if (is_array($value) && isset($value['filterType'])) {
							$filterType = $value['filterType'];
							$value = $value['value'];
						} else {
							if (isset($clauses['filters']['filterType'])) {
								$filterType = $clauses['filters']['filterType'];
							} else {
								$filterType = '';
							}
						}

						switch($filterType) {
							case 'custom' :
								$generatedClauses .= ' ' . $operation . ' ' . $value;
								break;
							case 'strict' :
								$generatedClauses .= ' ' . $operation . ' ' . $this -> makeColumnNameSQLConform($column) . ' = ' . $value . '';
								break;
							case 'equal' :
								$generatedClauses .= ' ' . $operation . ' ' . $this -> makeColumnNameSQLConform($column) . ' = "' . $value . '"';
								break;
							case 'range' :
								$ranges = explode(',', $value);
								$generatedClauses .= ' ' . $operation . ' ' . $this -> makeColumnNameSQLConform($column) . ' >= ' . trim($ranges[0]) . ' AND ' . $this -> makeColumnNameSQLConform($column) . ' <= ' . trim($ranges[1]);
								break;
							case 'or' :
								$elements = explode(',', $value);
								$generatedClauses .= ' ' . $operation . ' (';
								$count = 0;
								foreach ($elements as $element) {
									if ($count !== 0) {
										$generatedClauses .= ' OR ';
									}
									$generatedClauses .= $this -> makeColumnNameSQLConform($column) . ' = ' . trim($element);
									$count++;
								}
								$generatedClauses .= ') ';
								break;
							default :
								$generatedClauses .= ' ' . $operation . ' ' . $this -> makeColumnNameSQLConform($column) . ' LIKE "%' . $value . '%"';
								break;
						}
					}
				}
			}
			if ($clauses['search']) {
				$counter = 0;
				foreach ($clauses['search'] as $column => $columnValue) {
					if (isset($columnValue['value'])) {
						$value = trim($columnValue['value']);
					} else {
						$value = '';
					}
					if (isset($columnValue['operation']) && $columnValue['operation'] !== '') {
						$operation = $columnValue['operation'];
					} else {
						$operation = 'AND';
					}

					if ($value != NULL && $value != "") {
						if (($column != 'fullTextSearch' && $column != 'highlighting') && $columnValue['searchType'] == '') {
							if ($counter == 0) {
								$generatedClauses .= ' AND (';
								$counter++;
							} else {
								if ($clauses['search']['fullTextSearch'] == 'true') {
									$generatedClauses .= ' OR ';
								} else {
									$generatedClauses .= ' ' . $operation . ' ';
								}
							}

							if ($columnValue['columnType'] != 'numeric' && $columnValue['columnType'] != 'int' && $columnValue['columnType'] != 'boolean') {
								$filterStatement = '"' . $value . '"';
							} else {
								$filterStatement = $value;
							}

							$conformColumn = $this -> makeColumnNameSQLConform($column);

							switch($columnValue['searchMode']) {
								case 'strict' :
									$filterStatement = ' = ' . $filterStatement;
									break;
								case 'word' :
									$filterStatement = '"% ' . $value . ' %"';
									$filterStatement = ' LIKE ' . $filterStatement;
									$conformColumn = 'CONCAT(" ", ' . $conformColumn . ', " ")';
									break;
								default :
									$filterStatement = '"%' . $value . '%"';
									$filterStatement = ' LIKE ' . $filterStatement;
									break;
							}

							$generatedClauses .= $conformColumn . $filterStatement;
						} else {
							if ($columnValue['searchType'] !== '') {
								if ($columnValue['searchType'] == 'custom') {
									if ($counter == 0) {
										$counter++;
									} else {
										$generatedClauses .= ') ';
									}
									$generatedClauses .= $columnValue['value'];
								}
							}
						}
					}
				}
				if ($counter != 0)
					$generatedClauses .= ')';
			}
			if ($clauses['groupBy']) {
				$generatedClauses .= ' GROUP BY ' . $this -> makeColumnNameSQLConform($clauses['groupBy']);
			}
			if (isset($clauses['filters']['having'])) {
				$counter = 0;
				foreach ($clauses['filters']['having'] as $having) {
					if ($counter == 0) {
						$generatedClauses .= ' HAVING ' . $having;
					} else {
						$generatedClauses .= ' AND ' . $having;
					}
					$counter++;
				}
			}
			if ($clauses['orderByValue'] == NULL) {
				if ($clauses['orderBy']) {
					$generatedClauses .= ' ORDER BY ' . $this -> makeColumnNameSQLConform($clauses['orderBy']) . $clauses['order'];
				}
			} else {
				$generatedClauses .= ' ORDER BY ' . $clauses['orderByValue'];
			}
		}

		return $generatedClauses;
	}

	/**
	 * Makes a column name (table.column) SQL conform (table.`column`)
	 *
	 * @param string $column Column name
	 * @return string $conformColumn
	 */
	private function makeColumnNameSQLConform($column) {
		$lastCharPosition = strlen(trim($column)) - 1;
		if ($column[$lastCharPosition] === ')') {
			$conformColumn = $column;
		} else {
			$conformColumn = substr($column, 0, strpos($column, '.')) . '.`' . substr($column, strpos($column, '.') + 1, strlen($column) - strpos($column, '.') - 1) . '`';
		}
		return $conformColumn;
	}

}
?>