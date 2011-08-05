<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
 * Scheduler Task Sitemap Base
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
abstract class tx_tqseo_scheduler_task_sitemap_base extends tx_scheduler_task {

	###########################################################################
	# Attributes
	###########################################################################

	/**
	 * Sitemap base directory
	 *
	 * @var string
	 */
	protected $_sitemapDir = null;

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Execute task
	 */
	public function execute() {
		// Build sitemap

		$rootPageList = $this->_getRootPages();

		$this->_cleanupDirectory();

		foreach($rootPageList as $uid => $page) {
			$this->_initRootPage($uid);
			$this->_buildSitemap($uid);
		}

		return true;
	}

	/**
	 * Get list of root pages in current typo3
	 *
	 * @return	array
	 */
	protected function _getRootPages() {
		global $TYPO3_DB;

		$ret = array();

		$query = 'SELECT uid
					FROM pages
				   WHERE is_siteroot = 1
					  AND deleted = 0';
		$res = $TYPO3_DB->sql_query($query);

		while($row = $TYPO3_DB->sql_fetch_assoc($res) ) {
			$uid = $row['uid'];
			$ret[$uid] = $row;
		}

		return $ret;
	}

	/**
	 * Initalize root page (TSFE and stuff)
	 *
	 * @param	integer	$rootPageId	$rootPageId
	 */
	protected function _initRootPage($rootPageId) {
		$GLOBALS['TT'] = new t3lib_timeTrackNull;
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $rootPageId, 0);
		$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$GLOBALS['TSFE']->sys_page->init(TRUE);
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($rootPageId, '');
		$GLOBALS['TSFE']->getConfigArray();
		$GLOBALS['TSFE']->cObj = new tslib_cObj();
	}

	/**
	 * Cleanup sitemap directory
	 */
	protected function _cleanupDirectory() {
		if( empty($this->_sitemapDir) ) {
			throw new Exception('Basedir not set');
		}

		$fullPath = PATH_site.'/'.$this->_sitemapDir;

		if( !is_dir($fullPath) ) {
			t3lib_div::mkdir($fullPath);
		}

		foreach( new DirectoryIterator($fullPath) as $file) {
			if( $file->isFile() && !$file->isDot() ) {
				$fileName = $file->getFilename();
				unlink( $fullPath.'/'.$fileName );
			}
		}
	}

	/**
	 * Write content to file
	 *
	 * @param	string	$file		Filename/path
	 * @param	string	$content	Content
	 */
	protected function _writeToFile($file, $content) {
		if( !function_exists('gzopen') ) {
			throw new Exception('tq_seo needs zlib support');
		}

		$fp = gzopen($file, 'w');
		gzwrite($fp,$content);
		gzclose($fp);
	}

	###########################################################################
	# Abstract Methods
	###########################################################################

	/**
	 * Build sitemap
	 *
	 * @param	integer	$rootPageId	Root page id
	 */
	abstract protected function _buildSitemap($rootPageId);

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/scheduler/class.sitemap_xml.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/scheduler/class.sitemap_xml.php']);
}
?>
