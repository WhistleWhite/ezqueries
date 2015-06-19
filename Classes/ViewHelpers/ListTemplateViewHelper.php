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
 * ListTemplateViewHelper
 */
class ListTemplateViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Database records
	 *
	 * @var array
	 */
	private $records;

	/**
	 * Counter variable
	 *
	 * @var int
	 */
	private $i;

	/**
	 * Renders list template into the view
	 *
	 * @param string $template Template HTML code
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @return string $code
	 */
	public function render($template, $recordManagement) {

		$this -> records = $recordManagement -> getRecords();
		$this -> i = $recordManagement -> getConditions() -> getStartRecord();

		$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
		$templateUtility -> initTemplateUtility('list', $recordManagement, $this -> controllerContext -> getUriBuilder());

		$templateUtility -> setRecordCount(count($this -> records));
		$templateUtility -> setRecordNumber($this -> i);

		$code = $this -> interpretTemplate($template, $templateUtility);

		$this -> i = $recordManagement -> getConditions() -> getStartRecord();
		$templateUtility -> setRecordNumber($this -> i);

		$code = $templateUtility -> fillMarkers($code);
		return $code;
	}

	/**
	 * Search for list markers in the template and replace all markers between them
	 *
	 * @param string $code Template code
	 * @param \Frohland\Ezqueries\Utility\TemplateUtility $templateUtility Template utility
	 * @return string $code
	 */
	private function interpretTemplate($code, $templateUtility) {
		$listMarkerStart = "<record>";
		$listMarkerEnd = "</record>";

		if (strpos($code, $listMarkerStart) !== FALSE) {
			$markerCode = substr($code, strpos($code, $listMarkerStart), (strpos($code, $listMarkerEnd) + strlen($listMarkerEnd)) - strpos($code, $listMarkerStart));
			$listMarkerValue = $templateUtility -> getMarkerValue($markerCode, $listMarkerEnd);

			$newValue = '';

			foreach ($this->records as $record) {
				$templateUtility -> setRecord($record);
				$newValue .= $templateUtility -> fillMarkers($listMarkerValue);
				$this -> i = $this -> i + 1;
				$templateUtility -> setRecordNumber($this -> i);
			}

			$newCode = str_replace($markerCode, $newValue, $code);
			$code = $this -> interpretTemplate($newCode, $templateUtility);
			return $code;
		} else {
			return $code;
		}
	}

}
?>