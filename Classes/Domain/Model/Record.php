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
 * Record
 */
class Record extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * The record data
	 *
	 * @var array
	 */
	private $data = NULL;

	/**
	 * The primary keys of the record
	 *
	 * @var array
	 */
	private $primaryKeys = array();

	/**
	 * Init
	 *
	 * @param array $data Record data
	 * @param array $primaryKeys Primary keys
	 * @return void
	 */
	public function __construct($data, $primaryKeys) {
		$this -> data = $data;
		$this -> primaryKeys = $primaryKeys;
	}

	/**
	 * Set record data
	 *
	 * @param array $data Record data
	 * @return void
	 */
	public function setData($data) {
		$this -> data = $data;
	}

	/**
	 * Get record data
	 *
	 * @return array $data
	 */
	public function getData() {
		return $this -> data;
	}

	/**
	 * Set primary keys
	 *
	 * @param array $primaryKeys
	 * @return void
	 */
	public function setPrimaryKeys($primaryKeys) {
		$this -> primaryKeys = $primaryKeys;
	}

	/**
	 * Get primary keys
	 *
	 * @return array $primaryKeys
	 */
	public function getPrimaryKeys() {
		return $this -> primaryKeys;
	}

}
?>