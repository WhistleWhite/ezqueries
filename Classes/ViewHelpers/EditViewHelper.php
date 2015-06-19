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
 * EditViewHelper
 */
class EditViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Database record
	 *
	 * @var array
	 */
	private $record;

	/**
	 * Selected columns of the record
	 *
	 * @var array
	 */
	private $columns;

	/**
	 * Column types of the record
	 *
	 * @var array
	 */
	private $columnTypes;

	/**
	 * Renders edit form elements
	 *
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @return string $code
	 */
	public function render($recordManagement) {
		$this -> record = $recordManagement -> getRecords(0) -> getData();
		$primaryKeys = $recordManagement -> getRecords(0) -> getPrimaryKeys();
		$this -> columns = $recordManagement -> getTable() -> getSelectedColumns();
		$this -> columnTypes = $recordManagement -> getTable() -> getColumnTypes();
		$search = $recordManagement -> getConditions() -> getSearch();
		$filters = $recordManagement -> getConditions() -> getFilters();

		// Generate URL
		$arguments = array("primaryKeys" => $primaryKeys, "search" => $search, "filters" => $filters);
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		$url = $urlUtility -> createURL("update", $arguments);

		// Generate form
		$code = '<div class="tx_ezqueries_edit"><form action="' . $url . '" method="post" id="tx_ezqueries_edit_form" class="tx_ezqueries_form tx_ezqueries_edit_form">';
		$code .= $this -> generateFormElements($url);
		$code .= '</form></div>';

		return $code;
	}

	/**
	 * Generate form elements
	 *
	 * @return string $code
	 */
	private function generateFormElements($url) {
		$formUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\FormUtility', $this -> controllerContext -> getUriBuilder());

		// Generate form elements
		$code = '<div class="tx_ezqueries_form tx_ezqueries_edit_form">';

		foreach ($this->columns as $column) {
			$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_' . $column['cssName'] . '">';
			// Label
			if (!isset($this -> columnTypes[$column['name']]['userID'])) {
				$code .= '<div class="tx_ezqueries_form_label tx_ezqueries_form_label_' . $column['cssName'] . '">';
				if (isset($this -> columnTypes[$column['name']]['labelFrom'])) {
					$label = $this -> record[$this -> columnTypes[$column['name']]['labelFrom']];
				} else {
					$label = $column['columnName'];
				}
				if ($label == '') {
					$label = '&nbsp;';
				}
				if (($this -> columnTypes[$column['name']]['not_null'] == TRUE && $this -> columnTypes[$column['name']]['auto_increment'] == FALSE) || $this -> columnTypes[$column['name']]['required'] == 'true') {
					$label .= ' *';
				}
				$code .= '<label for="' . $column['cssName'] . '">' . $label . '</label></div>';
			}
			$code .= '<div class="tx_ezqueries_form_data tx_ezqueries_form_data_' . $column['cssName'] . '">';
			// Form element
			$code .= $formUtility -> generateFormElement($column['name'], $this -> record[$column['name']], $this -> columnTypes, 'edit', $this -> record);
			$code .= '</div></div>';
		}

		// Submit edit form element
		$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_submit"><input class="tx_ezqueries_submit tx_ezqueries_submit_edit" name="' . $url . '" type="submit" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('edit_form_submit', 'ezqueries') . '" /></div>';
		$code .= '</div>';

		return $code;
	}

}
?>