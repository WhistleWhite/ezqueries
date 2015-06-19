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
 * Language-Utility
 *
 * Utility class for language handling.
 */
class LanguageUtility {

	/**
	 * Translates a value if value begins with "locallang_"
	 *
	 * @param string $originalValue Value
	 * @return string $translatedValue Translated value
	 */
	public function translateValue($originalValue, $locallang_only = FALSE) {
		$value = trim($originalValue);
		if (substr($value, 0, 10) == "locallang_") {
			$langKey = substr($value, 10, strlen($value) - 10);
			$translatedValue = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(trim($langKey), 'ezqueries');
		} else {
			if($locallang_only == FALSE){
				$translatedValue = $this->translate($originalValue);	
			}else{
				$translatedValue = $value;
			}
		}

		if ($translatedValue == '') {
			$translatedValue = $originalValue;
		}

		return $translatedValue;
	}

	/**
	 * Translates a value
	 *
	 * @param string $originalValue Value
	 * @return string $translatedValue Translated value
	 */
	public function translate($originalValue) {
		$value = trim($originalValue);
		$translatedValue = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(\Frohland\Ezqueries\Utility\LanguageUtility::makeLanguageKey($value), 'ezqueries');
		if ($translatedValue == '') {
			$translatedValue = $originalValue;
		}

		return $translatedValue;
	}

	/**
	 * Transforms a value into a language key
	 *
	 * @param string $value Value
	 * @return string $languageKey Language key
	 */
	public function makeLanguageKey($value) {
		$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ë,ï,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,e,i,y,a,e,i,o,u,a,e,i,o,u");
		$value = str_replace($search, $replace, $value);
		$search = array('ä', 'Ä', 'ö', 'Ö', 'ü', 'Ü', 'ß');
		$replace = array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'ss');
		$value = str_replace($search, $replace, $value);
		$languageKey = preg_replace('([^a-z^A-Z])', '_', $value);

		return $languageKey;
	}

}
?>