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
 * LinkViewHelper
 */
class LinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders a link
	 *
	 * @param string $action Controller action
	 * @param array $arguments Arguments for the URL (GET parameter)
	 * @param string $additionalClass Additional class for the link
	 * @param string $type Link type (default, button)
	 * @return string $code
	 */
	public function render($action, $arguments = array(), $additionalClass = '', $type = 'default') {
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		$url = $urlUtility -> createURL($action, $arguments);

		$linkText = '';
		$linkType = '';

		switch($action) {
			case 'list' :
				// Abort from Delete text
				if ($additionalClass != str_replace("tx_ezqueries_link_abort", "", $additionalClass) && $additionalClass != str_replace("tx_ezqueries_link_delete", "", $additionalClass)) {
					if ($additionalClass != str_replace("tx_ezqueries_link_abort_delete", "", $additionalClass)) {
						$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_close_delete', 'ezqueries');
					} else {
						$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_abort', 'ezqueries');
					}
				} else {
					// Close detail view text
					if ($additionalClass != str_replace("tx_ezqueries_link_abort", "", $additionalClass) && $additionalClass != str_replace("tx_ezqueries_link_detail", "", $additionalClass)) {
						$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_close_detail', 'ezqueries');
					} else {
						// Close edit view text
						if ($additionalClass != str_replace("tx_ezqueries_link_abort", "", $additionalClass) && $additionalClass != str_replace("tx_ezqueries_link_edit", "", $additionalClass)) {
							$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_close_edit', 'ezqueries');
						} else {
							// Close new view text
							if ($additionalClass != str_replace("tx_ezqueries_link_abort", "", $additionalClass) && $additionalClass != str_replace("tx_ezqueries_link_new", "", $additionalClass)) {
								$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_close_new', 'ezqueries');
							} else {
								// Close search view text
								if ($additionalClass != str_replace("tx_ezqueries_link_abort", "", $additionalClass) && $additionalClass != str_replace("tx_ezqueries_link_search", "", $additionalClass)) {
									$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_close_search', 'ezqueries');
								} else {
									// To list view text
									$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_list', 'ezqueries');
								}
							}
						}
					}
				}
				break;
			case 'detail' :
				$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_detail', 'ezqueries');
				break;
			case 'edit' :
				$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_edit', 'ezqueries');
				break;
			case 'delete' :
				$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_delete', 'ezqueries');
				break;
			case 'new' :
				$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_new', 'ezqueries');
				break;
			case 'search' :
				$linkText = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('template_link_search', 'ezqueries');
				break;
		}

		switch ($type) {
			case 'button' :
				$linkType = 'tx_ezqueries_link_button';
				break;
			default :
				$linkType = '';
				break;
		}

		$code = '<a class="tx_ezqueries_link ' . $linkType . ' ' . $additionalClass . '" href="' . $url . '">' . $linkText . '</a>';
		return $code;
	}

}
?>