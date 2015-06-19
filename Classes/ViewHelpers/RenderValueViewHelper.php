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
 * RenderValueViewHelper
 */
class RenderValueViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders the value depending on the column configuration
	 *
	 * @param string $column Name of the column where to insert the value
	 * @param \Frohland\Ezqueries\Domain\Model\Record $record Record
	 * @param array $columnTypes Column types
	 * @param array $search Search arguments
	 * @param \Frohland\Ezqueries\Domain\Repository\RecordManagementRepository $recordManagementRepository RecordManagementRepository
	 * @return string $code
	 */
	public function render($column, $record, $columnTypes, $search = array(), $recordManagementRepository) {
		$valueUtility = $this -> objectManager -> get('Frohland\\Ezqueries\\Utility\\ValueUtility');
		if ($record) {
			$code = $valueUtility -> generateValue($column, $record -> getData(), $columnTypes, $search, $this -> controllerContext -> getUriBuilder(), NULL, $recordManagementRepository);
		}
		return $code;
	}

}
?>