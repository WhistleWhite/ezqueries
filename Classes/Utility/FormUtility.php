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
 * Form-Utility
 *
 * Utility class for forms.
 */
class FormUtility {

	/**
	 * Uri builder
	 *
	 * @var UriBuilder
	 */
	private $uriBuilder;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Constructor
	 *
	 * @param UriBuilder $uriBuilder
	 * @return void
	 */
	public function __construct($uriBuilder) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this -> uriBuilder = $uriBuilder;
	}

	/**
	 * Generate form element
	 *
	 * @param string $column Column name
	 * @param string $value Value
	 * @param array $columnTypes Column types
	 * @param string $view View
	 * @param array $record The record
	 * @return string $code
	 */
	public function generateFormElement($column, $value, $columnTypes, $view, $record = NULL) {
		$code = '';
		$validationAttr = '';
		$primaryClass = '';

		$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');
		$valueUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ValueUtility');
		$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> uriBuilder);

		$id = str_replace('.', '_', $column);
		$helpText = '';
		$placeholder = $languageUtility -> translateValue($columnTypes[$column]['placeholder']);

		// Set default value
		if (isset($columnTypes[$column]['defaultValue']) && ($value === '' || $value === NULL || $value === '0000-00-00' || $columnTypes[$column]['defaultValueMode'] === 'overwrite')) {
			$value = $columnTypes[$column]['defaultValue'];
		}

		// Set type - when additional type is set
		if (isset($columnTypes[$column]['additionalType'])) {
			$columnTypes[$column]['type'] = $columnTypes[$column]['additionalType'];
		}

		// UserID form element
		if (isset($columnTypes[$column]['userID'])) {
			$code .= '<div class="tx_ezqueries_input">';
			$code .= '<input class="tx_ezqueries_input" id="' . $id . '" type="hidden" value="' . $columnTypes[$column]['userID'] . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
			$code .= '</div>';

			return $code;
		}

		$readOnly = FALSE;

		// Hook for setting form value
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['formUtilty']['hookSetFormValue'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['formUtilty']['hookSetFormValue'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$value = $_procObj -> hookSetFormValue($value, $column, $view, $record);
			}
		}

		// Hook for setting readonly status (return TRUE if the field status is readonly)
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['formUtilty']['hookSetReadOnlyStatus'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['formUtilty']['hookSetReadOnlyStatus'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$readOnly = $_procObj -> hookSetReadOnlyStatus($column, $view, $record);
			}
		}

		// Readonly form element
		if ($readOnly == TRUE || $columnTypes[$column]['readOnly'] == 'true' || ($columnTypes[$column]['edit_readOnly'] == 'true' && $view == 'edit') || ($columnTypes[$column]['new_readOnly'] == 'true' && $view == 'new')) {

			if ($columnTypes[$column]['primary_key'] == TRUE) {
				$primaryClass .= ' tx_ezqueries_input_primary_key ';
			}

			if ($columnTypes[$column]['render'] == 'text_long' || $columnTypes[$column]['render'] == 'text_editor') {
				if ($columnTypes[$column]['render'] == 'text_long') {
					$code .= '<div class="tx_ezqueries_input tx_ezqueries_disabled_element">';
					$code .= '<textarea class="tx_ezqueries_textarea ' . $primaryClass . '" id="' . $id . '" readonly="readonly" disabled="disabled">' . $value . '</textarea>';
					$value = htmlspecialchars($value);
					$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
					$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
					return $code;
				} else {
					$code .= '<div class="tx_ezqueries_input tx_ezqueries_disabled_element">';
					$code .= '<div class="tx_ezqueries_textarea_editor_disabled" id="' . $id . '">' . $value . '</div>';
					$code .= '</div>';
					return $code;
				}
			} else {
				// Checkbox
				if ($columnTypes[$column]['render'] == 'checkbox') {
					$code .= '<div class="tx_ezqueries_checkbox_div tx_ezqueries_disabled_element">';
					if ($value == 1) {
						$code .= '<input class="tx_ezqueries_checkbox" id="' . $id . '" type="checkbox" value="' . $value . '" checked="checked" readonly="readonly" disabled="disabled" />';
					} else {
						$code .= '<input class="tx_ezqueries_checkbox" id="' . $id . '" type="checkbox" value="' . $value . '" readonly="readonly" disabled="disabled" />';
					}
					$code .= '<input class="tx_ezqueries_checkbox_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
					$code .= '</div>';
					return $code;
				} else {
					// Yes/No
					if ($columnTypes[$column]['render'] == 'yesno') {
						$code .= '<div class="tx_ezqueries_input tx_ezqueries_disabled_element">';
						if ($value == 1) {
							$code .= '<input class="tx_ezqueries_input ' . $primaryClass . '" id="' . $id . '" type="text" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_yes', 'ezqueries') . '" readonly="readonly" disabled="disabled" />';
						} else {
							$code .= '<input class="tx_ezqueries_input ' . $primaryClass . '" id="' . $id . '" type="text" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_no', 'ezqueries') . '" readonly="readonly" disabled="disabled" />';
						}
						$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
						$code .= '</div>';
						return $code;
					} else {
						if (isset($columnTypes[$column]['dropDownList'])) {
							// Do nothing here
						} else {
							if ($columnTypes[$column]['type'] == 'date') {
								$convertedValue = $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $value);
								$code .= '<div class="tx_ezqueries_input tx_ezqueries_disabled_element">';
								$code .= '<input class="tx_ezqueries_input ' . $primaryClass . '" id="' . $id . '" type="text" value="' . $convertedValue . '" readonly="readonly" disabled="disabled" />';
								$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
								$code .= '</div>';
								return $code;
							} else {
								$code .= '<div class="tx_ezqueries_input tx_ezqueries_disabled_element">';
								$code .= '<input class="tx_ezqueries_input ' . $primaryClass . '" id="' . $id . '" type="text" value="' . $value . '" readonly="readonly" disabled="disabled" />';
								$value = htmlspecialchars($value);
								$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
								$code .= '</div>';
								return $code;
							}
						}
					}
				}
			}
		}

		// Select form element
		if (isset($columnTypes[$column]['dropDownList'])) {
			$options = '';
			if ($readOnly == TRUE || $columnTypes[$column]['readOnly'] == 'true' || ($columnTypes[$column]['edit_readOnly'] == 'true' && $view == 'edit') || ($columnTypes[$column]['new_readOnly'] == 'true' && $view == 'new')) {
				$code .= '<input class="tx_ezqueries_input_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
				$code .= '<select id="' . $id . '" class="tx_ezqueries_select tx_ezqueries_disabled_element" size="1" disabled="disabled">';
			} else {
				$code .= '<div class="tx_ezqueries_select_wrapper">';
				if ($columnTypes[$column]['dropDownListFilter'] == 'true') {
					$code .= '<div class="tx_ezqueries_select_filter">';
					$code .= '<input name="regexp" class="tx_ezqueries_select_filter_input" placeholder="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('select_filter_label', 'ezqueries') . '" autocomplete="off" />';
					$code .= '</div>';
				}
				if($columnTypes[$column]['not_null'] == TRUE || $columnTypes[$column]['required'] == 'true'){
					$code .= '<select id="' . $id . '" class="tx_ezqueries_select" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" size="1" data-rule-required="true">';
				} else {
					$code .= '<select id="' . $id . '" class="tx_ezqueries_select" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" size="1">';
				}

			}

			$isValueIncluded = FALSE;

			if ($columnTypes[$column]['not_null'] == FALSE && $columnTypes[$column]['required'] != 'true') {
				$options .= '<option value=""></option>';
			}

			$items = explode('###', $columnTypes[$column]['dropDownList']);
			foreach ($items as $item) {
				if (strpos($item, '[')) {
					$itemName = trim(substr($item, 0, strpos($item, '[')));
					$itemValue = substr($item, strpos($item, '[') + 1, strpos($item, ']') - strpos($item, '[') - 1);
				} else {
					$itemName = trim($item);
					$itemValue = trim($item);
				}

				if ($itemValue == $value) {
					$isValueIncluded = TRUE;
					$options .= '<option selected="selected" value="' . $itemValue . '">' . $languageUtility -> translateValue($itemName) . '</option>';
				} else {
					$options .= '<option value="' . $itemValue . '">' . $languageUtility -> translateValue($itemName) . '</option>';
				}
			}

			if ($isValueIncluded == FALSE && !$columnTypes[$column]['noCurrent'] == 'true') {
				$options = '<option selected="selected" value="">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_select_choose', 'ezqueries') . '</option>' . $options;
			}
			$code .= $options;
			$code .= '</select></div>';
			if ($columnTypes[$column]['readOnly'] == 'true' || ($columnTypes[$column]['edit_readOnly'] == 'true' && $view == 'edit') || ($columnTypes[$column]['new_readOnly'] == 'true' && $view == 'new')) {
				return $code;
			}
		} else {

			// Set validation classes
			// Required
			if ($columnTypes[$column]['not_null'] == TRUE && $columnTypes[$column]['auto_increment'] == FALSE) {
				$validationAttr .= 'data-rule-required="true" ';
			} else {
				if ($columnTypes[$column]['required'] == 'true') {
					$validationAttr .= 'data-rule-required="true" ';
				}
			}

			// Max length
			$maxLength = -1;
			if (isset($columnTypes[$column]['maxLength'])) {
				$maxLength = $columnTypes[$column]['maxLength'];
				$validationAttr .= 'data-rule-maxlength="' . $columnTypes[$column]['maxLength'] . '" ';
			} else {
				if ($columnTypes[$column]['max_length'] != '-1') {
					$maxLength = $columnTypes[$column]['max_length'];
					$validationAttr .= 'data-rule-maxlength="' . $columnTypes[$column]['max_length'] . '" ';
				}
			}
			// Min length
			if (isset($columnTypes[$column]['minLength'])) {
				$validationAttr .= 'data-rule-minlength="' . $columnTypes[$column]['minLength'] . '" ';
			}
			// Range length
			if (isset($columnTypes[$column]['rangeLength'])) {
				$min = substr($columnTypes[$column]['rangeLength'], 0, strpos($columnTypes[$column]['rangeLength'], '-'));
				$max = substr($columnTypes[$column]['rangeLength'], strpos($columnTypes[$column]['rangeLength'], '-') + 1, strlen($columnTypes[$column]['rangeLength']) - strpos($columnTypes[$column]['rangeLength'], '-'));
				$validationAttr .= 'data-rule-rangelength="' . $min . ',' . $max . '" ';
			}
			// Max words
			if (isset($columnTypes[$column]['maxWords'])) {
				$validationAttr .= 'data-rule-maxWords="' . $columnTypes[$column]['maxWords'] . '" ';
			}
			// Min words
			if (isset($columnTypes[$column]['minWords'])) {
				$validationAttr .= 'data-rule-minWords="' . $columnTypes[$column]['minWords'] . '" ';
			}
			// Range words
			if (isset($columnTypes[$column]['rangeWords'])) {
				$min = substr($columnTypes[$column]['rangeWords'], 0, strpos($columnTypes[$column]['rangeWords'], '-'));
				$max = substr($columnTypes[$column]['rangeWords'], strpos($columnTypes[$column]['rangeWords'], '-') + 1, strlen($columnTypes[$column]['rangeWords']) - strpos($columnTypes[$column]['rangeWords'], '-'));
				$validationAttr .= 'data-rule-rangeWords="' . $min . ',' . $max . '" ';
			}
			// Max
			if (isset($columnTypes[$column]['max'])) {
				$validationAttr .= 'data-rule-max="' . $columnTypes[$column]['max'] . '" ';
			}
			// Min
			if (isset($columnTypes[$column]['min'])) {
				$validationAttr .= 'data-rule-min="' . $columnTypes[$column]['min'] . '" ';
			}
			// Range
			if (isset($columnTypes[$column]['range'])) {
				$min = substr($columnTypes[$column]['range'], 0, strpos($columnTypes[$column]['range'], '-'));
				$max = substr($columnTypes[$column]['range'], strpos($columnTypes[$column]['range'], '-') + 1, strlen($columnTypes[$column]['range']) - strpos($columnTypes[$column]['range'], '-'));
				if($max == 'actYear'){
					$max = date("Y");
				}
				if($max == 'nextYear'){
					$max = intval(date("Y")) + 1;
				}
				$validationAttr .= 'data-rule-range="' . $min . ',' . $max . '" ';
			}
			// Email
			if ($columnTypes[$column]['render'] == 'email') {
				$validationAttr .= 'data-rule-email="true" ';
			}
			// URL
			if ($columnTypes[$column]['render'] == 'link') {
				$validationAttr .= 'data-rule-url="true" ';
			}
			// Digits
			if ($columnTypes[$column]['render'] == 'number' || $columnTypes[$column]['type'] == 'year') {
				$validationAttr .= 'data-rule-digits="true" ';
			}
			// Number
			if ($columnTypes[$column]['type'] == 'numeric') {
				$validationAttr .= 'data-rule-number="true" ';
			}
			// Integer
			if ($columnTypes[$column]['type'] == 'int') {
				$validationAttr .= 'data-rule-digits="true" ';
			}
			// Alphanumeric
			if (isset($columnTypes[$column]['alphanumeric'])) {
				$validationAttr .= 'data-rule-alphanumeric="true" ';
			}

			// Set primary key class
			if ($columnTypes[$column]['primary_key'] == TRUE) {
				$primaryClass .= ' tx_ezqueries_input_primary_key ';
			}

			// Varchar form element
			if ($columnTypes[$column]['type'] == 'varchar') {
				// Upload element
				if ($columnTypes[$column]['render'] == 'image' || $columnTypes[$column]['render'] == 'document') {
					$fileName = $value;
					$url = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Classes/Utility/UploadUtility.php';
					$code .= '<div class="' . $url . '">';
					$code .= '<div class="tx_ezqueries_input">';
					$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input tx_ezqueries_input_upload tx_ezqueries_input_upload_' . $columnTypes[$column]['render'] . '" id="' . $id . '" type="text" value="' . $fileName . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
					$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
					$code .= '<div class="tx_ezqueries_upload_button">
								<noscript>
									<p>Please enable JavaScript to use file uploader.</p>
								</noscript>
							  </div>';
					$code .= '</div>';
				} else {
					if ($columnTypes[$column]['render'] == 'text_long') {
						$code .= '<div class="tx_ezqueries_input">';
						$code .= '<textarea class="' . $primaryClass . 'tx_ezqueries_textarea tx_ezqueries_textarea_noeditor" id="' . $id . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . '>' . $value . '</textarea>';
						$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
					} else {
						if ($columnTypes[$column]['render'] == 'text_editor') {
							$randomNumber = mt_rand(0, 100);
							$code .= '<div class="tx_ezqueries_input" data-max-length="' . $maxLength . '">';
							$code .= '<div class="tx_ezqueries_textarea_editor" id="' . $id . '_' . $randomNumber . '">' . $value . '</div>';
							$code .= '<textarea style="display: none;" class="' . $primaryClass . 'tx_ezqueries_textarea tx_ezqueries_textarea_editor" id="' . $id . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . '>' . $value . '</textarea>';
							$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
						} else {
							$code .= '<div class="tx_ezqueries_input">';
							$value = htmlspecialchars($value);
							$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
							$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
						}
					}
				}
			}

			// Text form element
			if ($columnTypes[$column]['type'] == 'text') {
				if ($columnTypes[$column]['render'] == 'text_long') {
					$code .= '<div class="tx_ezqueries_input">';
					$code .= '<textarea class="' . $primaryClass . 'tx_ezqueries_textarea tx_ezqueries_textarea_noeditor" id="' . $id . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . '>' . $value . '</textarea>';
					$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
				} else {
					if ($columnTypes[$column]['render'] == 'text_editor') {
						$randomNumber = mt_rand(0, 100);
						$code .= '<div class="tx_ezqueries_input" data-max-length="' . $maxLength . '">';
						$code .= '<div class="tx_ezqueries_textarea_editor" id="' . $id . '_' . $randomNumber . '">' . $value . '</div>';
						$code .= '<textarea style="display: none;" class="' . $primaryClass . 'tx_ezqueries_textarea tx_ezqueries_textarea_editor" id="' . $id . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . '>' . $value . '</textarea>';
						$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
					} else {
						$code .= '<div class="tx_ezqueries_input">';
						$value = htmlspecialchars($value);
						$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
						$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
					}
				}
			}

			// Boolean form element
			if ($columnTypes[$column]['type'] == 'boolean') {
				// Checkbox
				if ($columnTypes[$column]['render'] == 'checkbox') {
					$code .= '<div class="tx_ezqueries_checkbox_div">';
					if ($value == 1) {
						$code .= '<input class="tx_ezqueries_checkbox" id="' . $id . '" type="checkbox" value="' . $value . '" checked="checked" />';
					} else {
						$code .= '<input class="tx_ezqueries_checkbox" id="' . $id . '" type="checkbox" value="' . $value . '" />';
					}
					$code .= '<input class="tx_ezqueries_checkbox_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
					$code .= '</div>';
				}
				// Yes/No select
				if ($columnTypes[$column]['render'] == 'yesno') {
					$code .= '<select class="tx_ezqueries_select" id="' . $id . '" size="1" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" >';
					if ($value == 1) {
						$code .= '<option value="1" selected="selected">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_yes', 'ezqueries') . '</option>';
						$code .= '<option value="0">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_no', 'ezqueries') . '</option>';
					} else {
						$code .= '<option value="1">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_yes', 'ezqueries') . '</option>';
						$code .= '<option value="0" selected="selected">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_no', 'ezqueries') . '</option>';
					}
					$code .= '</select>';
				}
				// Choose/Yes/No select
				if ($columnTypes[$column]['render'] == 'chooseyesno') {
					$code .= '<select class="tx_ezqueries_select" id="' . $id . '" size="1" name="tx_ezqueries_ezqueriesplugin[' . $column . ']"  data-rule-required="true">';
					$code .= '<option selected="selected" value="">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_select_choose', 'ezqueries') . '</option>';
					$code .= '<option value="1">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_yes', 'ezqueries') . '</option>';
					$code .= '<option value="0">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('form_bool_no', 'ezqueries') . '</option>';
					$code .= '</select>';
				}
				// Number
				if ($columnTypes[$column]['render'] == 'number') {
					$code .= '<div class="tx_ezqueries_input">';
					$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
					$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
				}
			}

			// Int form element
			if ($columnTypes[$column]['type'] == 'int') {
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
			}

			// Numeric form element
			if ($columnTypes[$column]['type'] == 'numeric') {
				$convertedValue = $conversionUtility -> convertNumber($columnTypes[$column]['decimals'], '.', '', $value);
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $convertedValue . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
			}

			// Date form element
			if ($columnTypes[$column]['type'] == 'date') {
				$convertedValue = $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $value);
				$code .= '<div class="tx_ezqueries_datepicker">';
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input id="' . $id . '" class="' . $primaryClass . 'tx_ezqueries_input tx_ezqueries_input_readonly tx_ezqueries_input_date" type="text" placeholder="' . $placeholder . '" value="' . $convertedValue . '" data-ezqueries-dateformat="' . $conversionUtility -> convertDateFormat($columnTypes[$column]['dateformat']) . '" name="' . $id . '" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
				$code .= '<input class="tx_ezqueries_input_hidden tx_ezqueries_input_date_hidden" type="hidden" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" />';
				$code .= '</div>';
			}

			// Timestamp form element
			if ($columnTypes[$column]['type'] == 'timestamp') {
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
			}

			// Time form element
			if ($columnTypes[$column]['type'] == 'time') {
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
			}

			// Year form element
			if ($columnTypes[$column]['type'] == 'year') {
				$code .= '<div class="tx_ezqueries_input">';
				$code .= '<input class="' . $primaryClass . 'tx_ezqueries_input" id="' . $id . '" type="text" placeholder="' . $placeholder . '" value="' . $value . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" ' . $validationAttr . ' />';
				$code .= '<div class="tx_ezqueries_image_delete"></div></div>';
			}
		}

		// Help text
		if (isset($columnTypes[$column]['helpText'])) {
			$helpText = $columnTypes[$column]['helpText'];
		}
		if ((isset($columnTypes[$column]['edit_helpText']) && $view == 'edit')) {
			$helpText = $columnTypes[$column]['edit_helpText'];
		}
		if ((isset($columnTypes[$column]['new_helpText']) && $view == 'new')) {
			$helpText = $columnTypes[$column]['new_helpText'];
		}
		if (isset($columnTypes[$column]['helpText']) || (isset($columnTypes[$column]['edit_helpText']) && $view == 'edit') || (isset($columnTypes[$column]['new_helpText']) && $view == 'new')) {
			if ($columnTypes[$column]['helpTextPosition'] == 'top') {
				$code = '<div class="tx_ezqueries_help_text tx_ezqueries_help_text_top">' . $helpText . '</div>' . $code;
			} else {
				if ($columnTypes[$column]['helpTextPosition'] == 'bottom') {
					$code .= '<div class="tx_ezqueries_help_text tx_ezqueries_help_text_bottom">' . $helpText . '</div>';
				} else {
					if ($columnTypes[$column]['helpTextPosition'] == 'custom') {
						$code .= '<div class="tx_ezqueries_help_text_custom" data-ezqueries-helptext="' . $helpText . '"></div>';
					} else {
						$code .= '<div class="tx_ezqueries_help_text_icon" data-ezqueries-helptext="' . $helpText . '"></div>';
					}
				}
			}
		}

		return $code;

	}

}
?>