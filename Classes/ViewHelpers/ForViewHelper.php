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
 * ForViewHelper
 */
class ForViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param array $each The array or Tx_Extbase_Persistence_ObjectStorage to iterated over
	 * @param string $as The name of the iteration variable
	 * @param boolean $reverse If enabled, the iterator will start with the last element and proceed reversely
	 * @param string $iteration The name of the variable to store iteration information
	 * @param string $parity
	 * @return string Rendered string
	 */
	public function render($each, $as, $reverse = FALSE, $iteration = NULL, $parity = NULL) {
		$output = '';
		if ($each === NULL) {
			return '';
		}
		if (is_object($each) && !$each instanceof Traversable) {
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('ForViewHelper only supports arrays and objects implementing Traversable interface', 1248728393);
		}

		if ($reverse === TRUE) {
			// array_reverse only supports arrays
			if (is_object($each)) {
				$each = iterator_to_array($each);
			}
			$each = array_reverse($each);
		}
		$iterationData = '';
		$parities = array();
		$i = 0;

		$output = '';
		foreach ($each as $keyValue => $singleElement) {
			$this -> templateVariableContainer -> add($as, $singleElement);
			if ($iteration !== NULL) {
				$iterationData = $i;
				$this -> templateVariableContainer -> add($iteration, $iterationData);
				$i++;

				$even = $i % 2 === 0;
				$parities[isEven] = $even;
				$parities[isOdd] = !$even;
				$this -> templateVariableContainer -> add($parity, $parities);
			}
			$output .= $this -> renderChildren();
			$this -> templateVariableContainer -> remove($as);
			if ($iteration !== NULL) {
				$this -> templateVariableContainer -> remove($iteration);
			}
			if ($parity !== NULL) {
				$this -> templateVariableContainer -> remove($parity);
			}
		}
		return $output;
	}

}
?>