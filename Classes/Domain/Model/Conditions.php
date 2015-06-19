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
 * Conditions
 */
class Conditions extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * Column relations
	 *
	 * @var array
	 */
	private $columnRelations = array();

	/**
	 * Primary keys clauses
	 *
	 * @var array
	 */
	private $primaryKeys = array();

	/**
	 * WHERE clause
	 *
	 * @var string
	 */
	private $where = NULL;

	/**
	 * Filter clauses
	 *
	 * @var array
	 */
	private $filters = array();

	/**
	 * Search clauses
	 *
	 * @var array
	 */
	private $search = array();

	/**
	 * OrderBy
	 *
	 * @var string
	 */
	private $orderBy = NULL;

	/**
	 * OrderByValue
	 *
	 * @var string
	 */
	private $orderByValue = NULL;

	/**
	 * Order
	 *
	 * @var string
	 */
	private $order = NULL;

	/**
	 * GroupBy
	 *
	 * @var string
	 */
	private $groupBy = NULL;

	/**
	 * Start record
	 *
	 * @var int
	 */
	private $startRecord = 0;

	/**
	 * Records per page
	 *
	 * @var int
	 */
	private $recordsPerPage = 0;

	/**
	 * Maximum count of pages
	 *
	 * @var int
	 */
	private $maxPages = 0;

	/**
	 * Count of records
	 *
	 * @var int
	 */
	private $recordsCount = 0;

	/**
	 * Set column relations
	 *
	 * @param array $columnRelations
	 * @return void
	 */
	public function setColumnRelations($columnRelations) {
		$this -> columnRelations = $columnRelations;
	}

	/**
	 * Get column relations
	 *
	 * @return array $columnRelations
	 */
	public function getColumnRelations() {
		return $this -> columnRelations;
	}

	/**
	 * Set primary keys clauses
	 *
	 * @param array $primaryKeys
	 * @return void
	 */
	public function setPrimaryKeys($primaryKeys) {
		$this -> primaryKeys = $primaryKeys;
	}

	/**
	 * Get primary keys clauses
	 *
	 * @return array $primaryKeys
	 */
	public function getPrimaryKeys() {
		return $this -> primaryKeys;
	}

	/**
	 * Set WHERE clause
	 *
	 * @param string $where
	 * @return void
	 */
	public function setWhere($where) {
		$this -> where = $where;
	}

	/**
	 * Get WHERE clause
	 *
	 * @return string $where
	 */
	public function getWhere() {
		return $this -> where;
	}

	/**
	 * Set filter clauses
	 *
	 * @param array $filters
	 * @return void
	 */
	public function setFilters($filters) {
		$this -> filters = $filters;
	}

	/**
	 * Get filter clauses
	 *
	 * @return array $filters
	 */
	public function getFilters() {
		return $this -> filters;
	}

	/**
	 * Set search clauses
	 *
	 * @param array $search
	 * @return void
	 */
	public function setSearch($search) {
		$this -> search = $search;
	}

	/**
	 * Get search clauses
	 *
	 * @return array $search
	 */
	public function getSearch() {
		return $this -> search;
	}

	/**
	 * Set OrderBy
	 *
	 * @param string $orderBy
	 * @return void
	 */
	public function setOrderBy($orderBy) {
		$this -> orderBy = $orderBy;
	}

	/**
	 * Get OrderBy
	 *
	 * @return string $orderBy
	 */
	public function getOrderBy() {
		return $this -> orderBy;
	}

	/**
	 * Set OrderByValue
	 *
	 * @param string $orderByValue
	 * @return void
	 */
	public function setOrderByValue($orderByValue) {
		$this -> orderByValue = $orderByValue;
	}

	/**
	 * Get OrderByValue
	 *
	 * @return string $orderByValue
	 */
	public function getOrderByValue() {
		return $this -> orderByValue;
	}

	/**
	 * Set order
	 *
	 * @param string $order
	 * @return void
	 */
	public function setOrder($order) {
		$this -> order = $order;
	}

	/**
	 * Get order
	 *
	 * @return string $order
	 */
	public function getOrder() {
		return $this -> order;
	}

	/**
	 * Set GroupBy
	 *
	 * @param string $groupBy
	 * @return void
	 */
	public function setGroupBy($groupBy) {
		$this -> groupBy = $groupBy;
	}

	/**
	 * Get GroupBy
	 *
	 * @return string $groupBy
	 */
	public function getGroupBy() {
		return $this -> groupBy;
	}

	/**
	 * Set start record
	 *
	 * @param int $startRecord
	 * @return void
	 */
	public function setStartRecord($startRecord) {
		$this -> startRecord = $startRecord;
	}

	/**
	 * Get start record
	 *
	 * @return int $startRecord
	 */
	public function getStartRecord() {
		return $this -> startRecord;
	}

	/**
	 * Set records per page
	 *
	 * @param int $recordsPerPage
	 * @return void
	 */
	public function setRecordsPerPage($recordsPerPage) {
		$this -> recordsPerPage = $recordsPerPage;
	}

	/**
	 * Get records per page
	 *
	 * @return int $recordsPerPage
	 */
	public function getRecordsPerPage() {
		return $this -> recordsPerPage;
	}

	/**
	 * Set max pages
	 *
	 * @param int $maxPages
	 * @return void
	 */
	public function setMaxPages($maxPages) {
		$this -> maxPages = $maxPages;
	}

	/**
	 * Get max pages
	 *
	 * @return int $maxPages
	 */
	public function getMaxPages() {
		return $this -> maxPages;
	}

	/**
	 * Set records count
	 *
	 * @param int $recordsCount
	 * @return void
	 */
	public function setRecordsCount($recordsCount) {
		$this -> recordsCount = $recordsCount;
	}

	/**
	 * Get records count
	 *
	 * @return int $recordsCount
	 */
	public function getRecordsCount() {
		return $this -> recordsCount;
	}

}
?>