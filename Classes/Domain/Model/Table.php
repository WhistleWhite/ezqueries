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
 * Table
 */
class Table extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * The table type (e.g. table for list, table for detail, ...)
	 *
	 * @var string
	 */
	private $tableType = NULL;

	/**
	 * The table names
	 *
	 * @var array
	 */
	private $tableNames = array();

	/**
	 * The table columns
	 *
	 * @var array
	 */
	private $columns = array();

	/**
	 * The selected table columns
	 *
	 * @var array
	 */
	private $selectedColumns = array();

	/**
	 * The column types
	 *
	 * @var array
	 */
	private $columnTypes = array();

	/**
	 * The foreign key column
	 *
	 * @var string
	 */
	private $foreignKeyColumn;

	/**
	 * The foreign key relation column
	 *
	 * @var string
	 */
	private $foreignKeyRelationColumn;

	/**
	 * Init
	 *
	 * @param string $tableType Table type
	 * @return void
	 */
	public function __construct($tableType) {
		$this -> tableType = $tableType;
	}

	/**
	 * Get table type
	 *
	 * @return string $tableType
	 */
	public function getTableType() {
		return $this -> tableType;
	}

	/**
	 * Set table names
	 *
	 * @param array $tableNames Table names
	 * @return void
	 */
	public function setTableNames($tableNames) {
		$this -> tableNames = $tableNames;
	}

	/**
	 * Get table names
	 *
	 * @return array $tableNames
	 */
	public function getTableNames() {
		return $this -> tableNames;
	}

	/**
	 * Set table columns
	 *
	 * @param array $columns Table columns
	 * @return void
	 */
	public function setColumns($columns) {
		$this -> columns = $columns;
	}

	/**
	 * Get table columns
	 *
	 * @return array $columns
	 */
	public function getColumns() {
		return $this -> columns;
	}

	/**
	 * Set selected columns
	 *
	 * @param array $selectedColumns Selected columns
	 * @return void
	 */
	public function setSelectedColumns($selectedColumns) {
		$this -> selectedColumns = $selectedColumns;
	}

	/**
	 * Get selected columns
	 *
	 * @return array $selectedColumns
	 */
	public function getSelectedColumns() {
		return $this -> selectedColumns;
	}

	/**
	 * Set column types
	 *
	 * @param array $columnTypes Column types
	 * @return void
	 */
	public function setColumnTypes($columnTypes) {
		// Include hook to manipulate column types
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['table']['editColumnTypes'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['table']['editColumnTypes'] as $_classRef) {
				$_procObj = &t3lib_div::getUserObj($_classRef);
				$columnTypes = $_procObj -> editColumnTypes($columnTypes);
			}
		}
		$this -> columnTypes = $columnTypes;
	}

	/**
	 * Get column types
	 *
	 * @return array $columnTypes
	 */
	public function getColumnTypes() {
		return $this -> columnTypes;
	}

	/**
	 * Set foreign key column
	 *
	 * @param string $column Column name
	 * @return void
	 */
	public function setForeignKeyColumn($column) {
		$this -> foreignKeyColumn = $column;
	}

	/**
	 * Get foreign key column
	 *
	 * @return string $foreignKeyColumn
	 */
	public function getForeignKeyColumn() {
		return $this -> foreignKeyColumn;
	}

	/**
	 * Set foreign key relation column
	 *
	 * @param string $column Column name
	 * @return void
	 */
	public function setForeignKeyRelationColumn($column) {
		$this -> foreignKeyRelationColumn = $column;
	}

	/**
	 * Get foreign key relation column
	 *
	 * @return string $foreignKeyRelationColumn
	 */
	public function getForeignKeyRelationColumn() {
		return $this -> foreignKeyRelationColumn;
	}

}
?>