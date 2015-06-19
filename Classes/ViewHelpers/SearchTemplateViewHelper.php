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
 * SearchTemplateViewHelper
 */
class SearchTemplateViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders search template into the view
	 *
	 * @param string $template Template HTML code
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @return string $code
	 */
	public function render($template, $recordManagement) {
		$filters = $recordManagement -> getConditions() -> getFilters();

		$templateUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\TemplateUtility');
		$templateUtility -> initTemplateUtility('search', $recordManagement, $this -> controllerContext -> getUriBuilder());

		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		$arguments = array("filters" => $filters);
		$url = $urlUtility -> createURL("list", $arguments);
		$code = '<form action="' . $url . '" method="post"  class="tx_ezqueries_form tx_ezqueries_search_form">';
		$code .= $templateUtility -> fillMarkers($template);
		$code .= '</form>';

		return $code;
	}

}
?>