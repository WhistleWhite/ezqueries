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
 * RecordBrowserViewHelper
 */
class RecordBrowserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders a record browser
	 *
	 * @param string $action Controller action
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @return string $code
	 */
	public function render($action, $recordManagement) {
		$i = 0;
		$code = '';
		$recordPosition = 0;
		$records = $recordManagement -> getRecords();
		if ($records) {
			$primaryKeys = $records[0] -> getPrimaryKeys();
			$primaryKeysArray = $recordManagement -> getPrimaryKeys();
			$countPrimaryKeys = count($primaryKeys);
			$countRightKeys = 0;
			$orderBy = $recordManagement -> getConditions() -> getOrderBy();
			$order = $recordManagement -> getConditions() -> getOrder();
			$search = $recordManagement -> getConditions() -> getSearch();
			$filters = $recordManagement -> getConditions() -> getFilters();

			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());

			$code .= '<div class="tx_ezqueries_options_recordbrowser">';

			foreach ($primaryKeysArray as $keys) {
				$countRightKeys = 0;
				foreach ($primaryKeys as $column => $value) {
					if ($keys[$column] == $value) {
						$countRightKeys++;
					}
				}
				if ($countRightKeys == $countPrimaryKeys) {
					$recordPosition = $i;
					break;
				} else {
					$i++;
				}
			}

			if ($recordPosition != 0) {
				$arguments = array("primaryKeys" => $primaryKeysArray[$recordPosition - 1], "orderBy" => $orderBy, "order" => $order, "search" => $search, "filters" => $filters);
				$url = $urlUtility -> createURL($action, $arguments);
				$code .= '<div class="tx_ezqueries_options_recordbrowser_previous"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_' . $action . '" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('recordbrowser_previous', 'ezqueries') . '</a></div>';
			}
			if ($recordPosition < count($primaryKeysArray) - 1) {
				$arguments = array("primaryKeys" => $primaryKeysArray[$recordPosition + 1], "orderBy" => $orderBy, "order" => $order, "search" => $search, "filters" => $filters);
				$url = $urlUtility -> createURL($action, $arguments);
				$code .= '<div class="tx_ezqueries_options_recordbrowser_next"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_' . $action . '" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('recordbrowser_next', 'ezqueries') . '</a></div>';
			}
			$code .= '</div>';
		}

		return $code;
	}

}
?>