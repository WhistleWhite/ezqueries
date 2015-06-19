<?php
namespace Frohland\Ezqueries\ViewHelpers;

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
 * SortColumnsViewHelper
 */
class SortColumnsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders sortable column headers for the list view
	 *
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @return string $code
	 */
	public function render($recordManagement) {
		$columns = $recordManagement -> getTable() -> getSelectedColumns();
		$columnTypes = $recordManagement -> getTable() -> getColumnTypes();
		$orderBy = $recordManagement -> getConditions() -> getOrderBy();
		$order = $recordManagement -> getConditions() -> getOrder();
		$search = $recordManagement -> getConditions() -> getSearch();
		$filters = $recordManagement -> getConditions() -> getFilters();
		$code = '';
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());

		foreach ($columns as $column) {
			if ($columnTypes[$column['name']]['sortable'] == TRUE) {
				$arguments = array("orderBy" => $column['name'], "order" => 'ASC', "search" => $search, "filters" => $filters);

				$imgClass = 'tx_ezqueries_image_order';
				$class = '';

				if ($column['name'] == $orderBy) {
					if ($order == 'ASC') {
						$arguments = array("orderBy" => $column['name'], "order" => 'DESC', "search" => $search, "filters" => $filters);
						$imgClass = 'tx_ezqueries_image_order tx_ezqueries_image_order_asc';
					} else {
						$arguments = array("orderBy" => $column['name'], "order" => 'ASC', "search" => $search, "filters" => $filters);
						$imgClass = 'tx_ezqueries_image_order tx_ezqueries_image_order_desc';
					}
				}
				$url = $urlUtility -> createURL("list", $arguments);
				$columnValue = '<a class="tx_ezqueries_link tx_ezqueries_link_sort tx_ezqueries_list_link_sort ' . $imgClass . '" href="' . $url . '"><span class="tx_ezqueries_link_sort_text">' . $column['columnName'] . '</span></a>';
				$class = 'tx_ezqueries_list_header_sortable';
			} else {
				$columnValue = $column['columnName'];
				$class = '';
			}

			$code .= '<th class="tx_ezqueries_list_header ' . $class . ' tx_ezqueries_list_header_' . $column['cssName'] . '" nowrap="nowrap">' . $columnValue . '</th>';
		}

		return $code;
	}

}
?>