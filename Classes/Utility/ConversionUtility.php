<?php
namespace Frohland\Ezqueries\Utility;

use \DateTime;

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
 * Conversion-Utility
 *
 * Utility class for conversion of date formats and number formats.
 */
class ConversionUtility {

	/**
	 * Converts a number into a defined number format
	 *
	 * @param int $decimals Number of decimal points
	 * @param string $dec_point Separator for the decimal point
	 * @param string $thousands_sep Thousands separator
	 * @param float $number Date to be converted
	 * @return string $convertedNumber Converted number
	 */
	public function convertNumber($decimals, $dec_point, $thousands_sep, $number) {
		if ($number == '') {
			$convertedNumber = '';
			return $convertedNumber;
		}

		$convertedNumber = number_format($number, $decimals, $dec_point, $thousands_sep);
		return $convertedNumber;
	}

	/**
	 * Converts a date into a given date format
	 *
	 * @param string $dateFormat Date format for the conversion
	 * @param string $date Date to be converted
	 * @return string $convertedDate Converted date
	 */
	public function convertDate($dateFormat, $date) {
		if ($date == '' || $date == '0000-00-00' || $date == '0000-00-00 00:00:00' || $date == NULL) {
			$convertedDate = '';
			return $convertedDate;
		}
		if ($dateFormat == NULL) {
			return $date;
		}

		$dateTime = new DateTime($date);
		$convertedDate = $dateTime -> format($dateFormat);
		$convertedDate = $this -> translateDate($convertedDate);
		return $convertedDate;
	}

	/**
	 * Converts php date format into jQuery date format
	 *
	 * @param string $dateFormat Date format
	 * @return string $convertedDateFormat Converted date format
	 */
	public function convertDateFormat($dateFormat) {
		// PHP format
		$phpFormat = array(
		// Day
		'd', // Day of the month, 2 digits with leading zeros
		'j', // Day of the month without leading zeros
		'D', // A textual representation of a day, three letters
		'l', // A full textual representation of the day of the week
		'z', // The day of the year (starting from 0)

		// Month
		'F', // A full textual representation of a month, such as January or March
		'M', // A short textual representation of a month, three letters
		'n', // Numeric representation of a month, without leading zeros
		'm', // Numeric representation of a month, with leading zeros

		// Year
		'Y', // A full numeric representation of a year, 4 digits
		'y'	// A two digit representation of a year
		);

		// jQuery format
		$jQueryFormat = array('dd', 'd', 'D', 'DD', 'o', 'MM', 'M', 'm', 'mm', 'yy', 'y');

		foreach ($phpFormat as &$p) {
			$p = '/' . $p . '/';
		}

		return preg_replace($phpFormat, $jQueryFormat, $dateFormat, 1);
	}

	/**
	 * Translates a date
	 *
	 * @param string $date Date
	 * @return string $translatedDate Translated date
	 */
	public function translateDate($date) {
		// Translation of days and months
		$daysAndMonths = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

		$translation = array(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_monday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_tuesday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_wednesday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_thursday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_friday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_saturday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_sunday', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_mon', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_tue', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_wed', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_thu', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_fri', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_sat', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_sun', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_january', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_february', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_march', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_april', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_may', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_june', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_july', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_august', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_september', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_october', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_november', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_december', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_jan', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_feb', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_mar', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_apr', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_may', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_jun', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_jul', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_aug', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_sep', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_oct', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_nov', 'ezqueries'), \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('date_dec', 'ezqueries'));

		foreach ($daysAndMonths as &$dam) {
			$dam = '/\b' . $dam . '\b/';
		}

		$return = preg_replace($daysAndMonths, $translation, $date, 1);
		return $return;
	}

}
?>