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
 * Filter-Utility
 *
 * Utility class for filters.
 */
class FilterUtility {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Generate filter element
	 *
	 * @param string $column Column name
	 * @param array $columnTypes Column types
	 * @param string $placeholder PLaceholder text
	 * @return string $code
	 */
	public function generateFilterElement($column, $columnTypes, $placeholder = '') {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		$code = '';
		$id = 'filter_' . str_replace('.', '_', $column);
		$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');
		$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
		if (isset($columnTypes[$column]['searchPlaceholder'])) {
			$placeholder = $languageUtility -> translateValue($columnTypes[$column]['searchPlaceholder']);
		} else {
			$placeholder = $languageUtility -> translateValue($placeholder);
		}

		// Boolean form element
		if ($columnTypes[$column]['type'] == 'boolean') {
			$code .= '<select class="tx_ezqueries_select ' . $columnTypes[$column]['filterDropDownListClass'] . '" id="' . $id . '" size="1" name="tx_ezqueries_ezqueriesplugin[search][' . $column . '][value]" >';
			$code .= '<option selected="selected">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_no_filter', 'ezqueries') . '</option>';
			$code .= '<option value="1">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_yes', 'ezqueries') . '</option>';
			$code .= '<option value="0">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_no', 'ezqueries') . '</option>';
			$code .= '</select>';
		}

		// Date form element
		if ($columnTypes[$column]['type'] == 'date') {
			$code .= '<div class="tx_ezqueries_datepicker">';
			$code .= '<div class="tx_ezqueries_input">';
			$code .= '<input class="tx_ezqueries_input tx_ezqueries_input_date" id="' . $id . '" type="text" value="" placeholder="' . $placeholder . '" dateformat="' . $conversionUtility -> convertDateFormat($columnTypes[$column]['dateformat']) . '" readonly="readonly" />';
			$code .= '<div class="tx_ezqueries_image_delete"></div>';
			$code .= '<input class="tx_ezqueries_input_hidden tx_ezqueries_input_date_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[search][' . $column . '][value]" />';
			$code .= '</div></div>';
		}

		// All other form elements
		if ($columnTypes[$column]['type'] == 'text' || $columnTypes[$column]['type'] == 'varchar' || $columnTypes[$column]['type'] == 'int' || $columnTypes[$column]['type'] == 'numeric' || $columnTypes[$column]['type'] == 'timestamp' || $columnTypes[$column]['type'] == 'time' || $columnTypes[$column]['type'] == 'year') {
			$code .= '<div class="tx_ezqueries_input">';
			$code .= '<input class="tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="" name="tx_ezqueries_ezqueriesplugin[search][' . $column . '][value]" />';
			$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
		}

		// Select
		if (isset($columnTypes[$column]['filterDropDownListSQL']) && !isset($columnTypes[$column]['filterAutocomplete'])) {
			$code = '';
			$code .= '<select class="tx_ezqueries_select ' . $columnTypes[$column]['filterDropDownListClass'] . '" id="' . $id . '" size="1" name="tx_ezqueries_ezqueriesplugin[search][' . $column . '][value]" >';
			$code .= '<option value="" selected="selected">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_no_filter', 'ezqueries') . '</option>';

			$items = explode('###', $columnTypes[$column]['filterDropDownList']);
			foreach ($items as $item) {
				if (strpos($item, '[')) {
					$itemName = trim(substr($item, 0, strpos($item, '[')));
					$itemValue = substr($item, strpos($item, '[') + 1, strpos($item, ']') - strpos($item, '[') - 1);
				} else {
					$itemName = trim($item);
					$itemValue = trim($item);
				}

				$code .= '<option value="' . $itemValue . '">' . $languageUtility -> translate($itemName) . '</option>';
			}

			$code .= '</select>';
		}

		// Autofill
		if ($columnTypes[$column]['filterAutocomplete'] === 'true') {
			$randomNumber = mt_rand(0, 100);
			$code .= '<div class="tx_ezqueries_input">';
			$code .= '<input class="tx_ezqueries_input" id="' . $id . '" type="text" list="datalist_' . $randomNumber . '" placeholder="' . $placeholder . '" value="" name="tx_ezqueries_ezqueriesplugin[search][' . $column . '][value]" autocomplete="off" />';
			$code .= '<datalist id="datalist_' . $randomNumber . '">';

			$items = explode('###', $columnTypes[$column]['filterDropDownList']);
			foreach ($items as $item) {
				if (strpos($item, '[')) {
					$itemName = trim(substr($item, 0, strpos($item, '[')));
					$itemValue = substr($item, strpos($item, '[') + 1, strpos($item, ']') - strpos($item, '[') - 1);
				} else {
					$itemName = trim($item);
					$itemValue = trim($item);
				}

				$code .= '<option value="' . $itemName . '">';
			}
			$code .= '</datalist>';
			$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
		}
		return $code;
	}

}
?>