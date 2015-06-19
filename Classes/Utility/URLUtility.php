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
 * URL-Utility
 *
 * Utility class for URLs.
 */
class URLUtility {

	/**
	 * Uri builder
	 *
	 * @var UriBuilder
	 */
	private $uriBuilder;

	/**
	 * Constructor
	 *
	 * @param UriBuilder $uriBuilder
	 * @return void
	 */
	public function __construct($uriBuilder) {
		$this -> uriBuilder = $uriBuilder;
	}

	/**
	 * Creates an URL for a given controller action
	 *
	 * @param string $action Controller action
	 * @param array $arguments Arguments for the URL (GET parameter)
	 * @param string $pageUid ID of the page the link is redirect to (default: same page as the link)
	 * @return string $uri URL
	 */
	public function createURL($action, $arguments, $pageUid = NULL, $replaceAmpersand = FALSE) {
		$controller = NULL;
		$extensionName = NULL;
		$pluginName = NULL;
		$pageType = 0;
		$noCache = TRUE;
		$noCacheHash = TRUE;
		$section = '';
		$format = '';
		$linkAccessRestrictedPages = FALSE;
		$additionalParams = array();
		$absolute = FALSE;
		$addQueryString = FALSE;
		$argumentsToBeExcludedFromQueryString = array();
		$uriBuilder = $this -> uriBuilder;

		$uri = $uriBuilder -> reset() -> setTargetPageUid($pageUid) -> setTargetPageType($pageType) -> setNoCache($noCache) -> setUseCacheHash(!$noCacheHash) -> setSection($section) -> setFormat($format) -> setLinkAccessRestrictedPages($linkAccessRestrictedPages) -> setArguments($additionalParams) -> setCreateAbsoluteUri($absolute) -> setAddQueryString($addQueryString) -> setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString) -> uriFor($action, $arguments, $controller, $extensionName, $pluginName);
		if ($replaceAmpersand) {
			$replace = '&amp;';
			$uri = preg_replace('/&(?!amp)/', $replace, $uri);
		}
		$uri = base64_encode($uri);
		return $uri;
	}

}
?>