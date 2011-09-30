<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 TEQneers GmbH & Co. KG <info@teqneers.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */
require_once(PATH_t3lib.'class.t3lib_pagetree.php');

/**
 * TYPO3 Backend ajax module page
 *
 * @author		TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package		TYPO3
 * @subpackage	tx_seo
 */
class tx_tqseo_backend_ajax_page extends tx_tqseo_backend_ajax_base {

	/**
	 * Return overview entry list for root tree
	 *
	 * @return	array
	 */
	protected function _executeGetList() {
		global $TYPO3_DB, $BE_USER;

		// Init
		$list = array();

		$pid			= (int)$this->_postVar['pid'];
		$offset			= (int)$this->_postVar['start'];
		$limit			= (int)$this->_postVar['limit'];
		$itemsPerPage	= (int)$this->_postVar['pagingSize'];
		$depth			= (int)$this->_postVar['depth'];
		$listType		= (string)$this->_postVar['listType'];

		if( !empty($pid) ) {
			$page = t3lib_BEfunc::getRecord('pages', $pid);

			$fieldList = array();

			switch($listType) {
				case 'metadata':
					$fieldList = array_merge($fieldList, array(
						'keywords',
						'description',
						'abstract',
						'author',
						'author_email',
					));
					break;

				case 'pagetitle':
					$fieldList = array_merge($fieldList, array(
						'tx_tqseo_pagetitle',
						'tx_tqseo_pagetitle_prefix',
						'tx_tqseo_pagetitle_suffix',
					));
					break;
			}

			// Init tree
			$tree = t3lib_div::makeInstance('t3lib_pageTree');
			foreach($fieldList as $field) {
				$tree->addField($field, true);
			}
			$tree->init('AND doktype IN (1,4) AND '.$BE_USER->getPagePermsClause(1));

			$tree->tree[] = array(
				'row'			=> $page,
				'invertedDepth'	=> 0,
			);

			$tree->getTree($pid, $depth, '');


			foreach($tree->tree as $row) {
				$tmp = $row['row'];
				$tmp['_depth'] = $row['invertedDepth'];

				$list[] = $tmp;
			}
		}

		$ret = array(
			'results'	=> count($list),
			'rows'		=> $list,
		);

		return $ret;
	}

}

?>