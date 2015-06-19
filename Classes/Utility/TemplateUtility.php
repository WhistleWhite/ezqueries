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
 * Template-Utility
 *
 * Utility class for template processing.
 */
class TemplateUtility {
	/**
	 * Type of the template (e.g. list, edit, detail)
	 *
	 * @var string
	 */
	public $templateType;

	/**
	 * Database record
	 *
	 * @var Tx_Ezqueries_Domain_Model_Record
	 */
	private $record;

	/**
	 * Database record data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Number of the record
	 *
	 * @var int
	 */
	public $recordNumber;

	/**
	 * Number of records
	 *
	 * @var int
	 */
	private $recordCount;

	/**
	 * Position of the record in the primary keys array
	 *
	 * @var int
	 */
	private $recordPosition;

	/**
	 * Primary key(s) of the record
	 *
	 * @var array
	 */
	public $primaryKeys;

	/**
	 * Primary keys of all records in the browser
	 *
	 * @var array
	 */
	public $primaryKeysArray;

	/**
	 * Column types of the record
	 *
	 * @var array
	 */
	private $columnTypes;

	/**
	 * Order by this column
	 *
	 * @var string
	 */
	public $orderBy;

	/**
	 * Order
	 *
	 * @var string
	 */
	public $order;

	/**
	 * Search arguments
	 *
	 * @var array
	 */
	public $search;

	/**
	 * Filter arguments
	 *
	 * @var array
	 */
	public $filters;

	/**
	 * Selected columns
	 *
	 * @var array
	 */
	private $selectedColumns;

	/**
	 * Uri builder
	 *
	 * @var UriBuilder
	 */
	private $uriBuilder;

	/**
	 * @var \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository
	 */
	private $recordManagementRepository;

	/**
	 * @var \Frohland\Ezqueries\Domain\Model\RecordManagement
	 */
	private $recordManagement;

	/**
	 * Custom SQL marker
	 *
	 * @var array
	 */
	private $customSQLMarker;

	/**
	 * The POST or GET parameters
	 *
	 * @var array
	 */
	private $arguments;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Template utility constructor
	 */
	function __construct() {
		$this -> objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		// Include hook to set custom SQL marker array (<custom>arrayIndex</custom> in SQL statement)
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['templateUtilty']['hookSetCustomSQLMarker'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['templateUtilty']['hookSetCustomSQLMarker'] as $_classRef) {
				$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
				$this -> customSQLMarker = $_procObj -> hookSetCustomSQLMarker();
			}
		}
	}

	/**
	 * Initialize the Template utility object
	 *
	 * @param string $templateType Type of the template (e.g. list, edit, detail)
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @param UriBuilder $uriBuilder UriBuilder
	 */
	public function initTemplateUtility($templateType, $recordManagement, $uriBuilder) {
		$this -> recordManagement = $recordManagement;
		$this -> recordManagementRepository = $recordManagement -> getRecordManagementRepository();
		$this -> arguments = $recordManagement -> getArguments();
		$this -> templateType = $templateType;
		$this -> record = $recordManagement -> getRecords(0);
		$this -> recordNumber = 0;
		if ($this -> record) {
			$this -> primaryKeys = $this -> record -> getPrimaryKeys();
		}
		$this -> primaryKeysArray = $recordManagement -> getPrimaryKeys();
		$this -> columnTypes = $recordManagement -> getTable() -> getColumnTypes();
		$this -> orderBy = $recordManagement -> getConditions() -> getOrderBy();
		$this -> order = $recordManagement -> getConditions() -> getOrder();
		$this -> search = $recordManagement -> getConditions() -> getSearch();
		$this -> filters = $recordManagement -> getConditions() -> getFilters();
		$this -> selectedColumns = $recordManagement -> getTable() -> getSelectedColumns();
		$this -> uriBuilder = $uriBuilder;

		$i = 0;
		$this -> recordPosition = 0;
		$countPrimaryKeys = count($this -> primaryKeys);
		$countRightKeys = 0;

		if ($this -> primaryKeysArray !== NULL) {
			foreach ($this->primaryKeysArray as $keys) {
				$countRightKeys = 0;
				foreach ($this->primaryKeys as $column => $value) {
					if ($keys[$column] == $value) {
						$countRightKeys++;
					}
				}
				if ($countRightKeys == $countPrimaryKeys) {
					$this -> recordPosition = $i;
					break;
				} else {
					$i++;
				}
			}
		}
	}

	/**
	 * Search for markers in a code (template) and replace them with content
	 *
	 * @param string $code Any code
	 * @return string $code
	 */
	public function fillMarkers($code) {
		// Markers
		$formValueMarkerStart = '<formelement';
		$formValueMarkerEnd = '</formelement>';
		$valueMarkerStart = '<value>';
		$valueMarkerEnd = '</value>';
		$sortMarkerStart = '<sort';
		$sortMarkerEnd = '</sort>';
		$submitMarkerStart = '<submit';
		$submitMarkerEnd = '</submit>';
		$detailMarkerStart = '<detail';
		$detailMarkerEnd = '</detail>';
		$editMarkerStart = '<edit';
		$editMarkerEnd = '</edit>';
		$previousMarkerStart = '<previous';
		$previousMarkerEnd = '</previous>';
		$nextMarkerStart = '<next';
		$nextMarkerEnd = '</next>';
		$closeMarkerStart = '<close';
		$closeMarkerEnd = '</close>';
		$deleteMarkerStart = '<delete';
		$deleteMarkerEnd = '</delete>';
		$newMarkerStart = '<new';
		$newMarkerEnd = '</new>';
		$searchMarkerStart = '<search';
		$searchMarkerEnd = '</search>';
		$listMarkerStart = '<list';
		$listMarkerEnd = '</list>';
		$parityMarker = '<parity />';
		$indexMarker = '<index />';
		$recordCountMarker = '<recordcount />';
		$contentMarkerStart = '<content';
		$contentMarkerEnd = '</content>';
		$labelMarkerStart = '<columnlabel>';
		$labelMarkerEnd = '</columnlabel>';
		$loclabelMarkerStart = '<locallang>';
		$loclabelMarkerEnd = '</locallang>';
		$argumentMarkerStart = '<parameter>';
		$argumentMarkerEnd = '</parameter>';
		$pageBrowserMarker = '<pagebrowser />';

		$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');
		$valueUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ValueUtility');
		$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
		$filterUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\FilterUtility');
		$recordBrowser = $this -> objectManager -> get('Frohland\\Ezqueries\\ViewHelpers\\PageBrowserViewHelper');
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> uriBuilder);
		$formUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\FormUtility', $this -> uriBuilder);

		$this -> data = array();
		$this -> primaryKeys = array();

		if ($this -> record) {
			$this -> data = $this -> record -> getData();
			$this -> primaryKeys = $this -> record -> getPrimaryKeys();
		}

		// Template type?
		switch($this->templateType) {

			// #### Edit ####
			case 'edit' :
				// Form value
				if (strpos($code, $formValueMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $formValueMarkerStart), (strpos($code, $formValueMarkerEnd) + strlen($formValueMarkerEnd)) - strpos($code, $formValueMarkerStart));
					$formValueMarkerAttributes = $this -> getMarkerAttributes($markerCode, $formValueMarkerStart);
					$formValueMarkerValue = $this -> getMarkerValue($markerCode, $formValueMarkerEnd);

					$replaceCode = '';

					// Form value without attributes
					if ($formValueMarkerAttributes['type'] == NULL) {
						if ($this -> columnTypes[$formValueMarkerValue]['type'] !== 'additional' || isset($this -> columnTypes[$formValueMarkerValue]['additionalType'])) {
							$replaceCode .= $formUtility -> generateFormElement($formValueMarkerValue, $this -> data[$formValueMarkerValue], $this -> columnTypes, 'edit', $this -> data);
						} else {
							$replaceCode = $valueUtility -> generateValue($formValueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);
						}
					} else {
						// Form value with attributes
						if ($formValueMarkerAttributes['type'] == 'select') {
							$options = explode(',', $formValueMarkerAttributes['options']);
							$replaceCode .= '<select class="tx_ezqueries_select" id="' . $formValueMarkerValue . '" size="1" name="tx_ezqueries_ezqueriesplugin[' . $formValueMarkerValue . ']">';
							$replaceCode .= '<option selected="selected">' . $this -> data[$formValueMarkerValue] . '</option>';
							foreach ($options as $option) {
								$replaceCode .= '<option>' . $option . '</option>';
							}
							$replaceCode .= '</select>';
						}
					}

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				} else {
					// Edit submit
					if (strpos($code, $submitMarkerStart) !== FALSE) {
						$markerCode = substr($code, strpos($code, $submitMarkerStart), (strpos($code, $submitMarkerEnd) + strlen($submitMarkerEnd)) - strpos($code, $submitMarkerStart));
						$submitMarkerAttributes = $this -> getMarkerAttributes($markerCode, $submitMarkerStart);
						$submitMarkerValue = $this -> getMarkerValue($markerCode, $submitMarkerEnd);

						$replaceCode = '';
						$submitClass = '';

						if ($submitMarkerAttributes['onSubmit'] == 'dontClosePopup') {
							$submitClass = 'tx_ezqueries_submit_popup';
						}

						$arguments = array("primaryKeys" => $this -> primaryKeys, "search" => $this -> search, "filters" => $this -> filters);
						$url = $urlUtility -> createURL("update", $arguments);
						$replaceCode = '<input class="tx_ezqueries_submit tx_ezqueries_submit_edit ' . $submitClass . '" name="' . $url . '" type="submit" value="' . $languageUtility -> translateValue($submitMarkerValue) . '" />';

						$newCode = str_replace($markerCode, $replaceCode, $code);
						$code = $this -> fillMarkers($newCode);
						return $code;
					} else {
						// Value
						if (strpos($code, $valueMarkerStart) !== FALSE) {
							$markerCode = substr($code, strpos($code, $valueMarkerStart), (strpos($code, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($code, $valueMarkerStart));
							$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

							$replaceCode = $valueUtility -> generateValue($valueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);

							$newCode = str_replace($markerCode, $replaceCode, $code);
							$code = $this -> fillMarkers($newCode);
							return $code;
						} else {
							break;
						}
					}
				}
			// #### New ####
			case 'new' :
				// Form value
				if (strpos($code, $formValueMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $formValueMarkerStart), (strpos($code, $formValueMarkerEnd) + strlen($formValueMarkerEnd)) - strpos($code, $formValueMarkerStart));
					$formValueMarkerAttributes = $this -> getMarkerAttributes($markerCode, $formValueMarkerStart);
					$formValueMarkerValue = $this -> getMarkerValue($markerCode, $formValueMarkerEnd);

					$replaceCode = '';

					// Form value without attributes
					if ($formValueMarkerAttributes['type'] == NULL) {
						$replaceCode .= $formUtility -> generateFormElement($formValueMarkerValue, $this -> data[$formValueMarkerValue], $this -> columnTypes, 'new', $this -> data);
					} else {
						// Form value with attributes
						if ($formValueMarkerAttributes['type'] == 'select') {
							$options = explode(',', $formValueMarkerAttributes['options']);
							$replaceCode .= '<select class="tx_ezqueries_select" id="' . $formValueMarkerValue . '" size="1" name="tx_ezqueries_ezqueriesplugin[' . $formValueMarkerValue . ']">';
							$replaceCode .= '<option selected="selected">' . $this -> data[$formValueMarkerValue] . '</option>';
							foreach ($options as $option) {
								$replaceCode .= '<option>' . $option . '</option>';
							}
							$replaceCode .= '</select>';
						}
					}

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				} else {
					// New submit
					if (strpos($code, $submitMarkerStart) !== FALSE) {
						$markerCode = substr($code, strpos($code, $submitMarkerStart), (strpos($code, $submitMarkerEnd) + strlen($submitMarkerEnd)) - strpos($code, $submitMarkerStart));
						$submitMarkerAttributes = $this -> getMarkerAttributes($markerCode, $submitMarkerStart);
						$submitMarkerValue = $this -> getMarkerValue($markerCode, $submitMarkerEnd);

						$replaceCode = '';
						$submitClass = '';

						if ($submitMarkerAttributes['onSubmit'] == 'dontClosePopup') {
							$submitClass = 'tx_ezqueries_submit_popup';
						}

						$arguments = array("search" => $this -> search, "filters" => $this -> filters);
						$url = $urlUtility -> createURL("create", $arguments);
						$replaceCode = '<input class="tx_ezqueries_submit tx_ezqueries_submit_new ' . $submitClass . '" name="' . $url . '" type="submit" value="' . $languageUtility -> translateValue($submitMarkerValue) . '" />';

						$newCode = str_replace($markerCode, $replaceCode, $code);
						$code = $this -> fillMarkers($newCode);
						return $code;
					} else {
						// Value
						if (strpos($code, $valueMarkerStart) !== FALSE) {
							$markerCode = substr($code, strpos($code, $valueMarkerStart), (strpos($code, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($code, $valueMarkerStart));
							$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

							$replaceCode = $valueUtility -> generateValue($valueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);

							$newCode = str_replace($markerCode, $replaceCode, $code);
							$code = $this -> fillMarkers($newCode);
							return $code;
						} else {
							break;
						}
					}
				}
			// #### Search ####
			case 'search' :
				// Form value
				if (strpos($code, $formValueMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $formValueMarkerStart), (strpos($code, $formValueMarkerEnd) + strlen($formValueMarkerEnd)) - strpos($code, $formValueMarkerStart));
					$formValueMarkerAttributes = $this -> getMarkerAttributes($markerCode, $formValueMarkerStart);
					$formValueMarkerValue = $this -> getMarkerValue($markerCode, $formValueMarkerEnd);

					$replaceCode = '';
					$replaceCode = $filterUtility -> generateFilterElement($formValueMarkerValue, $this -> columnTypes, $formValueMarkerAttributes['placeholder']);

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				} else {
					// Search submit
					if (strpos($code, $submitMarkerStart) !== FALSE) {
						$markerCode = substr($code, strpos($code, $submitMarkerStart), (strpos($code, $submitMarkerEnd) + strlen($submitMarkerEnd)) - strpos($code, $submitMarkerStart));
						$submitMarkerAttributes = $this -> getMarkerAttributes($markerCode, $submitMarkerStart);
						$submitMarkerValue = $this -> getMarkerValue($markerCode, $submitMarkerEnd);

						$replaceCode = '';
						$submitClass = '';

						if ($submitMarkerAttributes['onSubmit'] == 'dontClosePopup') {
							$submitClass = 'tx_ezqueries_submit_popup';
						}

						$arguments = array("filters" => $this -> filters);
						$url = $urlUtility -> createURL("list", $arguments);
						$replaceCode = '<input class="tx_ezqueries_submit tx_ezqueries_submit_search ' . $submitClass . '" name="' . $url . '" type="submit" value="' . $languageUtility -> translateValue($submitMarkerValue) . '" />';

						$newCode = str_replace($markerCode, $replaceCode, $code);
						$code = $this -> fillMarkers($newCode);
						return $code;
					} else {
						// Value
						if (strpos($code, $valueMarkerStart) !== FALSE) {
							$markerCode = substr($code, strpos($code, $valueMarkerStart), (strpos($code, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($code, $valueMarkerStart));
							$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

							$replaceCode = $valueUtility -> generateValue($valueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);

							$newCode = str_replace($markerCode, $replaceCode, $code);
							$code = $this -> fillMarkers($newCode);
							return $code;
						} else {
							break;
						}
					}
				}
			// #### Detail ####
			case 'detail' :
				// Value
				if (strpos($code, $valueMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $valueMarkerStart), (strpos($code, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($code, $valueMarkerStart));
					$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

					$replaceCode = $valueUtility -> generateValue($valueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				} else {
					// Content
					if (strpos($code, $contentMarkerStart) !== FALSE) {
						$markerCode = substr($code, strpos($code, $contentMarkerStart), (strpos($code, $contentMarkerEnd) + strlen($contentMarkerEnd)) - strpos($code, $contentMarkerStart));
						$contentMarkerAttributes = $this -> getMarkerAttributes($markerCode, $contentMarkerStart);
						$contentMarkerValue = $this -> getMarkerValue($markerCode, $contentMarkerEnd);

						$replaceCode = '';
						$filters = array();

						if (isset($contentMarkerAttributes['filter'])) {
							$filters[$contentMarkerAttributes['filter']] = $contentMarkerAttributes['filterValue'];
						}

						$arguments = array('filters' => $filters);
						$url = $urlUtility -> createURL(NULL, $arguments, $contentMarkerValue);

						$replaceCode = '<div class="tx_ezqueries_ajax_content" id="' . $url . '"></div>';

						$newCode = str_replace($markerCode, $replaceCode, $code);
						$code = $this -> fillMarkers($newCode);
						return $code;
					}
				}
				break;
			// #### List ####
			case 'list' :
				// Value
				if (strpos($code, $valueMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $valueMarkerStart), (strpos($code, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($code, $valueMarkerStart));
					$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

					$replaceCode = $valueUtility -> generateValue($valueMarkerValue, $this -> data, $this -> columnTypes, $this -> search, $this -> uriBuilder, $this, $this -> recordManagementRepository, $this -> arguments);

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				} else {
					// New link
					if (strpos($code, $newMarkerStart) !== FALSE) {
						$markerCode = substr($code, strpos($code, $newMarkerStart), (strpos($code, $newMarkerEnd) + strlen($newMarkerEnd)) - strpos($code, $newMarkerStart));
						$newMarkerAttributes = $this -> getMarkerAttributes($markerCode, $newMarkerStart);
						$newMarkerValue = $this -> getMarkerValue($markerCode, $newMarkerEnd);

						$replaceCode = '';
						$linkClass = '';
						$linkTitle = '';

						if ($newMarkerAttributes['type'] == 'button') {
							$linkClass = 'tx_ezqueries_link_button';
						}
						if (isset($newMarkerAttributes['title'])) {
							$linkTitle = $languageUtility -> translateValue($newMarkerAttributes['title']);
						}

						$arguments = array("search" => $this -> search, "filters" => $this -> filters);
						$url = $urlUtility -> createURL("new", $arguments);
						$replaceCode = '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_new tx_ezqueries_' . $this -> templateType . '_link_new" href="' . $url . '" >' . $languageUtility -> translateValue($newMarkerValue) . '</a>';

						$newCode = str_replace($markerCode, $replaceCode, $code);
						$code = $this -> fillMarkers($newCode);
						return $code;
					} else {
						// Sort
						if (strpos($code, $sortMarkerStart) !== FALSE) {
							$markerCode = substr($code, strpos($code, $sortMarkerStart), (strpos($code, $sortMarkerEnd) + strlen($sortMarkerEnd)) - strpos($code, $sortMarkerStart));
							$sortMarkerAttributes = $this -> getMarkerAttributes($markerCode, $sortMarkerStart);
							$sortMarkerValue = $this -> getMarkerValue($markerCode, $sortMarkerEnd);

							$replaceCode = '';
							$linkTitle = '';

							$arguments = array("orderBy" => $sortMarkerValue, "order" => 'ASC', "search" => $this -> search, "filters" => $this -> filters);
							$imgClass = 'tx_ezqueries_image_order';

							if ($sortMarkerValue == $this -> orderBy) {
								if ($this -> order == 'ASC') {
									$arguments = array("orderBy" => $sortMarkerValue, "order" => 'DESC', "search" => $this -> search, "filters" => $this -> filters);
									$imgClass = 'tx_ezqueries_image_order tx_ezqueries_image_order_asc';
								} else {
									$arguments = array("orderBy" => $sortMarkerValue, "order" => 'ASC', "search" => $this -> search, "filters" => $this -> filters);
									$imgClass = 'tx_ezqueries_image_order tx_ezqueries_image_order_desc';
								}
							}
							$url = $urlUtility -> createURL("list", $arguments);

							if (isset($sortMarkerAttributes['title'])) {
								$linkTitle = $languageUtility -> translateValue($sortMarkerAttributes['title']);
							}

							if ($sortMarkerAttributes['as'] == NULL) {
								$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link tx_ezqueries_link_sort tx_ezqueries_list_link_sort ' . $imgClass . '" href="' . $url . '"><span class="tx_ezqueries_link_sort_text">' . $sortMarkerValue . '</span></a>';
							} else {
								$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link tx_ezqueries_link_sort tx_ezqueries_list_link_sort ' . $imgClass . '" href="' . $url . '"><span class="tx_ezqueries_link_sort_text">' . $languageUtility -> translateValue($sortMarkerAttributes['as']) . '</span></a>';
							}

							$newCode = str_replace($markerCode, $replaceCode, $code);
							$code = $this -> fillMarkers($newCode);
							return $code;
						} else {
							// Parity
							if (strpos($code, $parityMarker) !== FALSE) {
								$markerCode = $parityMarker;

								if ($this -> recordNumber % 2 == 0) {
									$replaceCode = 'odd';
								} else {
									$replaceCode = 'even';
								}

								$newCode = str_replace($markerCode, $replaceCode, $code);
								$code = $this -> fillMarkers($newCode);
								return $code;
							} else {
								// Record count
								if (strpos($code, $recordCountMarker) !== FALSE) {
									$markerCode = $recordCountMarker;

									$replaceCode = $this -> recordManagement -> getConditions() -> getRecordsCount();

									$newCode = str_replace($markerCode, $replaceCode, $code);
									$code = $this -> fillMarkers($newCode);
									return $code;
								} else {
									// Search link
									if (strpos($code, $searchMarkerStart) !== FALSE) {
										$markerCode = substr($code, strpos($code, $searchMarkerStart), (strpos($code, $searchMarkerEnd) + strlen($searchMarkerEnd)) - strpos($code, $searchMarkerStart));
										$searchMarkerAttributes = $this -> getMarkerAttributes($markerCode, $searchMarkerStart);
										$searchMarkerValue = $this -> getMarkerValue($markerCode, $searchMarkerEnd);

										$replaceCode = '';
										$linkClass = '';
										$linkTitle = '';

										if ($searchMarkerAttributes['type'] == 'button') {
											$linkClass = 'tx_ezqueries_link_button';
										}
										if (isset($searchMarkerAttributes['title'])) {
											$linkTitle = $languageUtility -> translateValue($searchMarkerAttributes['title']);
										}

										$arguments = array("filters" => $this -> filters);
										$url = $urlUtility -> createURL("search", $arguments);
										$replaceCode = '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_search tx_ezqueries_' . $this -> templateType . '_link_search" href="' . $url . '" >' . $languageUtility -> translateValue($searchMarkerValue) . '</a>';

										$newCode = str_replace($markerCode, $replaceCode, $code);
										$code = $this -> fillMarkers($newCode);
										return $code;
									} else {
										// Index
										if (strpos($code, $indexMarker) !== FALSE) {
											$markerCode = $indexMarker;

											$replaceCode = $this -> recordNumber;

											$newCode = str_replace($markerCode, $replaceCode, $code);
											$code = $this -> fillMarkers($newCode);
											return $code;
										} else {
											// Page browser
											if (strpos($code, $pageBrowserMarker) !== FALSE) {
												$markerCode = $pageBrowserMarker;

												$replaceCode = $recordBrowser -> render($this -> recordManagement, $this -> uriBuilder);

												$newCode = str_replace($markerCode, $replaceCode, $code);
												$code = $this -> fillMarkers($newCode);
												return $code;
											} else {
												break;
											}
										}
									}
								}
							}
						}
					}
				}
		}

		// Detail link
		if (strpos($code, $detailMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $detailMarkerStart), (strpos($code, $detailMarkerEnd) + strlen($detailMarkerEnd)) - strpos($code, $detailMarkerStart));
			$detailMarkerAttributes = $this -> getMarkerAttributes($markerCode, $detailMarkerStart);
			$detailMarkerValue = $this -> getMarkerValue($markerCode, $detailMarkerEnd);

			$replaceCode = '';
			$linkClass = '';
			$linkTitle = '';

			if ($detailMarkerAttributes['type'] == 'button') {
				$linkClass = 'tx_ezqueries_link_button';
			}
			if (isset($detailMarkerAttributes['title'])) {
				$linkTitle = $languageUtility -> translateValue($detailMarkerAttributes['title']);
			}

			if ($this -> templateType == 'list') {
				$arguments = array("primaryKeys" => $this -> primaryKeysArray[$this -> recordNumber], "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
			} else {
				$arguments = array("primaryKeys" => $this -> primaryKeys, "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
			}

			$url = $urlUtility -> createURL("detail", $arguments);
			$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_detail tx_ezqueries_' . $this -> templateType . '_link_detail" href="' . $url . '" >' . $languageUtility -> translateValue($detailMarkerValue) . '</a>';

			$newCode = str_replace($markerCode, $replaceCode, $code);
			$code = $this -> fillMarkers($newCode);
			return $code;
		} else {
			// Edit link
			if (strpos($code, $editMarkerStart) !== FALSE) {
				$markerCode = substr($code, strpos($code, $editMarkerStart), (strpos($code, $editMarkerEnd) + strlen($editMarkerEnd)) - strpos($code, $editMarkerStart));
				$editMarkerAttributes = $this -> getMarkerAttributes($markerCode, $editMarkerStart);
				$editMarkerValue = $this -> getMarkerValue($markerCode, $editMarkerEnd);

				$replaceCode = '';
				$linkClass = '';
				$linkTitle = '';

				if ($editMarkerAttributes['type'] == 'button') {
					$linkClass = 'tx_ezqueries_link_button';
				}
				if (isset($editMarkerAttributes['title'])) {
					$linkTitle = $languageUtility -> translateValue($editMarkerAttributes['title']);
				}

				if ($this -> templateType == 'list') {
					$arguments = array("primaryKeys" => $this -> primaryKeysArray[$this -> recordNumber], "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
				} else {
					$arguments = array("primaryKeys" => $this -> primaryKeys, "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
				}

				$url = $urlUtility -> createURL("edit", $arguments);
				$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_edit tx_ezqueries_' . $this -> templateType . '_link_edit" href="' . $url . '" >' . $languageUtility -> translateValue($editMarkerValue) . '</a>';

				$newCode = str_replace($markerCode, $replaceCode, $code);
				$code = $this -> fillMarkers($newCode);
				return $code;
			} else {
				// Delete link
				if (strpos($code, $deleteMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $deleteMarkerStart), (strpos($code, $deleteMarkerEnd) + strlen($deleteMarkerEnd)) - strpos($code, $deleteMarkerStart));
					$deleteMarkerAttributes = $this -> getMarkerAttributes($markerCode, $deleteMarkerStart);
					$deleteMarkerValue = $this -> getMarkerValue($markerCode, $deleteMarkerEnd);

					$replaceCode = '';
					$linkClass = '';
					$linkTitle = '';

					if ($deleteMarkerAttributes['type'] == 'button') {
						$linkClass = 'tx_ezqueries_link_button';
					}
					if (isset($deleteMarkerAttributes['title'])) {
						$linkTitle = $languageUtility -> translateValue($deleteMarkerAttributes['title']);
					}

					if ($this -> templateType == 'list') {
						$arguments = array("primaryKeys" => $this -> primaryKeysArray[$this -> recordNumber], "search" => $this -> search, "filters" => $this -> filters);
					} else {
						$arguments = array("primaryKeys" => $this -> primaryKeys, "search" => $this -> search, "filters" => $this -> filters);
					}

					$url = $urlUtility -> createURL("delete", $arguments);
					$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_delete tx_ezqueries_' . $this -> templateType . '_link_delete" href="' . $url . '" >' . $languageUtility -> translateValue($deleteMarkerValue) . '</a>';

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				}
			}
		}

		if ($this -> templateType == 'detail' || $this -> templateType == 'edit' || $this -> templateType == 'new' || $this -> templateType == 'search') {
			// Close link
			if (strpos($code, $closeMarkerStart) !== FALSE) {
				$markerCode = substr($code, strpos($code, $closeMarkerStart), (strpos($code, $closeMarkerEnd) + strlen($closeMarkerEnd)) - strpos($code, $closeMarkerStart));
				$closeMarkerAttributes = $this -> getMarkerAttributes($markerCode, $closeMarkerStart);
				$closeMarkerValue = $this -> getMarkerValue($markerCode, $closeMarkerEnd);

				$replaceCode = '';
				$linkClass = '';
				$linkTitle = '';

				if ($closeMarkerAttributes['type'] == 'button') {
					$linkClass = 'tx_ezqueries_link_button';
				}
				if (isset($closeMarkerAttributes['title'])) {
					$linkTitle = $languageUtility -> translateValue($closeMarkerAttributes['title']);
				}

				if ($this -> templateType == 'detail' || $this -> templateType == 'edit') {
					$arguments = array("search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
				} else {
					$arguments = array("search" => $this -> search, "filters" => $this -> filters);
				}
				$url = $urlUtility -> createURL("list", $arguments);
				$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_' . $this -> templateType . ' tx_ezqueries_link_abort" href="' . $url . '" >' . $languageUtility -> translateValue($closeMarkerValue) . '</a>';

				$newCode = str_replace($markerCode, $replaceCode, $code);
				$code = $this -> fillMarkers($newCode);
				return $code;
			}
		}

		if ($this -> templateType == 'detail' || $this -> templateType == 'edit') {
			// Previous link
			if (strpos($code, $previousMarkerStart) !== FALSE) {
				$markerCode = substr($code, strpos($code, $previousMarkerStart), (strpos($code, $previousMarkerEnd) + strlen($previousMarkerEnd)) - strpos($code, $previousMarkerStart));
				$previousMarkerAttributes = $this -> getMarkerAttributes($markerCode, $previousMarkerStart);
				$previousMarkerValue = $this -> getMarkerValue($markerCode, $previousMarkerEnd);

				$replaceCode = '';
				$linkClass = '';
				$linkTitle = '';

				if ($previousMarkerAttributes['type'] == 'button') {
					$linkClass = 'tx_ezqueries_link_button';
				}
				if (isset($previousMarkerAttributes['title'])) {
					$linkTitle = $languageUtility -> translateValue($previousMarkerAttributes['title']);
				}

				if ($this -> recordPosition != 0) {
					$arguments = array("primaryKeys" => $this -> primaryKeysArray[$this -> recordPosition - 1], "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
					$url = $urlUtility -> createURL($this -> templateType, $arguments);
					$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_' . $this -> templateType . '" href="' . $url . '">' . $languageUtility -> translateValue($previousMarkerValue) . '</a>';
				}

				$newCode = str_replace($markerCode, $replaceCode, $code);
				$code = $this -> fillMarkers($newCode);
				return $code;
			} else {
				// Next link
				if (strpos($code, $nextMarkerStart) !== FALSE) {
					$markerCode = substr($code, strpos($code, $nextMarkerStart), (strpos($code, $nextMarkerEnd) + strlen($nextMarkerEnd)) - strpos($code, $nextMarkerStart));
					$nextMarkerAttributes = $this -> getMarkerAttributes($markerCode, $nextMarkerStart);
					$nextMarkerValue = $this -> getMarkerValue($markerCode, $nextMarkerEnd);

					$replaceCode = '';
					$linkClass = '';
					$linkTitle = '';

					if ($nextMarkerAttributes['type'] == 'button') {
						$linkClass = 'tx_ezqueries_link_button';
					}
					if (isset($nextMarkerAttributes['title'])) {
						$linkTitle = $languageUtility -> translateValue($nextMarkerAttributes['title']);
					}

					if ($this -> recordPosition < count($this -> primaryKeysArray) - 1) {
						$arguments = array("primaryKeys" => $this -> primaryKeysArray[$this -> recordPosition + 1], "search" => $this -> search, "orderBy" => $this -> orderBy, "order" => $this -> order, "filters" => $this -> filters);
						$url = $urlUtility -> createURL($this -> templateType, $arguments);
						$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . ' tx_ezqueries_link_' . $this -> templateType . '" href="' . $url . '">' . $languageUtility -> translateValue($nextMarkerValue) . '</a>';
					}

					$newCode = str_replace($markerCode, $replaceCode, $code);
					$code = $this -> fillMarkers($newCode);
					return $code;
				}
			}
		}

		// Labels
		if (strpos($code, $labelMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $labelMarkerStart), (strpos($code, $labelMarkerEnd) + strlen($labelMarkerEnd)) - strpos($code, $labelMarkerStart));
			$labelMarkerValue = $this -> getMarkerValue($markerCode, $labelMarkerEnd);

			$replaceCode = '<label for="' . str_replace('.', '_', $labelMarkerValue) . '">' . $this -> selectedColumns[$labelMarkerValue]['columnName'];
			if (($this -> columnTypes[$labelMarkerValue]['not_null'] == TRUE || $this -> columnTypes[$labelMarkerValue]['required'] == 'true') && ($this -> templateType == 'edit' || $this -> templateType == 'new')) {
				$replaceCode .= '<span class="tx_ezqueries_required_mark"></span>';
			}

			$replaceCode .= '</label>';

			// Hook for setting a custom label (label, column, view)
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['templateUtilty']['hookSetCustomLabel'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['templateUtilty']['hookSetCustomLabel'] as $_classRef) {
					$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
					$replaceCode = $_procObj -> hookSetCustomLabel($replaceCode, $labelMarkerValue, $this -> templateType, $this -> data);
				}
			}

			$newCode = str_replace($markerCode, $replaceCode, $code);
			$code = $this -> fillMarkers($newCode);
			return $code;
		}
		if (strpos($code, $loclabelMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $loclabelMarkerStart), (strpos($code, $loclabelMarkerEnd) + strlen($loclabelMarkerEnd)) - strpos($code, $loclabelMarkerStart));
			$labelMarkerValue = $this -> getMarkerValue($markerCode, $loclabelMarkerEnd);

			$replaceCode = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($labelMarkerValue, 'ezqueries');

			$newCode = str_replace($markerCode, $replaceCode, $code);
			$code = $this -> fillMarkers($newCode);
			return $code;
		}

		// Arguments
		if (strpos($code, $argumentMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $argumentMarkerStart), (strpos($code, $argumentMarkerEnd) + strlen($argumentMarkerEnd)) - strpos($code, $argumentMarkerStart));
			$argumentMarkerValue = $this -> getMarkerValue($markerCode, $argumentMarkerEnd);

			$replaceCode = $this -> arguments[$argumentMarkerValue];

			$newCode = str_replace($markerCode, $replaceCode, $code);
			$code = $this -> fillMarkers($newCode);
			return $code;
		}

		// List link
		if (strpos($code, $listMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $listMarkerStart), (strpos($code, $listMarkerEnd) + strlen($listMarkerEnd)) - strpos($code, $listMarkerStart));
			$listMarkerAttributes = $this -> getMarkerAttributes($markerCode, $listMarkerStart);
			$listMarkerValue = $this -> getMarkerValue($markerCode, $listMarkerEnd);

			$linkClass = '';
			$linkTitle = '';

			if (isset($listMarkerAttributes['title'])) {
				$linkTitle = $languageUtility -> translateValue($listMarkerAttributes['title']);
			}

			if (isset($listMarkerAttributes['linkTo'])) {
				$page = $listMarkerAttributes['linkTo'];
				$redirect = 'true';
				// Link type
				if (isset($listMarkerAttributes['type'])) {
					if ($listMarkerAttributes['type'] == 'button') {
						$linkClass = 'tx_ezqueries_link_button';
					}
				}
				// Redirect
				if (isset($listMarkerAttributes['redirect'])) {
					$redirect = $listMarkerAttributes['redirect'];
				}
				if ($redirect == 'true') {
					$linkClass .= ' tx_ezqueries_link_redirect ';
				}
				// Filter column
				if (isset($listMarkerAttributes['filter'])) {
					if (isset($listMarkerAttributes['filterColumn'])) {
						$filters[$listMarkerAttributes['filter']] = $this -> data[$listMarkerAttributes['filterColumn']];
					} else {
						$filters[$listMarkerAttributes['filter']] = $this -> data[$listMarkerAttributes['filter']];
					}
					$arguments = array('filters' => $filters);
				} else {
					$arguments = array();
				}

				$url = $urlUtility -> createURL('list', $arguments, $page);
				$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . '" onclick="return ' . $redirect . ';" href="' . $url . '">' . $languageUtility -> translateValue($listMarkerValue) . '</a>';
			} else {
				// Link type
				if (isset($listMarkerAttributes['type'])) {
					if ($listMarkerAttributes['type'] == 'button') {
						$linkClass = 'tx_ezqueries_link_button';
					}
				}
				$arguments = array("filters" => $this -> filters);
				$url = $urlUtility -> createURL('list', $arguments);
				$replaceCode .= '<a title="' . $linkTitle . '" class="tx_ezqueries_link ' . $linkClass . '" href="' . $url . '">' . $languageUtility -> translateValue($listMarkerValue) . '</a>';
			}

			$newCode = str_replace($markerCode, $replaceCode, $code);
			$code = $this -> fillMarkers($newCode);
			return $code;
		}

		return $code;
	}

	/**
	 * Search for markers in a SQL statement and replace them with content
	 *
	 * @param string $statement SQL statement
	 * @param array $arguments Arguments
	 * @param array $data Record Data
	 * @return string $statement Parsed SQL statement
	 */
	public function fillMarkersInSQLStatement($statement, $arguments, $data = NULL, $recordManagementRepository) {
		// Markers
		$parameterMarkerStart = '<parameter>';
		$parameterMarkerEnd = '</parameter>';
		$valueMarkerStart = '<columnvalue>';
		$valueMarkerEnd = '</columnvalue>';
		$customMarkerStart = '<custom>';
		$customMarkerEnd = '</custom>';

		if (strpos($statement, $valueMarkerStart) !== FALSE) {
			$markerCode = substr($statement, strpos($statement, $valueMarkerStart), (strpos($statement, $valueMarkerEnd) + strlen($valueMarkerEnd)) - strpos($statement, $valueMarkerStart));
			$valueMarkerValue = $this -> getMarkerValue($markerCode, $valueMarkerEnd);

			if ($data !== NULL) {
				if ($valueMarkerValue == '###AI###') {
					$replaceCode = $data['ezqueriesAIValue']['value'];
				} else {
					if ($data[$valueMarkerValue]['type'] == 'text') {
						$connection = $recordManagementRepository -> connect();
						$replaceCode = $connection -> qstr($data[$valueMarkerValue]['value']);
						$connection -> Close();
					} else {
						$replaceCode = $data[$valueMarkerValue]['value'];
					}
				}
			} else {
				$replaceCode = '';
			}

			$statement = str_replace($markerCode, $replaceCode, $statement);
			$statement = $this -> fillMarkersInSQLStatement($statement, $arguments, $data, $recordManagementRepository);

			return $statement;
		} else {
			if (strpos($statement, $parameterMarkerStart) !== FALSE) {
				$markerCode = substr($statement, strpos($statement, $parameterMarkerStart), (strpos($statement, $parameterMarkerEnd) + strlen($parameterMarkerEnd)) - strpos($statement, $parameterMarkerStart));
				$parameterMarkerValue = $this -> getMarkerValue($markerCode, $parameterMarkerEnd);

				if ($arguments !== NULL && isset($arguments[$parameterMarkerValue])) {
					$replaceCode = $arguments[$parameterMarkerValue];
				} else {
					$replaceCode = '';
				}

				$newStatement = str_replace($markerCode, $replaceCode, $statement);
				$statement = $this -> fillMarkersInSQLStatement($newStatement, $arguments, $data, $recordManagementRepository);

				return $statement;
			} else {
				if (strpos($statement, $customMarkerStart) !== FALSE) {
					$markerCode = substr($statement, strpos($statement, $customMarkerStart), (strpos($statement, $customMarkerEnd) + strlen($customMarkerEnd)) - strpos($statement, $customMarkerStart));
					$customMarkerValue = $this -> getMarkerValue($markerCode, $customMarkerEnd);

					if (isset($this -> customSQLMarker[$customMarkerValue])) {
						$replaceCode = $this -> customSQLMarker[$customMarkerValue];
					} else {
						$replaceCode = '';
					}

					$newStatement = str_replace($markerCode, $replaceCode, $statement);
					$statement = $this -> fillMarkersInSQLStatement($newStatement, $arguments, $data, $recordManagementRepository);

					return $statement;
				} else {
					return $statement;
				}
			}
		}
	}

	/**
	 * Get marker attributes
	 *
	 * @param string $markerCode Marker code (e.g. "<formvalue type=\"check\">")
	 * @return array $attributes Attributes
	 */
	private function getMarkerAttributes($markerCode, $markerStart) {
		$attributes = array();

		$markerCode = substr($markerCode, strlen($markerStart), strlen($markerCode) - strlen($markerStart));

		while (strpos($markerCode, '="') !== FALSE) {
			$key = trim(substr($markerCode, 0, strpos($markerCode, '="')));
			$markerCode = trim(substr($markerCode, strpos($markerCode, '="') + 2, strlen($markerCode) - (strpos($markerCode, '="') + 2)));
			$value = trim(substr($markerCode, 0, strpos($markerCode, '"')));
			$markerCode = trim(substr($markerCode, strpos($markerCode, '"') + 1, strlen($markerCode) - (strpos($markerCode, '"') + 1)));

			$attributes[$key] = $value;
		}

		return $attributes;
	}

	/**
	 * Get marker value
	 *
	 * @param string $markerCode Marker code (e.g. "<value>The value</value>")
	 * @return string $value The value
	 */
	public function getMarkerValue($markerCode, $markerEnd) {
		$value = substr($markerCode, strpos($markerCode, '>') + 1, (strpos($markerCode, $markerEnd)) - (strpos($markerCode, '>') + 1));
		return trim($value);
	}

	/**
	 * Set record number
	 *
	 * @param int $recordNumber Number of the record
	 * @return void
	 */
	public function setRecordNumber($recordNumber) {
		$this -> recordNumber = $recordNumber;
	}

	/**
	 * Set record count
	 *
	 * @param int $recordCount Number of records
	 * @return void
	 */
	public function setRecordCount($recordCount) {
		$this -> recordCount = $recordCount;
	}

	/**
	 * Set record
	 *
	 * @param Tx_Ezqueries_Domain_Model_Record $record Database record
	 * @return void
	 */
	public function setRecord($record) {
		$this -> record = $record;
	}

}
?>