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
 * Value-Utility
 *
 * Utility class for values in the list and detail view.
 */
class ValueUtility {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Generate value
	 *
	 * @param string $column Name of the column where to insert the value
	 * @param array $record The record
	 * @param array $columnTypes Column types
	 * @param array $search Search arguments
	 * @param UriBuilder $uriBuilder UriBuilder
	 * @param \Frohland\Ezqueries\Utility\TemplateUtility $templateUtility TemplateUtility
	 * @param \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository RecordManagementRepository
	 * @param array $arguments Arguments
	 * @return string $code
	 */
	public function generateValue($column, $record, $columnTypes, $search, $uriBuilder, $templateUtility = NULL, $recordManagementRepository = NULL, $arguments = NULL) {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $uriBuilder);
		if ($templateUtility == NULL) {
			$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
		}
		$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');

		$code = '';

		// Value settings
		if (isset($columnTypes[$column]['value'])) {
			$value = $columnTypes[$column]['value'];
			if (!isset($columnTypes[$column]['valueType'])) {
				$value = $record[$value];
			}
			if (isset($columnTypes[$column]['valueFunction'])) {
				if ($columnTypes[$column]['valueFunction'] == 'convertUmlaute') {
					$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ë,ï,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
					$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,e,i,y,a,e,i,o,u,a,e,i,o,u");
					$value = str_replace($search, $replace, $value);
					$search = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'ß', ' ');
					$replace = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'ss', '');
					$value = str_replace($search, $replace, $value);
					$value = preg_replace('([^a-z^A-Z])', '_', $value);
				}
				if ($columnTypes[$column]['valueFunction'] == 'htmlentities') {
					$value = htmlentities($value);
				}
				if ($columnTypes[$column]['valueFunction'] == 'htmlspecialchars') {
					$value = htmlspecialchars($value);
				}
				if ($columnTypes[$column]['valueFunction'] == 'uppercase') {
					$value = mb_strtoupper($value);
				}
			}
		} else {
			if (isset($columnTypes[$column]['valueSQL'])) {
				$data = array();
				foreach ($record as $recordColumn => $recordValue) {
					if ($columnTypes[$recordColumn]['type'] != 'numeric' && $columnTypes[$recordColumn]['type'] != 'int' && $columnTypes[$recordColumn]['type'] != 'boolean') {
						$data[$recordColumn]['value'] = $recordValue;
						$data[$recordColumn]['type'] = 'text';
					} else {
						$data[$recordColumn]['value'] = $recordValue;
						$data[$recordColumn]['type'] = 'numeric';
					}
				}
				$sqlQuery = $templateUtility -> fillMarkersInSQLStatement($columnTypes[$column]['valueSQL'], $arguments, $data, $recordManagementRepository);
				$records = $recordManagementRepository -> getRecordsBySQLQuery($sqlQuery);
				if ($records !== FALSE) {
					$value = $records[0][0];
				}
			} else {
				if (isset($columnTypes[$column]['filter'])) {
					$data = array();
					foreach ($record as $recordColumn => $recordValue) {
						if ($columnTypes[$recordColumn]['type'] != 'numeric' && $columnTypes[$recordColumn]['type'] != 'int' && $columnTypes[$recordColumn]['type'] != 'boolean') {
							$data[$recordColumn]['value'] = $recordValue;
							$data[$recordColumn]['type'] = 'text';
						} else {
							$data[$recordColumn]['value'] = $recordValue;
							$data[$recordColumn]['type'] = 'numeric';
						}
					}
					$value = $record[$column];
				} else {
					$value = $record[$column];
				}
			}
		}

		// Include hook to set a different value (return FALSE to hide value in template)
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['valueUtility']['hookSetValue'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['valueUtility']['hookSetValue'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$value = $_procObj -> hookSetValue($column, $record, $columnTypes, $value, $arguments);
			}
		}

		if ($value !== FALSE) {
			$value = $languageUtility -> translateValue($value);
			$originalValue = $value;

			switch($columnTypes[$column]['type']) {
				// Boolean column
				case 'boolean' :
					// Checkbox
					if ($columnTypes[$column]['render'] == 'checkbox') {
						if ($originalValue == 1) {
							$code .= '<input class="tx_ezqueries_checkbox" type="checkbox" value="' . $originalValue . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" checked="checked" onclick="return false;" />';
						} else {
							$code .= '<input class="tx_ezqueries_checkbox" type="checkbox" value="' . $originalValue . '" name="tx_ezqueries_ezqueriesplugin[' . $column . ']" onclick="return false;" />';
						}
					}

					// Yes/No
					if ($columnTypes[$column]['render'] == 'yesno') {
						if ($originalValue == 1) {
							$code .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_bool_yes', 'ezqueries');
						} else {
							$code .= \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_bool_no', 'ezqueries');
						}
					}

					// Number
					if ($columnTypes[$column]['render'] == 'number') {
						$code .= $value;
					}

					// Number - value only
					if ($columnTypes[$column]['valueRender'] == 'number') {
						$code = $value;
					}
					break;

				// Varchar column
				case 'varchar' :
					// Text
					if ($columnTypes[$column]['render'] == 'text' || $columnTypes[$column]['render'] == 'text_long' || $columnTypes[$column]['render'] == 'text_editor') {
						$code .= $value;
					}

					// Email
					if ($columnTypes[$column]['render'] == 'email') {
						$code .= '<a class="tx_ezqueries_data_link" href="mailto:' . $originalValue . '">' . $value . '</a>';
					}

					// Link
					if ($columnTypes[$column]['render'] == 'link') {
						$code .= '<a class="tx_ezqueries_data_link" href="' . $originalValue . '" target="_blank" >' . $value . '</a>';
					}

					// Image
					if ($columnTypes[$column]['render'] == 'image') {
						$altText = substr(strrchr($originalValue, '/'), 1);
						$code .= '<img class="tx_ezqueries_data_image" src="' . $originalValue . '" alt="' . $altText . '" />';
					}

					// Document
					if ($columnTypes[$column]['render'] == 'document') {
						if (isset($columnTypes[$column]['linkText']) && $originalValue !== '' && $originalValue !== NULL) {
							$linkText = $columnTypes[$column]['linkText'];
						} else {
							$linkText = substr(strrchr($originalValue, '/'), 1);
						}
						$code .= '<a class="tx_ezqueries_data_link" href="' . $originalValue . '" target="_blank" >' . $linkText . '</a>';
					}
					break;

				// Date column
				case 'date' :
					$code .= $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $originalValue);

					break;

				// Time column
				case 'time' :
					if (isset($columnTypes[$column]['dateformat'])) {
						$code .= $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $originalValue);
					} else {
						$code .= $value;
					}

					break;

				// Timestamp column
				case 'timestamp' :
					if (isset($columnTypes[$column]['dateformat'])) {
						$code .= $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $originalValue);
					} else {
						$code .= $value;
					}

					break;

				// Int column
				case 'int' :
					$code .= $conversionUtility -> convertNumber($columnTypes[$column]['decimals'], $columnTypes[$column]['dec_point'], $columnTypes[$column]['thousands_sep'], $originalValue);
					break;

				// Numeric column
				case 'numeric' :
					$code .= $conversionUtility -> convertNumber($columnTypes[$column]['decimals'], $columnTypes[$column]['dec_point'], $columnTypes[$column]['thousands_sep'], $originalValue);

					break;

				// Default
				default :
					$code .= $value;
					break;
			}

			// Highlight search strings in $value
			$searchString = $search[$column]['value'];
			$searchArray = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'ß');
			$replaceArray = array('a', 'A', 'o', 'O', 'u', 'U', 'ss');
			$searchString = str_replace($searchArray, $replaceArray, $searchString);
			$searchString = preg_quote($searchString, '/');
			if ($searchString !== '' && $searchString !== NULL && $search['highlighting'] == 'true') {
				if (isset($columnTypes[$column]['searchHighlightWrap'])) {
					$wrapString = trim($columnTypes[$column]['searchHighlightWrap']);
					$wrapBefore = $languageUtility -> translateValue(substr($wrapString, 0, strpos($wrapString, '|')));
					$wrapAfter = $languageUtility -> translateValue(substr($wrapString, strpos($wrapString, '|') + 1, strlen($wrapString) - strpos($wrapString, '|') - 1));
					$code = preg_replace('/(' . $searchString . ')/iu', '' . $wrapBefore . '${1}' . $wrapAfter . '', $code);
				} else {
					$code = preg_replace('/(' . $searchString . ')/iu', '<span class="tx_ezqueries_search_mark">${1}</span>', $code);
				}
			}

			$displayElement = TRUE;

			// Hook for setting display status of additional element (return FALSE to hide element)
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['valueUtility']['hookSetDisplayStatus'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['valueUtility']['hookSetDisplayStatus'] as $_classRef) {
					$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
					$displayElement = $_procObj -> hookSetDisplayStatus($column, $record);
				}
			}

			// Link to another page with ezqueries plugin / Content from another page via AJAX container
			if ((isset($columnTypes[$column]['linkTo']) || isset($columnTypes[$column]['contentFrom'])) AND $displayElement === TRUE) {
				$linkClass = '';
				$contentClass = '';
				$linkText = $code;
				$linkIcon = '';
				$linkTarget = '';
				$view = NULL;
				$loading = 'data-ezqueries-ajax-loading="true"';

				// View
				if (isset($columnTypes[$column]['linkToView'])) {
					$view = $columnTypes[$column]['linkToView'];
				}
				if (isset($columnTypes[$column]['contentFromView'])) {
					$view = $columnTypes[$column]['contentFromView'];
				}

				// Link label
				if (isset($columnTypes[$column]['linkLabel'])) {
					$linkText = $columnTypes[$column]['linkLabel'];
				}

				// link icon
				if (isset($columnTypes[$column]['linkIcon'])) {
					$linkIcon = $columnTypes[$column]['linkIcon'];
				}

				// Link design
				if (isset($columnTypes[$column]['linkDesign'])) {
					if ($columnTypes[$column]['linkDesign'] == 'button') {
						$linkClass .= ' tx_ezqueries_link_button tx_ezqueries_custom_button ';
					}
				}

				// Link type
				if (isset($columnTypes[$column]['linkType'])) {
					if ($columnTypes[$column]['linkType'] == 'popup') {
						$linkClass .= ' tx_ezqueries_link_popup ';
						$redirect = 'false';
					} else {
						if ($columnTypes[$column]['linkType'] == 'noRedirect') {
							$linkClass .= '';
							$redirect = 'false';
						}
					}
				} else {
					// Link target
					if (isset($columnTypes[$column]['linkTarget'])) {
						$linkTarget = ' data-ezqueries-link-target="' . $columnTypes[$column]['linkTarget'] . '"';
					} else {
						$linkClass .= ' tx_ezqueries_link_redirect ';
						$redirect = 'false';
					}
				}

				// Link parameters general
				if (isset($columnTypes[$column]['filter'])) {
					if (isset($columnTypes[$column]['filterColumn'])) {
						$filters[$columnTypes[$column]['filter']] = $record[$columnTypes[$column]['filterColumn']];
					} else {
						if (isset($columnTypes[$column]['filterValue'])) {
							$filters[$columnTypes[$column]['filter']] = $columnTypes[$column]['filterValue'];
						} else {
							$filters[$columnTypes[$column]['filter']] = $originalValue;
						}
					}
					if (isset($columnTypes[$column]['filterType'])) {
						$filters['filterType'] = $columnTypes[$column]['filterType'];
						if ($filters['filterType'] == 'custom') {
							$customFilter = $templateUtility -> fillMarkersInSQLStatement($columnTypes[$column]['filterValue'], $arguments, $data, $recordManagementRepository);
							$filters[$columnTypes[$column]['filter']] = $customFilter;
						}
					}
					$arguments = array('filters' => $filters);
				} else {
					$arguments = array();
				}

				// Link parameters additional
				if (isset($columnTypes[$column]['parameters'])) {
					$parameters = explode(',', $columnTypes[$column]['parameters']);
					foreach ($parameters as $parameter) {
						$parameter = trim($parameter);
						$parameterName = trim(substr($parameter, 0, strpos($parameter, '=')));
						$parameterValue = trim(substr($parameter, strpos($parameter, '=') + 1, strlen($parameter) - strpos($parameter, '=') - 1));
						if ($parameterValue[0] != '"') {
							$arguments[$parameterName] = $record[$parameterValue];
						} else {
							$arguments[$parameterName] = trim(substr($parameterValue, 1, strpos($parameterValue, '"', 1) - 1));
						}
					}
				}
				if (isset($columnTypes[$column]['primaryKey'])) {
					$parameters = explode(',', $columnTypes[$column]['primaryKey']);
					foreach ($parameters as $parameter) {
						$parameter = trim($parameter);
						$parameterName = trim(substr($parameter, 0, strpos($parameter, '=')));
						$parameterValue = trim(substr($parameter, strpos($parameter, '=') + 1, strlen($parameter) - strpos($parameter, '=') - 1));
						if ($parameterValue[0] != '"') {
							$arguments['primaryKeys'][$parameterName] = $record[$parameterValue];
						} else {
							$arguments['primaryKeys'][$parameterName] = trim(substr($parameterValue, 1, strpos($parameterValue, '"', 1) - 1));
						}
					}
				}

				// Custom link class
				if (isset($columnTypes[$column]['linkClass'])) {
					$linkClass .= ' ' . $columnTypes[$column]['linkClass'];
				}

				// Custom content class
				if (isset($columnTypes[$column]['contentClass'])) {
					$contentClass .= ' ' . $columnTypes[$column]['contentClass'];
				}

				// Tooltip?
				if (isset($columnTypes[$column]['tooltip'])) {
					if ($columnTypes[$column]['tooltip'] == 'true') {
						$linkClass .= ' tooltip';
					}
				}

				// Show loading status
				if (isset($columnTypes[$column]['loading'])) {
					$loading = 'data-ezqueries-ajax-loading="' . $columnTypes[$column]['loading'] . '"';
				}

				// Generate content
				if (isset($columnTypes[$column]['linkTo'])) {
					if (!is_numeric($columnTypes[$column]['linkTo'])) {
						$sqlQuery = 'SELECT uid FROM pages WHERE subtitle="' . $columnTypes[$column]['linkTo'] . '"';
						$records = $recordManagementRepository -> getRecordsBySQLQueryTypo3($sqlQuery);
						$columnTypes[$column]['linkTo'] = $records[0]['uid'];
					}
					$url = $urlUtility -> createURL($view, $arguments, $columnTypes[$column]['linkTo']);
					if ($columnTypes[$column]['linkType'] == 'url') {
						$code = $url;
					} else {
						$jsonConfig = '';

						if ($columnTypes[$column]['linkType'] == 'JSON') {
							$jsonConfig = 'data-ezqueries-json="true" data-ezqueries-json-id="' . $columnTypes[$column]['jsonID'] . '"';
						}

						if (isset($columnTypes[$column]['linkIcon'])) {
							$code = '<a class="tx_ezqueries_link ' . $linkClass . '" href="' . $url . '" ' . $linkTarget . ' data-title="' . $linkText . '" title="' . $linkText . '" ' . $jsonConfig . '><img src="' . $linkIcon . '" alt="' . $linkText . '" /></a>';
						} else {
							$code = '<a class="tx_ezqueries_link ' . $linkClass . '" href="' . $url . '" ' . $linkTarget . ' data-title="' . $linkText . '" title="' . $linkText . '" ' . $jsonConfig . '>' . $linkText . '</a>';
						}
					}
				} else {
					if (!is_numeric($columnTypes[$column]['contentFrom'])) {
						$sqlQuery = 'SELECT uid FROM pages WHERE subtitle="' . $columnTypes[$column]['contentFrom'] . '"';
						$records = $recordManagementRepository -> getRecordsBySQLQueryTypo3($sqlQuery);
						$columnTypes[$column]['contentFrom'] = $records[0]['uid'];
					}
					$url = $urlUtility -> createURL($view, $arguments, $columnTypes[$column]['contentFrom']);
					$randomNumber = mt_rand(0, 1000);
					if (isset($columnTypes[$column]['contentType'])) {
						if ($columnTypes[$column]['contentType'] == 'replace') {
							$code = '<div id="tx_ezqueries_ajax_content_' . $randomNumber . '" class="tx_ezqueries_ajax_content ' . $contentClass . '" data-ezqueries-ajax-url="' . $url . '" data-ezqueries-replace="true" ' . $loading . ' data-pid="' . $columnTypes[$column]['contentFrom'] . '"></div>';
						}
						if ($columnTypes[$column]['contentType'] == 'JSON') {
							$code = '<div id="tx_ezqueries_ajax_content_' . $randomNumber . '" class="tx_ezqueries_ajax_content ' . $contentClass . '" data-ezqueries-ajax-url="' . $url . '" data-ezqueries-json="true" data-ezqueries-json-id="' . $columnTypes[$column]['jsonID'] . '" ' . $loading . ' data-pid="' . $columnTypes[$column]['contentFrom'] . '"></div>';
						}
					} else {
						$code = '<div id="tx_ezqueries_ajax_content_' . $randomNumber . '" class="tx_ezqueries_ajax_content ' . $contentClass . '" data-ezqueries-ajax-url="' . $url . '"  ' . $loading . ' data-pid="' . $columnTypes[$column]['contentFrom'] . '"></div>';
					}
				}
			}

			// Additional Value
			if (isset($columnTypes[$column]['additionalValue']) && $code != '') {
				$code .= ' ' . $columnTypes[$column]['additionalValue'];
			}

			// Wrap Value
			if (isset($columnTypes[$column]['wrap']) && trim($code) != '') {
				$wrapString = trim($columnTypes[$column]['wrap']);
				$wrapBefore = $languageUtility -> translateValue(substr($wrapString, 0, strpos($wrapString, '|')));
				$wrapAfter = $languageUtility -> translateValue(substr($wrapString, strpos($wrapString, '|') + 1, strlen($wrapString) - strpos($wrapString, '|') - 1));
				$code = $wrapBefore . $code . $wrapAfter;
			}
		}

		return $code;
	}

}
?>