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
 * RelationViewHelper
 */
class RelationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders new form elements
	 *
	 * @param \Frohland\Ezqueries\Domain\Model\RecordManagement $recordManagement
	 * @param array $dataSource
	 * @param string $foreignKey
	 * @return string $code
	 */
	public function render($recordManagement, $dataSource, $foreignKey) {
		$columns = $recordManagement -> getTable() -> getSelectedColumns();
		$foreignKeyRelationColumn = $recordManagement -> getTable() -> getForeignKeyRelationColumn();
		$records = $recordManagement -> getRecords();

		// Generate URL
		$arguments = array('foreignKey' => $foreignKey);
		$urlUtility = $this -> objectManager -> create('Frohland\\Ezqueries\\Utility\\URLUtility', $this -> controllerContext -> getUriBuilder());
		$url = $urlUtility -> createURL("assign", $arguments);

		// Generate form
		$code = '<form action="' . $url . '" method="post" name="tx_ezqueries_relation_form" id="tx_ezqueries_relation_form" class="tx_ezqueries_form tx_ezqueries_relation_form">';
		$code .= '<table class="tx_ezqueries_relation">';
		$code .= '<tr><td class="tx_ezqueries_relation_left_column">';
		$code .= '<div class="tx_ezqueries_relation_info_text tx_ezqueries_relation_info_text_selected_elements">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('relation_selected_elements', 'ezqueries') . '</div>';
		$code .= '</td><td class="tx_ezqueries_relation_right_column">';
		$code .= '<div class="tx_ezqueries_relation_info_text tx_ezqueries_relation_info_text_selectable_elements">' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('relation_selectable_elements', 'ezqueries') . '</div>';
		$code .= '</td></tr>';
		$code .= '<tr><td class="tx_ezqueries_relation_left_column">';
		$code .= '<ul class="tx_ezqueries_relation_entries">';
		if ($records !== NULL) {
			foreach ($records as $record) {
				$recordData = $record -> getData();
				$code .= '<li id="' . $recordData[$foreignKeyRelationColumn] . '" class="tx_ezqueries_relation_entry">';
				$code .= '<span class="tx_ezqueries_relation_entry_name">';
				foreach ($columns as $column => $name) {
					$code .= $recordData[$column] . ' ';
				}
				$code .= '</span>';
				$code .= '<span class="tx_ezqueries_relation_delete_entry">X</span>';
				$code .= '</li>';
			}
		}
		$code .= '</ul>';
		$code .= '</td><td class="tx_ezqueries_relation_right_column">';
		$code .= '<div class="tx_ezqueries_select_wrapper tx_ezqueries_relation_select_wrapper">';
		$code .= '<div class="tx_ezqueries_select_filter">';
		$code .= '<input name="regexp" class="tx_ezqueries_select_filter_input" placeholder="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('select_filter_label', 'ezqueries') . '" />';
		$code .= '</div>';
		$code .= '<select name="tx_ezqueries_relation_select" class="tx_ezqueries_relation_select tx_ezqueries_select" size="10">';
		foreach ($dataSource as $sourceRecord) {
			$sourceRecordLength = count($sourceRecord);
			$code .= '<option class="tx_ezqueries_relation_option" value="' . $sourceRecord[$sourceRecordLength - 1] . '">';
			$counter = 0;
			foreach ($sourceRecord as $recordItem) {
				if ($counter < $sourceRecordLength - 1) {
					if ($counter !== 0) {
						$code .= ' ';
					}
					$code .= $recordItem;
				}
				$counter++;
			}
			$code .= '</option>';
		}
		$code .= '</select></div>';

		$code .= '<input class="tx_ezqueries_input_hidden tx_ezqueries_input_relation_data_old" type="hidden" value="';
		if ($records !== NULL) {
			foreach ($records as $record) {
				$recordData = $record -> getData();
				$code .= $recordData[$foreignKeyRelationColumn] . '<->';
			}
		}
		$code .= '" name="tx_ezqueries_ezqueriesplugin[relationDataOld]" />';
		$code .= '<input class="tx_ezqueries_input_hidden tx_ezqueries_input_relation_data_new" type="hidden" value="';
		if ($records !== NULL) {
			foreach ($records as $record) {
				$recordData = $record -> getData();
				$code .= $recordData[$foreignKeyRelationColumn] . '<->';
			}
		}
		$code .= '" name="tx_ezqueries_ezqueriesplugin[relationDataNew]" />';
		$code .= '</td></tr></table>';
		$code .= '<div class="tx_ezqueries_form_row tx_ezqueries_form_row_submit"><input class="tx_ezqueries_submit tx_ezqueries_submit_relation" name="' . $url . '" type="submit" value="' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('relation_form_submit', 'ezqueries') . '" /></div>';

		$code .= '</form>';

		return $code;
	}

}
?>