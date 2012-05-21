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


	protected $_templatePidList = array();

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

					$list = $this->_listDefaultTree($page, $depth, $fieldList);
					break;

				case 'pagetitle':
					$fieldList = array_merge($fieldList, array(
						'tx_tqseo_pagetitle',
						'tx_tqseo_pagetitle_prefix',
						'tx_tqseo_pagetitle_suffix',
					));

					$list = $this->_listDefaultTree($page, $depth, $fieldList);
					break;

				case 'pagetitlesim':
					$buildTree = false;
					$list = $this->_listPageTitleSim($page, $depth);
					break;
			}
		}

		$ret = array(
			'results'	=> count($list),
			'rows'		=> array_values($list),
		);

		return $ret;
	}


	/**
	 * Return default tree
	 *
	 * @param	array	$page		Root page
	 * @param	integer	$depth		Depth
	 * @param	array	$fieldList	Field list
	 * @return	array
	 */
	protected function _listDefaultTree($page, $depth, $fieldList) {
		global $BE_USER;

		$rootPid = $page['uid'];

		$list = array();

		$fieldList[] = 'pid';

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

		$tree->getTree($rootPid, $depth, '');


		// Build tree list
		foreach($tree->tree as $row) {
			$tmp = $row['row'];
			$list[ $tmp['uid'] ] = $tmp;
		}

		// Calc depth

		$rootLineRaw = array();
		foreach($list as $row) {
			$rootLineRaw[ $row['uid'] ] = $row['pid'];
		}

		$rootLineRaw[$rootPid] = null;

		unset($row);
		foreach($list as &$row) {
			$row['_depth'] = $this->_listCalcDepth($row['uid'], $rootLineRaw);
		}
		unset($row);


		return $list;
	}

	protected function _listCalcDepth($pageUid, $rootLineRaw, $depth = null) {

		if( $depth === null ) {
			$depth = 1;
		}

		if( empty($rootLineRaw[$pageUid]) ) {
			// found root page
			return $depth;
		}

		// we must be at least in the first depth
		++$depth;

		$pagePid = $rootLineRaw[$pageUid];

		if( !empty($pagePid) ) {
			// recursive
			$depth = $this->_listCalcDepth($pagePid, $rootLineRaw, $depth);
		}


		return $depth;
	}


	/**
	 * Return simulated page title
	 *
	 * @param	array	$page		Root page
	 * @param	integer	$depth		Depth
	 * @return	array
	 */
	protected function _listPageTitleSim($page, $depth) {
		global $TYPO3_DB, $BE_USER;

		// Init
		$list = array();

		$pid = $page['uid'];

		$fieldList = array(
			'title',
			'tx_tqseo_pagetitle',
			'tx_tqseo_pagetitle_prefix',
			'tx_tqseo_pagetitle_suffix',
		);

		$list = $this->_listDefaultTree($page, $depth, $fieldList);



		require_once (PATH_t3lib.'class.t3lib_page.php');
		require_once (PATH_t3lib.'class.t3lib_tstemplate.php');
		require_once (PATH_t3lib.'class.t3lib_tsparser_ext.php');
		require_once dirname(__FILE__).'/../../class.pagetitle.php';

		$uidList = array_keys($list);

		if( !empty($uidList) ) {
			// Find templates (for caching)
			$this->_templatePidList = array();

			$query = 'SELECT pid
						FROM sys_template
					   WHERE pid IN ('.implode(',', $uidList).')
					     AND deleted = 0
					     AND hidden = 0';
			$res = $TYPO3_DB->sql_query($query);
			while( $row = $TYPO3_DB->sql_fetch_assoc($res) ) {
				$this->_templatePidList[ $row['pid'] ] = $row['pid'];
			}

			// Build simulated title
			foreach($list as &$row) {
				$row['title_simulated'] = $this->_simulateTitle($row);
			}
		}


		return $list;
	}

	/**
	 * Generate simluated page title
	 *
	 * @param	array	$page	Page
	 * @return	string
	 */
	protected function _simulateTitle($page) {
		$this->_initTsfe($page);

		$pagetitle = new user_tqseo_pagetitle();
		$ret = $pagetitle->main($page['title']);

		return $ret;
	}


	/**
	 * Init TSFE (for simulated pagetitle)
	 *
	 * @param	array		$page		Page
	 * @param	null|array	$rootLine	Rootline
	 * @param	null|array	$pageData	Page data (recursive generated)
	 * @return	void
	 */
	protected function _initTsfe($page, $rootLine = null, $pageData = null, $rootlineFull = null) {
		global $TSFE;

		static $lastTsSetupPid = null;

		$pageUid = $page['uid'];

		if($rootLine === null) {
			$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
			$rootLine = $sysPageObj->getRootLine( $pageUid );

			// save full rootline, we need it in TSFE
			$rootlineFull = $rootLine;
		}

		if( $pageData === null ) {
			reset($rootLine);
			$pageData = current($rootLine);
		}


		if( count($rootLine) >= 2 && empty($this->_templatePidList[$pageUid]) ) {
			// go to prev page in rootline
			reset($rootLine);
			next($rootLine);
			$prevPage = current($rootLine);

			// strip current page from rootline
			reset($rootLine);
			$currPageIndex = key($rootLine);
			unset( $rootLine[$currPageIndex] );

			return $this->_initTsfe($prevPage, $rootLine, $pageData, $rootlineFull);
		}

		// Only setup tsfe if current instance must be changed
		if( $lastTsSetupPid !== $page['uid'] ) {
			$TSFE = t3lib_div::makeInstance('tslib_fe',  $TYPO3_CONF_VARS);
			$TSFE->cObj = t3lib_div::makeInstance('tslib_cObj');

			$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
			$TSObj->tt_track = 0;
			$TSObj->init();
			$TSObj->runThroughTemplates($rootLine);
			$TSObj->generateConfig();

			$TSFE->tmpl->setup = $TSObj->setup;

			$lastTsSetupPid = $page['uid'];
		}

		$TSFE->page = $pageData;
		$TSFE->rootLine = $rootlineFull;
		$TSFE->cObj->data = $pageData;
	}

	/**
	 * Update page field
	 */
	protected function _executeUpdatePageField() {
		global $TYPO3_DB, $BE_USER, $LANG;

		if( empty($this->_postVar['pid'])
			|| empty($this->_postVar['field']) ) {
			return;
		}

		$pid			= (int)$this->_postVar['pid'];
		$fieldName		= (string)$this->_postVar['field'];
		$fieldValue		= (string)$this->_postVar['value'];

		if( !$BE_USER->check('tables_modify','pages') ) {
			// No access
			return array(
				'error'	=> $LANG->getLL('access_denied'),
			);
		}

		if( !$BE_USER->check('non_exclude_fields', 'pages:'.$fieldName) ) {
			// No access
			return array(
				'error'	=> $LANG->getLL('access_denied'),
			);
		}

		$page = t3lib_BEfunc::getRecord('pages', $pid);

		if( empty($page) || !$BE_USER->doesUserHaveAccess($pageRec,2) ) {
			// No access
			return array(
				'error'	=> $LANG->getLL('access_denied'),
			);
		}

		$TYPO3_DB->exec_UPDATEquery(
			'pages',
			'uid = '.(int)$pid,
			array(
				$fieldName => $fieldValue
			)
		);
	}

}

?>