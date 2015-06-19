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
 * SearchViewHelper
 */
class SearchViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Column names of the record
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
	 * Full-text search?
	 *
	 * @var bool
	 */
	private $fullTextSearch;

	/**
	 * Renders search form element
	 *
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @param boolean $fullTextSearch
	 * @return string $code
	 */
	public function render($recordManagement, $fullTextSearch) {
		$this -> columns = $recordManagement -> getTable() -> getSelectedColumns();
		$this -> columnTypes = $recordManagement -> getTable() -> getColumnTypes();
		$this -> fullTextSearch = $fullTextSearch;
		$filters = $recordManagement -> getConditions() -> getFilters();

		// Generate URL
		$arguments = array("filters" => $filters);
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		$url = $urlUtility -> createURL("list", $arguments);

		// Generate form
		$code = '<div class="tx_ezqueries_search"><form action="' . $url . '" method="post" class="tx_ezqueries_form tx_ezqueries_search_form">';
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
		$value = '';
		$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');
		$filterUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\FilterUtility');

		// Generate form elements
		$code = '<div class="tx_ezqueries_form tx_ezqueries_search_form">';

		if ($this -> fullTextSearch == FALSE) {
			// Search in column
			foreach ($this->columns as $column) {
				$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_' . $column['cssName'] . '"><div class="tx_ezqueries_form_label tx_ezqueries_form_label_' . $column['cssName'] . '">';
				// Label
				$label = $column['columnName'];
				if ($label == '') {
					$label = '&nbsp;';
				}
				$code .= '<label for="' . $column['cssName'] . '">' . $label . '</label>';
				$code .= '</div><div class="tx_ezqueries_form_data tx_ezqueries_form_data_' . $column['cssName'] . '">';

				$code .= $filterUtility -> generateFilterElement($column, $this -> columnTypes);

				$code .= '</div></div>';
			}
			$code .= '<input class="tx_ezqueries_input_hidden_fulltextsearch" type="hidden" value="false" name="tx_ezqueries_ezqueriesplugin[search][fullTextSearch]" />';
		} else {
			// Full-text search
			$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_search"><div class="tx_ezqueries_form_label tx_ezqueries_form_label_search">';
			// Label
			$label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('search_form_search', 'ezqueries') . '';
			$code .= '<label for="tx_ezqueries_input_search">' . $label . ':</label>';
			$code .= '</div><div class="tx_ezqueries_form_data tx_ezqueries_form_data_search">';
			$code .= '<div class="tx_ezqueries_input">';
			$code .= '<input id="tx_ezqueries_input_search" class="tx_ezqueries_input tx_ezqueries_input_search" type="text" value="" name="" />';
			$code .= '<div class="tx_ezqueries_image_delete"></div>';

			foreach ($this->columns as $column) {
				if ($this -> columnTypes[$column['name']]['render'] !== 'image' && $this -> columnTypes[$column['name']]['render'] !== 'document') {
					$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="" name="tx_ezqueries_ezqueriesplugin[search][' . $column['name'] . '][value]" />';
				}
			}
			$code .= '<input class="tx_ezqueries_input_hidden_fulltextsearch" type="hidden" value="true" name="tx_ezqueries_ezqueriesplugin[search][fullTextSearch]" />';
			$code .= '</div></div></div>';
		}

		// Submit edit form element
		$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_submit"><input class="tx_ezqueries_submit tx_ezqueries_submit_search" name="' . $url . '" type="submit" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('search_form_submit', 'ezqueries') . '" /></div>';
		$code .= '</div>';
		return $code;
	}

}
?>