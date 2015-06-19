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
 * PageBrowserViewHelper
 */
class PageBrowserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders a page browser
	 *
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @param UriBuilder $uriBuilder
	 * @return string $code
	 */
	public function render($recordManagement, $uriBuilder = NULL) {
		$code = '';
		$startRecord = $recordManagement -> getConditions() -> getStartRecord();
		$recordsPerPage = $recordManagement -> getConditions() -> getRecordsPerPage();
		$maxPages = $recordManagement -> getConditions() -> getMaxPages();
		$recordsCount = $recordManagement -> getConditions() -> getRecordsCount();
		$orderBy = $recordManagement -> getConditions() -> getOrderBy();
		$order = $recordManagement -> getConditions() -> getOrder();
		$search = $recordManagement -> getConditions() -> getSearch();
		$filters = $recordManagement -> getConditions() -> getFilters();
		$pageCount = 0;
		$actualPage = 1;
		if ($uriBuilder == NULL) {
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		} else {
			$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $uriBuilder);
		}

		// Are there more than one page to create?
		if ($recordsCount >= $recordsPerPage) {
			$i = 1;

			// Get actual Page and number of pages
			for ($start = 0; $start < $recordsCount; $start = ($start + $recordsPerPage)) {
				if ($start == $startRecord) {
					$actualPage = $i - 1;
				}
				$pageCount++;
				$i++;
			}

			if ($maxPages == 0) {
				$maxPages = $pageCount;
			}

			if ($pageCount == 1 || $pageCount == 0) {
				return $code;
			}

			$code .= '<div class="tx_ezqueries_options_pagebrowser"><div class="tx_ezqueries_options_pagebrowser_pages">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_pages', 'ezqueries') . ' </div>';

			// Create page browser elements
			for ($page = 0; $page < $pageCount; $page++) {
				$arguments = array("startRecord" => $page * $recordsPerPage, "orderBy" => $orderBy, "order" => $order, "search" => $search, "filters" => $filters);

				$url = $urlUtility -> createURL("list", $arguments);

				if ($pageCount > $maxPages) {
					// Links to pages
					if ($page == $actualPage) {
						$code .= '<div class="tx_ezqueries_options_pagebrowser_actualpage">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</div>';
					} else {
						if ($page >= ($actualPage - ($maxPages / 2)) && $page <= ($actualPage + ($maxPages / 2))) {
							$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</a></div>';
						} else {
							if ($actualPage - ($maxPages / 2) < 0 && $page <= ($actualPage + ($maxPages / 2)) + (-1 * ($actualPage - ($maxPages / 2)))) {
								$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</a></div>';
							} else {
								if ($actualPage + ($maxPages / 2) > ($pageCount - 1) && $page >= ($actualPage - ($maxPages / 2)) - ($actualPage + ($maxPages / 2) - ($pageCount - 1))) {
									$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</a></div>';
								} else {
									// Link to first page
									if ($page == 0) {
										//$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_first','ezqueries') . '</a></div>';
										$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . '1' . '</a></div>';
										$code .= '<div class="tx_ezqueries_options_pagebrowser_points">...</div>';
									}
									// Link to last page
									if ($page == $pageCount - 1) {
										$code .= '<div class="tx_ezqueries_options_pagebrowser_points">...</div>';
										//$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_last','ezqueries') . '</a></div>';
										$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . $pageCount . '</a></div>';
									}
								}
							}

						}
					}
				} else {
					if ($page == $actualPage) {
						$code .= '<div class="tx_ezqueries_options_pagebrowser_actualpage">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</div>';
					} else {
						$code .= '<div class="tx_ezqueries_options_pagebrowser_page"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . ' ' . ($page + 1) . '</a></div>';
					}
				}
			}
			$arguments = array("startRecord" => 0, "recordsPerPage" => 'all', "orderBy" => $orderBy, "order" => $order, "search" => $search, "filters" => $filters);
			$url = $urlUtility -> createURL("list", $arguments);
			$code .= '<div class="tx_ezqueries_options_pagebrowser_all" style="display: none;"><a class="tx_ezqueries_link tx_ezqueries_link_button tx_ezqueries_link_pagebrowser" href="' . $url . '">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('pagebrowser_page', 'ezqueries') . '</a></div>';
			$code .= '</div>';
		}
		return $code;
	}

}
?>