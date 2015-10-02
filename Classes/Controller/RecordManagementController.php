<?php
namespace Frohland\Ezqueries\Controller;

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
 * Controller for the RecordManagement
 */
class RecordManagementController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository
	 */
	protected $recordManagementRepository;

	/**
	 * @var \TYPO3\CMS\Core\Resource\ResourceFactory
	 */
	protected $resourceFactory;

	/**
	 * @param \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository
	 * @return void
	 */
	public function injectRecordManagementRepository(\Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository) {
		$this -> recordManagementRepository = $recordManagementRepository;
		$this -> resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
	}

	/**
	 * Initializes the current action
	 * @return void
	 */
	protected function initializeAction() {
		// Debug ?
		if (isset($this -> settings['db']['debug'])) {
			$debug = $this -> settings['db']['debug'];
		} else {
			$debug = 'false';
		}

		// Set database connection
		if (isset($this -> settings['db']['server']) && isset($this -> settings['db']['database']) && isset($this -> settings['db']['username']) && isset($this -> settings['db']['password']) && isset($this -> settings['db']['useConnection'])) {
			$this -> recordManagementRepository -> setDatabaseConnection($this -> settings['db']['server'], $this -> settings['db']['database'], $this -> settings['db']['username'], $this -> settings['db']['password'], $this -> settings['db']['useConnection'], $debug);
		} else {
			$this -> recordManagementRepository -> setDatabaseConnection($this -> settings['server'], $this -> settings['database'], $this -> settings['username'], $this -> settings['password'], $this -> settings['useConnection'], $debug);
		}

		// Set header data
		$this -> setHeaderData();

		// Check if site access is js-only
		if ($this -> settings['access'] == 'js' && $_GET['tx_ezqueries_ezqueriesplugin']['action'] !== 'error' && $_GET['cHash'] == '') {
			if ($_GET['type'] == '526' || $_POST['type'] == '526') {
				// Access granted
			} else {
				//$this -> redirect('error');
			}
		}
	}

	/**
	 * Set header data
	 *
	 * @return void
	 */
	private function setHeaderData() {
		// Include CSS & JavaScript files
		$cssTemplateFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/CSS/tx_ezqueries_templates.css';
		$cssMainFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/CSS/tx_ezqueries.css';

		$jQueryFile = $this -> settings['pathToJQuery'];
		$jQueryUIFile = $this -> settings['pathToJQueryUI'];
		$cssJQueryUIFile = $this -> settings['pathToJQueryUICSS'];
		$jQueryFile = str_replace('EXT:ezqueries/', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries"), $jQueryFile);
		$jQueryUIFile = str_replace('EXT:ezqueries/', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries"), $jQueryUIFile);
		$cssJQueryUIFile = str_replace('EXT:ezqueries/', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries"), $cssJQueryUIFile);

		$jQueryUILanguageFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/localization/jquery.ui.datepicker-' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('language', 'ezqueries') . '.js';
		$jQueryFunctionsFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/jquery.functions.js';
		$jQueryUploadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/jquery.fileuploader.min.js';
		$jQueryValidateFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/jquery.validate.min.js';
		$jQueryValidateLocalizationFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/localization/messages_' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('language', 'ezqueries') . '.min.js';
		$jQueryUtilitiesFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/jquery.utilities.min.js';
		$tinyMCEFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath("ezqueries") . 'Resources/Public/JS/tiny_mce/tiny_mce.js';

		if ($this -> settings['css']['cssFile']) {
			$cssTemplateFile = $this -> settings['css']['cssFile'];
		} else {
			if ($this -> settings['cssFile']) {
				$cssTemplateFile = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['cssFile']) -> getPublicUrl();
			}
		}

		$GLOBALS['TSFE'] -> getPageRenderer() -> addCssFile($cssTemplateFile, 'stylesheet', 'all', '', TRUE, TRUE);
		$GLOBALS['TSFE'] -> getPageRenderer() -> addCssFile($cssMainFile, 'stylesheet', 'all', '', TRUE, TRUE);

		if ($cssJQueryUIFile !== '') {
			$GLOBALS['TSFE'] -> getPageRenderer() -> addCssFile($cssJQueryUIFile);
		}

		if ($this -> settings['css']['additionalCssFile']) {
			$cssFiles = explode(',', $this -> settings['css']['additionalCssFile']);
			foreach ($cssFiles as $cssFile) {
				$GLOBALS['TSFE'] -> getPageRenderer() -> addCssFile(trim($cssFile));
			}
		}

		if ($this -> settings['additionalCssFile']) {
			$cssFile = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['additionalCssFile']) -> getPublicUrl();
			$GLOBALS['TSFE'] -> getPageRenderer() -> addCssFile(trim($cssFile));
		}

		if ($this -> settings['uploadFolder']) {
			$uploadFolder = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['uploadFolder']) -> getPublicUrl();
		} else {
			if ($this -> settings['uploadFolderPath']) {
				$uploadFolder = $this -> settings['uploadFolderPath'];
			} else {
				$uploadFolder = 'fileadmin/user_upload/';
			}
		}

		if ($this -> settings['basedir']) {
			$uploadFolderPath = $this -> settings['basedir'] . $uploadFolder;
		} else {
			$uploadFolderPath = $uploadFolder;
		}

		$additionalHeaderData = '
				var labelUploadButton = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_button', 'ezqueries') . '";
				var labelUploadCancel = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_cancel', 'ezqueries') . '";
				var labelUploadFailed = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_failed', 'ezqueries') . '";
				var labelUploadFrom = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_from', 'ezqueries') . '";
				var uploadErrors = new Array();
				uploadErrors["typeError"] = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_typeerror', 'ezqueries') . '";
				uploadErrors["sizeError"] = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_sizeerror', 'ezqueries') . '";
				uploadErrors["minSizeError"] = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_minsizeerror', 'ezqueries') . '";
				uploadErrors["emptyError"] = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_emptyerror', 'ezqueries') . '";
				uploadErrors["onLeave"] = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_upload_onleaveerror', 'ezqueries') . '";
				var uploadFolder = "' . $uploadFolder . '";
				var uploadFolderPath = "' . $uploadFolderPath . '";
				var languageKey = "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('language', 'ezqueries') . '";
		';

		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsInlineCode('', $additionalHeaderData, TRUE, TRUE);

		if ($jQueryFile !== '') {
			$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryFile, 'text/javascript', TRUE, TRUE);
		}
		if ($jQueryUIFile !== '') {
			$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryUIFile);
		}

		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryUILanguageFile);
		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryUploadFile);
		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryValidateFile);
		if (\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('language', 'ezqueries') != 'en') {
			$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryValidateLocalizationFile);
		}
		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($tinyMCEFile, 'text/javascript', FALSE, TRUE, '', TRUE);
		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryUtilitiesFile);
		$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile($jQueryFunctionsFile);

		if ($this -> settings['js']['jsFile']) {
			$jsFiles = explode(',', $this -> settings['js']['jsFile']);
			foreach ($jsFiles as $jsFile) {
				$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile(trim($jsFile));
			}
		}
		if ($this -> settings['jsFile']) {
			$jsFile = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['jsFile']) -> getPublicUrl();
			$GLOBALS['TSFE'] -> getPageRenderer() -> addJsFile(trim($jsFile));
		}
	}

	/**
	 * Displays a list of all records
	 *
	 * @return void
	 */
	public function listAction() {
		// Check permission
		if ($this -> settings['enableList']) {
			// Get $_GET parameters
			$arguments = $this -> request -> getArguments();

			// Set FlashMessage
			if (isset($arguments['flashMessage'])) {
				$flashMessage = $arguments['flashMessage'];
			} else {
				$flashMessage = '';
			}

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'list');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set columns
			$recordManagement -> setColumns($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Set conditions
			$recordManagement -> setConditions($this -> settings, $arguments);

			// Set primary keys of all records
			$recordManagement -> setPrimaryKeys();

			// Set records
			$error = $recordManagement -> setRecords();
			if ($error === 'noError' || $this -> settings[showTemplateIfEmpty]) {
				// Show template
				$this -> view -> assign('showTemplate', 'true');
			} else {
				$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_norecordsfound', 'ezqueries') . ' ' . $error;
			}

			// Use JSON or HTML-template?
			if ($this -> settings['enableJSONOutput']) {
				$records = $recordManagement -> getRecords();
				$data = array();

				$conversionUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ConversionUtility');

				$columnTypes = $recordManagement -> getTable() -> getColumnTypes();

				foreach ($records as $record) {
					$tempData = $record -> getData();
					$convertedRecord = array();
					foreach ($tempData as $column => $value) {
						if ($columnTypes[$column]['type'] == 'date') {
							$convertedRecord[$column] = $conversionUtility -> convertDate($columnTypes[$column]['dateformat'], $value);
						} else {
							$convertedRecord[$column] = htmlentities($value, ENT_QUOTES, 'UTF-8', true);
						}
					}
					$data[] = $convertedRecord;
				}
				return json_encode($data);
			} else {
				// Assign record management to view
				$this -> view -> assign('recordManagement', $recordManagement);

				// Use custom list template?
				if ($this -> settings['useListTemplate']) {
					if ($this -> settings['listTemplateFile'] != '') {
						$template = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['listTemplateFile']) -> getContents();
					} else {
						$template = $this -> settings['listTemplate'];
					}
					$this -> view -> assign('template', $template);
				}
				$this -> view -> assign('useTemplate', $this -> settings['useListTemplate']);

				// Use sortable columns?
				$this -> view -> assign('useSortableColumns', $this -> settings['useSortableColumns']);

				// Use detail, edit or delete option?
				$showDetailOption = $this -> settings['showDetailOption'];
				$this -> view -> assign('showDetailOption', $showDetailOption);
				$showEditOption = $this -> settings['showEditOption'];
				$this -> view -> assign('showEditOption', $showEditOption);
				$showDeleteOption = $this -> settings['showDeleteOption'];
				$this -> view -> assign('showDeleteOption', $showDeleteOption);
				if ($showDetailOption || $showEditOption || $showDeleteOption) {
					$options = TRUE;
				} else {
					$options = FALSE;
				}
				$this -> view -> assign('options', $options);

				// Use new link?
				$this -> view -> assign('showNewLink', $this -> settings['showNewLink']);

				// Use search link?
				$this -> view -> assign('showSearchLink', $this -> settings['showSearchLink']);

				// Page browser position
				switch($this->settings['pageBrowserPosition']) {
					case 'bottom' :
						$this -> view -> assign('bottom', 'true');
						break;
					case 'top' :
						$this -> view -> assign('top', 'true');
						break;
					case 'bottomtop' :
						$this -> view -> assign('bottom', 'true');
						$this -> view -> assign('top', 'true');
						break;
					case 'dontshow' :
						break;
					default :
						$this -> view -> assign('bottom', 'true');
						break;
				}

				// Get URL and assign to view
				$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
				$this -> view -> assign('url', $urlUtility -> createURL('list', $arguments, NULL, FALSE));

				// Assign flash message to view
				$this -> view -> assign('flashMessage', $flashMessage);

				// Assign headline to view
				$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
				$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineList']));

				// Assign additional text to view
				$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopList']));
				$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomList']));
			}
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Displays a single record
	 *
	 * @return void
	 */
	public function detailAction() {
		// Check permission
		if ($this -> settings['enableDetail']) {
			// Get $_GET parameters
			$arguments = $this -> request -> getArguments();

			// Set FlashMessage
			if (isset($arguments['flashMessage'])) {
				$flashMessage = $arguments['flashMessage'];
			} else {
				$flashMessage = '';
			}

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'detail');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set columns
			$recordManagement -> setColumns($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Set conditions
			$recordManagement -> setConditions($this -> settings, $arguments);

			// Set primary keys of all records
			$recordManagement -> setPrimaryKeys($arguments);

			// Set records
			$error = $recordManagement -> setRecords();
			if ($error === 'noError' || $this -> settings[showTemplateIfEmpty]) {
				// Show template
				$this -> view -> assign('showTemplate', 'true');
			} else {
				$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_norecordsfound', 'ezqueries') . ' ' . $error;
			}

			// Assign record management to view
			$this -> view -> assign('recordManagement', $recordManagement);

			// Use record browser?
			$this -> view -> assign('useRecordBrowser', $this -> settings['useRecordBrowserDetail']);

			// Use custom detail template? -> assign template to view
			if ($this -> settings[useDetailTemplate]) {
				if ($this -> settings['detailTemplateFile']) {
					$template = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['detailTemplateFile']) -> getContents();
				} else {
					$template = $this -> settings['detailTemplate'];
				}
				$this -> view -> assign('template', $template);
			}
			$this -> view -> assign('useTemplate', $this -> settings['useDetailTemplate']);

			// Show close link?
			$this -> view -> assign('showCloseLink', $this -> settings['showCloseLinkDetail']);

			// Get URL and assign to view
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
			$this -> view -> assign('url', $urlUtility -> createURL('detail', $arguments, NULL, FALSE));

			// Assign flash message to view
			$this -> view -> assign('flashMessage', $flashMessage);

			// Assign headline to view
			$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
			$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineDetail']));

			// Assign additional text to view
			$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopDetail']));
			$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomDetail']));
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Displays a form for editing a record
	 *
	 * @return void
	 */
	public function editAction() {
		// Check permission
		if ($this -> settings['enableEdit']) {
			// Get $_GET parameters
			$arguments = $this -> request -> getArguments();

			// Set FlashMessage
			if (isset($arguments['flashMessage'])) {
				$flashMessage = $arguments['flashMessage'];
			} else {
				$flashMessage = '';
			}

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'edit');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set columns
			$recordManagement -> setColumns($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Set conditions
			$recordManagement -> setConditions($this -> settings, $arguments);

			// Set primary keys of all records
			$recordManagement -> setPrimaryKeys($arguments);

			// Set records
			$error = $recordManagement -> setRecords();
			if ($error === 'noError' || $this -> settings[showTemplateIfEmpty]) {
				// Show template
				$this -> view -> assign('showTemplate', 'true');
			} else {
				$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_norecordsfound', 'ezqueries') . ' ' . $error;
			}

			// Assign record management to view
			$this -> view -> assign('recordManagement', $recordManagement);

			// Redirect after form submit?
			if ($this -> settings['redirectEditPage'] !== '' && $this -> settings['redirectEditPage'] !== NULL) {
				$this -> view -> assign('redirectAfterSubmit', 'true');
			}

			// Use record browser?
			$this -> view -> assign('useRecordBrowser', $this -> settings['useRecordBrowserEdit']);

			// Use custom edit template? -> assign template to view
			if ($this -> settings['useEditTemplate']) {
				if ($this -> settings['editTemplateFile']) {
					$template = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['editTemplateFile']) -> getContents();
				} else {
					$template = $this -> settings['editTemplate'];
				}
				$this -> view -> assign('template', $template);
			}
			$useTemplate = $this -> settings['useEditTemplate'];
			$this -> view -> assign('useTemplate', $useTemplate);

			// Show close link?
			$this -> view -> assign('showCloseLink', $this -> settings['showCloseLinkEdit']);

			// Get URL and assign to view
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
			$this -> view -> assign('url', $urlUtility -> createURL('edit', $arguments, NULL, FALSE));

			// Check success
			$status = $arguments['status'];
			if ($status == 'success') {
				$successful = TRUE;
			} else {
				$successful = FALSE;
			}
			$this -> view -> assign('successful', $successful);

			// Assign flash message to view
			$this -> view -> assign('flashMessage', $flashMessage);

			// Assign headline to view
			$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
			$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineEdit']));

			// Assign additional text to view
			$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopEdit']));
			$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomEdit']));
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Updates an existing record
	 *
	 * @return void
	 */
	public function updateAction() {
		// Check permission
		if ($this -> settings['enableEdit']) {
			// Get $_POST parameters
			$arguments = $this -> request -> getArguments();

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'edit');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Get data from $_POST ($arguments)
			$data = array();
			$columns = $recordManagement -> getTable() -> getSelectedColumns();
			$columnTypes = $recordManagement -> getTable() -> getColumnTypes();
			$tables = $recordManagement -> getTable() -> getTableNames();

			foreach ($columns as $column) {
				if ($columnTypes[$column['name']]['type'] != 'numeric' && $columnTypes[$column['name']]['type'] != 'int' && $columnTypes[$column['name']]['type'] != 'boolean') {
					if (isset($arguments[$column['name']])) {
						$data[$column['name']]['value'] = $arguments[$column['name']];
						$data[$column['name']]['type'] = 'text';
					}
				} else {
					if (isset($arguments[$column['name']])) {
						if ($arguments[$column['name']] == '') {
							if ($columnTypes[$column['name']]['not_null']) {
								$data[$column['name']]['value'] = 0;
							} else {
								$data[$column['name']]['value'] = 'NULL';
							}
						} else {
							$data[$column['name']]['value'] = $arguments[$column['name']];
						}
						$data[$column['name']]['type'] = 'numeric';
					}
				}
			}

			// Search arguments
			if (isset($arguments['search'])) {
				$search = $arguments['search'];
			} else {
				$search = array();
			}

			// Filter arguments
			if (isset($arguments['filters'])) {
				$filters = $arguments['filters'];
			} else {
				$filters = array();
			}

			// Get primary keys of the record
			$primaryKeys = $arguments['primaryKeys'];

			// Include hook to validate data before updating the record (return TRUE if valid; or return "any error message" if not valid)
			$isValid = TRUE;
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeUpdate'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeUpdate'] as $_classRef) {
					$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
					$isValid = $_procObj -> hookBeforeUpdate($data, $columns, $columnTypes, $tables[0]);
				}
			}

			if ($isValid === TRUE) {
				// Update the record
				$status = $this -> recordManagementRepository -> updateRecord($tables[0], $primaryKeys, $data);
			} else {
				$status = $isValid;
			}

			// Include hook to do something after updating the record
			if ($status == 'success') {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterUpdate'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterUpdate'] as $_classRef) {
						$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
						$_procObj -> hookAfterUpdate($data, $primaryKeys, $columns, $columnTypes, $tables[0], $arguments, $filters);
					}
				}
			}

			// Post-processing SQL
			if ($status == 'success' && isset($this -> settings['postProcessingEditSQL'])) {
				$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
				$postProcessingSQLStatements = explode(';', $this -> settings['postProcessingEditSQL']);
				$postProcessingSQLStatusTotal = '';

				foreach ($postProcessingSQLStatements as $postProcessingSQLStatement) {
					if ($postProcessingSQLStatement !== '') {
						$parsedPostProcessingSQLStatement = $templateUtility -> fillMarkersInSQLStatement(trim($postProcessingSQLStatement), $arguments, $data);
						$postProcessingSQLStatus = $this -> recordManagementRepository -> executeSQLStatement($parsedPostProcessingSQLStatement);

						if ($postProcessingSQLStatus !== 'success') {
							$postProcessingSQLStatusTotal .= '<br />Post-processing error: ' . $postProcessingSQLStatus;
						}
					}

				}
			}

			// Show update status
			switch($status) {
				case 'success' :
					$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_update_successful', 'ezqueries') . $postProcessingSQLStatusTotal;
					break;
				//case 'unchanged': $flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_update_no_changes','ezqueries'); break;
				default :
					$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_error', 'ezqueries') . ' "' . $status . '"';
					$status = 'error';
					break;
			}

			// Redirect
			if ($status == 'success') {
				if ($this -> settings['redirectEdit'] == 'edit' || $this -> settings['redirectEdit'] == 'detail') {
					$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($tables);
					foreach ($primaryKeyColumns as $column => $value) {
						if (isset($arguments[$column])) {
							$primaryKeys[$column] = $arguments[$column];
						}
					}
				}
				if ($this -> settings['redirectEditPage'] !== '' && $this -> settings['redirectEditPage'] !== NULL) {
					$page = $this -> settings['redirectEditPage'];
					if ($this -> settings['redirectEdit'] == 'list') {
						$this -> redirect('list', NULL, NULL, array(), $page);
					}
					if ($this -> settings['redirectEdit'] == 'edit') {
						$this -> redirect('edit', NULL, NULL, array('primaryKeys' => $primaryKeys), $page);
					}
					if ($this -> settings['redirectEdit'] == 'detail') {
						$this -> redirect('detail', NULL, NULL, array('primaryKeys' => $primaryKeys), $page);
					}
					if ($this -> settings['redirectEdit'] == 'new') {
						$this -> redirect('new', NULL, NULL, array(), $page);
					}
				} else {
					if ($this -> settings['redirectEdit'] == 'list') {
						$this -> redirect('list', NULL, NULL, array('search' => $search, 'filters' => $filters));
					}
					if ($this -> settings['redirectEdit'] == 'edit') {
						$this -> redirect('edit', NULL, NULL, array('primaryKeys' => $primaryKeys, 'search' => $search, 'filters' => $filters, 'status' => $status, 'flashMessage' => $flashMessage));
					}
					if ($this -> settings['redirectEdit'] == 'detail') {
						$this -> redirect('detail', NULL, NULL, array('primaryKeys' => $primaryKeys, 'search' => $search, 'filters' => $filters));
					}
					if ($this -> settings['redirectEdit'] == 'new') {
						$this -> redirect('new', NULL, NULL, array('search' => $search, 'filters' => $filters));
					}
				}
			} else {
				$this -> redirect('edit', NULL, NULL, array('primaryKeys' => $primaryKeys, 'search' => $search, 'filters' => $filters, 'status' => $status, 'flashMessage' => $flashMessage));
			}
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Displays a form for creating a new record
	 *
	 * @return void
	 */
	public function newAction() {
		// Check permission
		if ($this -> settings['enableNew']) {
			// Get $_GET parameters
			$arguments = $this -> request -> getArguments();

			// Set FlashMessage
			if (isset($arguments['flashMessage'])) {
				$flashMessage = $arguments['flashMessage'];
			} else {
				$flashMessage = '';
			}

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'new');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Set conditions
			$recordManagement -> setConditions($this -> settings, $arguments);

			// Assign record management to view
			$this -> view -> assign('recordManagement', $recordManagement);

			// Use custom new template? -> assign template to view
			if ($this -> settings['useNewTemplate']) {
				if ($this -> settings['newTemplateFile']) {
					$template = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['newTemplateFile']) -> getContents();
				} else {
					$template = $this -> settings['newTemplate'];
				}
				$this -> view -> assign('template', $template);
			}
			$this -> view -> assign('useTemplate', $this -> settings['useNewTemplate']);

			// Redirect after form submit?
			if ($this -> settings['redirectNewPage'] !== '' && $this -> settings['redirectNewPage'] !== NULL) {
				$this -> view -> assign('redirectAfterSubmit', 'true');
			}

			// Show close link?
			$this -> view -> assign('showCloseLink', $this -> settings['showCloseLinkNew']);

			// Get URL and assign to view
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
			$this -> view -> assign('url', $urlUtility -> createURL('new', $arguments, NULL, FALSE));

			// Check success
			$status = $arguments['status'];
			if ($status == 'success') {
				$successful = TRUE;
			} else {
				$successful = FALSE;
			}
			$this -> view -> assign('successful', $successful);

			// Assign flash message to view
			$this -> view -> assign('flashMessage', $flashMessage);

			// Assign headline to view
			$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
			$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineNew']));

			// Assign additional text to view
			$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopNew']));
			$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomNew']));
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Creates a new record
	 *
	 * @return void
	 */
	public function createAction() {
		// Check permission
		if ($this -> settings['enableNew']) {
			// Get $_POST parameters
			$arguments = $this -> request -> getArguments();

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'new');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Get data from $_POST ($arguments)
			$data = array();
			$columns = $recordManagement -> getTable() -> getSelectedColumns();
			$columnTypes = $recordManagement -> getTable() -> getColumnTypes();
			$tables = $recordManagement -> getTable() -> getTableNames();

			foreach ($columns as $column) {
				if (isset($columnTypes[$column['name']]['defaultValueQuery'])) {
					$defaultValue = $this -> recordManagementRepository -> getRecordsBySQLQuery($columnTypes[$column['name']]['defaultValueQuery']);
					if ($defaultValue !== FALSE) {
						$defaultValue = $defaultValue[0][0];
					} else {
						$defaultValue = '';
					}
					if ($defaultValue == '' || $defaultValue == NULL) {
						$defaultValue = 1;
					}
					$data[$column['name']]['value'] = $defaultValue;
					$data[$column['name']]['type'] = 'numeric';
				} else {
					if ($columnTypes[$column['name']]['type'] != 'numeric' && $columnTypes[$column['name']]['type'] != 'int' && $columnTypes[$column['name']]['type'] != 'boolean') {
						if (isset($arguments[$column['name']])) {
							$data[$column['name']]['value'] = $arguments[$column['name']];
							$data[$column['name']]['type'] = 'text';
						} else {
							if ($columnTypes[$column['name']]['not_null']) {
								$data[$column['name']]['value'] = '';
								$data[$column['name']]['type'] = 'text';
							}
						}
					} else {
						if (isset($arguments[$column['name']])) {
							if ($arguments[$column['name']] == '') {
								if ($columnTypes[$column['name']]['not_null']) {
									$data[$column['name']]['value'] = 0;
								} else {
									$data[$column['name']]['value'] = 'NULL';
								}
							} else {
								$data[$column['name']]['value'] = $arguments[$column['name']];
							}
							$data[$column['name']]['type'] = 'numeric';
						} else {
							if ($columnTypes[$column['name']]['not_null']) {
								$data[$column['name']]['value'] = 0;
								$data[$column['name']]['type'] = 'numeric';
							}
						}
					}
				}

			}

			// Search arguments
			if ($arguments['search']) {
				$search = $arguments['search'];
			} else {
				$search = array();
			}

			// Filter arguments
			if (isset($arguments['filters'])) {
				$filters = $arguments['filters'];
			} else {
				$filters = array();
			}

			// Include hook to validate data before creating the new record (return TRUE if valid; or return "any error message" if not valid)
			$isValid = TRUE;
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeCreate'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeCreate'] as $_classRef) {
					$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
					$isValid = $_procObj -> hookBeforeCreate($data, $columns, $columnTypes, $tables[0]);
				}
			}

			if ($isValid === TRUE) {
				// Create the record
				$returnValues = $this -> recordManagementRepository -> createRecord($tables[0], $data);
				$status = $returnValues['status'];
			} else {
				$status = $isValid;
			}

			// Include hook to do something after creating the record
			if ($status == 'success') {
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterCreate'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterCreate'] as $_classRef) {
						$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
						$_procObj -> hookAfterCreate($data, $returnValues['insertID'], $columns, $columnTypes, $tables[0]);
					}
				}
			}

			// Post-processing SQL
			if ($status == 'success' && isset($this -> settings['postProcessingNewSQL'])) {
				$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
				$postProcessingSQLStatements = explode(';', $this -> settings['postProcessingNewSQL']);
				$postProcessingSQLStatusTotal = '';
				$data['ezqueriesAIValue']['type'] = 'numeric';
				$data['ezqueriesAIValue']['value'] = $returnValues['insertID'];

				foreach ($postProcessingSQLStatements as $postProcessingSQLStatement) {
					if ($postProcessingSQLStatement !== '') {
						$parsedPostProcessingSQLStatement = $templateUtility -> fillMarkersInSQLStatement(trim($postProcessingSQLStatement), $arguments, $data, $this -> recordManagementRepository);
						$postProcessingSQLStatus = $this -> recordManagementRepository -> executeSQLStatement($parsedPostProcessingSQLStatement);

						if ($postProcessingSQLStatus !== 'success') {
							$postProcessingSQLStatusTotal .= '<br />Post-processing error: ' . $postProcessingSQLStatus;
						}
					}

				}
			}

			// Show create status
			switch($status) {
				case 'success' :
					$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_create_successful', 'ezqueries') . $postProcessingSQLStatusTotal;
					break;
				default :
					$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_error', 'ezqueries') . ' "' . $status . '"';
					$status = 'error';
					break;
			}

			// Redirect
			if ($status == 'success') {
				if ($this -> settings['redirectNew'] == 'edit' || $this -> settings['redirectNew'] == 'detail') {
					$primaryKeyColumns = $this -> recordManagementRepository -> getPrimaryKeyColumns($tables);
					foreach ($primaryKeyColumns as $column => $value) {
						if (isset($arguments[$column])) {
							$primaryKeys[$column] = $arguments[$column];
						} else {
							$primaryKeys[$column] = $returnValues['insertID'];
						}
					}
				}
				if ($this -> settings['redirectNewPage'] !== '' && $this -> settings['redirectNewPage'] !== NULL) {
					$page = $this -> settings['redirectNewPage'];
					if ($this -> settings['redirectNew'] == 'list') {
						$this -> redirect('list', NULL, NULL, array(), $page);
					}
					if ($this -> settings['redirectNew'] == 'edit') {
						$this -> redirect('edit', NULL, NULL, array('primaryKeys' => $primaryKeys), $page);
					}
					if ($this -> settings['redirectNew'] == 'detail') {
						$this -> redirect('detail', NULL, NULL, array('primaryKeys' => $primaryKeys), $page);
					}
					if ($this -> settings['redirectNew'] == 'new') {
						$this -> redirect('new', NULL, NULL, array(), $page);
					}
				} else {
					if ($this -> settings['redirectNew'] == 'new') {
						$this -> redirect('new', NULL, NULL, array('search' => $search, 'filters' => $filters, 'status' => $status, 'flashMessage' => $flashMessage));
					}
					if ($this -> settings['redirectNew'] == 'list') {
						$this -> redirect('list', NULL, NULL, array('search' => $search, 'filters' => $filters));
					}
					if ($this -> settings['redirectNew'] == 'edit') {
						$this -> redirect('edit', NULL, NULL, array('primaryKeys' => $primaryKeys, 'search' => $search, 'filters' => $filters));
					}
					if ($this -> settings['redirectNew'] == 'detail') {
						$this -> redirect('detail', NULL, NULL, array('primaryKeys' => $primaryKeys, 'search' => $search, 'filters' => $filters));
					}
				}
			} else {
				$this -> redirect('new', NULL, NULL, array('search' => $search, 'filters' => $filters, 'status' => $status, 'flashMessage' => $flashMessage));
			}
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Deletes an existing record
	 *
	 * @return void
	 */
	public function deleteAction() {
		// Check permission
		if ($this -> settings['enableDeleting']) {
			// Get $_POST parameters
			$arguments = $this -> request -> getArguments();

			$confirmed = FALSE;

			// Assign headline to view
			$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
			$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineDeleting']));

			// Check if delete is confirmed
			if (isset($arguments['confirmed'])) {
				$confirmed = TRUE;

				// Set Table names
				$tables = explode(',', $this -> settings['tables']);

				// Get primary key(s) of the record
				if (isset($arguments['primaryKeys'])) {
					$primaryKeys = $arguments['primaryKeys'];
				} else {
					$primaryKeys = NULL;
				}

				// Data for post-processing SQL
				$data = array();
				if (isset($primaryKeys)) {
					foreach ($primaryKeys as $column => $value) {
						$data[$column]['value'] = $value;
					}
				}

				$deleteRecord = TRUE;

				// Include hook to do something before record deletion
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeDelete'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookBeforeDelete'] as $_classRef) {
						$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
						$deleteRecord = $_procObj -> hookBeforeDelete($data, $primaryKeys, $tables[0]);
					}
				}

				if ($deleteRecord === TRUE) {
					// Delete the record
					$status = $this -> recordManagementRepository -> deleteRecord($tables[0], $primaryKeys);
				} else {
					$status = $deleteRecord;
				}

				// Include hook to do something after record deletion
				if ($status == 'success') {
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterDelete'])) {
						foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ezqueries']['recordManagementController']['hookAfterDelete'] as $_classRef) {
							$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
							$_procObj -> hookAfterDelete($data, $primaryKeys, $tables[0]);
						}
					}
				}

				// Post-processing SQL
				if ($status == 'success' && isset($this -> settings['postProcessingDeletingSQL'])) {
					$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
					$postProcessingSQLStatements = explode(';', $this -> settings['postProcessingDeletingSQL']);
					$postProcessingSQLStatusTotal = '';

					foreach ($postProcessingSQLStatements as $postProcessingSQLStatement) {
						if ($postProcessingSQLStatement !== '') {
							$parsedPostProcessingSQLStatement = $templateUtility -> fillMarkersInSQLStatement(trim($postProcessingSQLStatement), $arguments, $data);
							$postProcessingSQLStatus = $this -> recordManagementRepository -> executeSQLStatement($parsedPostProcessingSQLStatement);

							if ($postProcessingSQLStatus !== 'success') {
								$postProcessingSQLStatusTotal .= '<br />Post-processing error: ' . $postProcessingSQLStatus;
							}
						}
					}
				}

				// Show create status
				switch($status) {
					case 'success' :
						$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_delete_successful', 'ezqueries') . $postProcessingSQLStatusTotal;
						break;
					default :
						$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_error', 'ezqueries') . ' "' . $status . '"';
						$status = 'error';
						// Redirect to delete view
						$this -> redirect('delete', NULL, NULL, array('status' => $status, 'flashMessage' => $flashMessage));
						break;
				}
			} else {
				// Set FlashMessage
				if (isset($arguments['flashMessage'])) {
					$flashMessage = $arguments['flashMessage'];
				} else {
					$flashMessage = '';
				}

				// Set Status
				if (isset($arguments['status'])) {
					if ($arguments['status'] == 'error') {
						$this -> view -> assign('error', 'true');
					}
				}

				// Assign search arguments to view
				if (isset($arguments['search'])) {
					$search = $arguments['search'];
				} else {
					$search = array();
				}
				$this -> view -> assign('search', $search);

				// Assign filter arguments to view
				if (isset($arguments['filters'])) {
					$filters = $arguments['filters'];
				} else {
					$filters = array();
				}
				$this -> view -> assign('filters', $filters);

				// Get primary key(s) of the record and assign to view
				if (isset($arguments['primaryKeys'])) {
					$primaryKeys = $arguments['primaryKeys'];
				} else {
					$primaryKeys = NULL;
				}
				$this -> view -> assign('primaryKeys', $primaryKeys);
			}

			// Is confirmed
			$this -> view -> assign('confirmed', $confirmed);

			// Assign flash message to view
			$this -> view -> assign('flashMessage', $flashMessage);
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Show search form
	 *
	 * @return void
	 */
	public function searchAction() {
		// Get $_GET parameters
		$arguments = $this -> request -> getArguments();

		// Create record management object
		$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'search');

		// Set Table names
		$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

		// Set column types
		$recordManagement -> setColumnTypes($this -> settings);

		// Set selected columns
		$fullTextSearch = $recordManagement -> setSelectedColumns($this -> settings, $arguments);
		$this -> view -> assign('fullTextSearch', $fullTextSearch);

		// Set filter arguments
		if (isset($arguments['filters'])) {
			$filters = $arguments['filters'];
		} else {
			$filters = array();
		}
		$recordManagement -> getConditions() -> setFilters($filters);

		// Assign record management to view
		$this -> view -> assign('recordManagement', $recordManagement);

		// Show close link?
		$this -> view -> assign('showCloseLink', $this -> settings['showCloseLinkSearch']);

		// Use custom search template? -> assign template to view
		if ($this -> settings['useSearchTemplate']) {
			if ($this -> settings['searchTemplateFile']) {
				$template = $this -> resourceFactory -> retrieveFileOrFolderObject($this -> settings['searchTemplateFile']) -> getContents();
			} else {
				$template = $this -> settings['searchTemplate'];
			}
			$this -> view -> assign('template', $template);
		}
		$this -> view -> assign('useTemplate', $this -> settings['useSearchTemplate']);

		// Assign headline to view
		$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
		$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineSearch']));

		// Assign additional text to view
		$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopSearch']));
		$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomSearch']));
	}

	/**
	 * Show from for relations
	 *
	 * @return void
	 */
	public function relationAction() {
		// Check permission
		if ($this -> settings['enableRelation']) {
			// Get $_GET parameters
			$arguments = $this -> request -> getArguments();

			// Set FlashMessage
			if (isset($arguments['flashMessage'])) {
				$flashMessage = $arguments['flashMessage'];
			} else {
				$flashMessage = '';
			}

			// Create record management object
			$recordManagement = $this -> objectManager -> create('Frohland\\Ezqueries\\Domain\\Model\\RecordManagement', $this -> recordManagementRepository, 'relation');

			// Set Table names
			$recordManagement -> setTableNames(explode(',', $this -> settings['tables']));

			// Set column types
			$recordManagement -> setColumnTypes($this -> settings);

			// Set selected columns
			$recordManagement -> setSelectedColumns($this -> settings, $arguments);

			// Set conditions
			$recordManagement -> setConditions($this -> settings, $arguments);

			// Set columns
			$recordManagement -> setColumns($this -> settings);

			// Set records
			$error = $recordManagement -> setRecords();
			if ($error === 'noError') {
				// Show records
				$this -> view -> assign('showRecords', 'true');
			} else {
				//$flashMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_norecordsfound','ezqueries') . ' ' . $error;
			}

			// Get data source for relation
			$dataSource = $this -> recordManagementRepository -> getRecordsBySQLQuery($this -> settings['relationDataSource']);
			$this -> view -> assign('dataSource', $dataSource);

			// Get foreign key and assign to view
			$foreignKey = $arguments['foreignKey'];
			$this -> view -> assign('foreignKey', $foreignKey);

			// Assign record management to view
			$this -> view -> assign('recordManagement', $recordManagement);

			// Get URL and assign to view
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
			$this -> view -> assign('url', $urlUtility -> createURL('relation', $arguments, NULL, FALSE));

			// Assign flash message to view
			$this -> view -> assign('flashMessage', $flashMessage);

			// Assign headline to view
			$languageUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\LanguageUtility');
			$this -> view -> assign('headline', $languageUtility -> translateValue($this -> settings['headlineRelation']));

			// Assign additional text to view
			$this -> view -> assign('textTop', $languageUtility -> translateValue($this -> settings['additionalTextTopRelation']));
			$this -> view -> assign('textBottom', $languageUtility -> translateValue($this -> settings['additionalTextBottomRelation']));
		} else {
			$this -> redirect('error');
		}
	}

	/**
	 * Assign relations
	 *
	 * @return void
	 */
	public function assignAction() {
		// Check permission
		if ($this -> settings['enableRelation']) {
			// Get $_GET and $_POST parameters
			$arguments = $this -> request -> getArguments();

			$flashMessage = '';

			// Set Table names
			$tables = explode(',', $this -> settings['tables']);

			// Get column types
			$columnTypes = $this -> recordManagementRepository -> getColumnTypes($tables);

			// Set foreign key / foreign key columns
			$foreignKeyColumn = $this -> settings['relationForeignKeyColumn'];
			$foreignKeyRelationColumn = $this -> settings['relationForeignKeyRelationColumn'];
			$foreignKey = $arguments['foreignKey'];

			// Get relation data
			$relationDataOld = explode('<->', $arguments['relationDataOld']);
			array_pop($relationDataOld);
			$relationDataNew = explode('<->', $arguments['relationDataNew']);
			array_pop($relationDataNew);

			if ($this -> settings['relationType'] == 'manytomany') {
				// Delete removed relations
				foreach ($relationDataOld as $idOld) {
					$deleteRelation = true;
					$status = 'success';

					foreach ($relationDataNew as $idNew) {
						if ($idOld == $idNew) {
							$deleteRelation = false;
							break;
						}
					}
					if ($deleteRelation) {
						$deleteData = array();
						$deleteData[$foreignKeyColumn] = $foreignKey;
						$deleteData[$foreignKeyRelationColumn] = $idOld;
						$status = $this -> recordManagementRepository -> deleteRecord($tables[0], $deleteData);
					}
					// Status
					switch($status) {
						case 'success' :
							break;
						default :
							$flashMessage .= ' "' . $status . '"';
							break;
					}
				}

				// Create added relations
				foreach ($relationDataNew as $idNew) {
					$createRelation = true;
					$status = 'success';

					foreach ($relationDataOld as $idOld) {
						if ($idNew == $idOld) {
							$createRelation = false;
							break;
						}
					}
					if ($createRelation) {
						$createData = array();
						$createData[$foreignKeyColumn]['value'] = $foreignKey;
						if ($columnTypes[$foreignKeyColumn]['type'] != 'numeric' && $columnTypes[$foreignKeyColumn]['type'] != 'int' && $columnTypes[$foreignKeyColumn]['type'] != 'boolean') {
							$createData[$foreignKeyColumn]['type'] = 'text';
						} else {
							$createData[$foreignKeyColumn]['type'] = 'numeric';
						}
						$createData[$foreignKeyRelationColumn]['value'] = $idNew;
						if ($columnTypes[$foreignKeyRelationColumn]['type'] != 'numeric' && $columnTypes[$foreignKeyRelationColumn]['type'] != 'int' && $columnTypes[$foreignKeyRelationColumn]['type'] != 'boolean') {
							$createData[$foreignKeyRelationColumn]['type'] = 'text';
						} else {
							$createData[$foreignKeyRelationColumn]['type'] = 'numeric';
						}
						$returnValues = $this -> recordManagementRepository -> createRecord($tables[0], $createData);
						$status = $returnValues['status'];
					}

					// Status
					switch($status) {
						case 'success' :
							break;
						default :
							$flashMessage .= ' "' . $status . '"';
							break;
					}
				}
			} else {
				// Update removed relations
				foreach ($relationDataOld as $idOld) {
					$deleteRelation = true;
					$status = 'success';

					foreach ($relationDataNew as $idNew) {
						if ($idOld == $idNew) {
							$deleteRelation = false;
							break;
						}
					}
					if ($deleteRelation) {
						$recordIdentifier = array();
						$recordIdentifier[$foreignKeyColumn] = $foreignKey;
						$recordIdentifier[$foreignKeyRelationColumn] = $idOld;
						$updateData = array();
						if ($columnTypes[$foreignKeyColumn]['type'] != 'numeric' && $columnTypes[$foreignKeyColumn]['type'] != 'int' && $columnTypes[$foreignKeyColumn]['type'] != 'boolean') {
							$updateData[$foreignKeyColumn]['value'] = '';
							$updateData[$foreignKeyColumn]['type'] = 'text';
						} else {
							if ($columnTypes[$foreignKeyColumn]['not_null']) {
								$updateData[$foreignKeyColumn]['value'] = 0;
							} else {
								$updateData[$foreignKeyColumn]['value'] = NULL;
							}
							$updateData[$foreignKeyColumn]['type'] = 'numeric';
						}
						$status = $this -> recordManagementRepository -> updateRecord($tables[0], $recordIdentifier, $updateData);
					}
					// Status
					switch($status) {
						case 'success' :
							break;
						default :
							$flashMessage .= ' "' . $status . '"';
							break;
					}
				}

				// Update added relations
				foreach ($relationDataNew as $idNew) {
					$createRelation = true;
					$status = 'success';

					foreach ($relationDataOld as $idOld) {
						if ($idNew == $idOld) {
							$createRelation = false;
							break;
						}
					}
					if ($createRelation) {
						$recordIdentifier = array();
						$recordIdentifier[$foreignKeyRelationColumn] = $idNew;
						$updateData = array();
						$updateData[$foreignKeyColumn]['value'] = $foreignKey;
						if ($columnTypes[$foreignKeyColumn]['type'] != 'numeric' && $columnTypes[$foreignKeyColumn]['type'] != 'int' && $columnTypes[$foreignKeyColumn]['type'] != 'boolean') {
							$updateData[$foreignKeyColumn]['type'] = 'text';
						} else {
							$updateData[$foreignKeyColumn]['type'] = 'numeric';
						}
						$status = $this -> recordManagementRepository -> updateRecord($tables[0], $recordIdentifier, $updateData);
					}

					// Status
					switch($status) {
						case 'success' :
							break;
						default :
							$flashMessage .= ' "' . $status . '"';
							break;
					}
				}
			}

			$this -> redirect('relation', NULL, NULL, array('foreignKey' => $foreignKey, 'flashMessage' => $flashMessage));
		}
	}

	/**
	 * Empty Action
	 *
	 * @return void
	 */
	public function emptyAction() {
		// Do nothing
	}

	/**
	 * Error Action
	 *
	 * @return void
	 */
	public function errorAction() {
		// Do nothing
	}

}
?>