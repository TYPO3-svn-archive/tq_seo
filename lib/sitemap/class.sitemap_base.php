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
 * Sitemap Base
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
abstract class tx_tqseo_sitemap_base {
	###########################################################################
	# Attributes
	###########################################################################

	/**
	 * Current root pid
	 * 
	 * @var integer
	 */
	protected $rootPid		= NULL;

	/**
	 * Sitemap pages
	 * 
	 * @var array
	 */
	public $sitemapPages	= array();

	/**
	 * Page lookups
	 * 
	 * @var array
	 */
	public $pages		= array();

	/**
	 * Extension configuration
	 * 
	 * @var array
	 */
	protected $extConf		= array();
	
	/**
	 * Extension setup configuration
	 * 
	 * @var array
	 */
	protected $tsSetup		= array();

	/**
	 * Page change frequency definition list
	 * 
	 * @var array
	 */
	protected $pageChangeFrequency = array(
		1 => 'always',
		2 => 'hourly',
		3 => 'daily',
		4 => 'weekly',
		5 => 'monthly',
		6 => 'yearly',
		7 => 'never',
	);

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Fetch sitemap information and generate sitemap
	 */
	public function main() {
		global $TSFE, $TYPO3_DB, $TYPO3_CONF_VARS;
		
		// INIT
		$this->rootPid		= tx_tqseo_tools::getRootPid();
		$sysLanguageId		= null;

		$this->tsSetup		= $TSFE->tmpl->setup['plugin.']['tq_seo.']['sitemap.'];
		
		// check if sitemap is enabled
		if( empty($this->tsSetup['enable']) ) {
			$this->showError();
		}

		$typo3Pids = array();
		
		// Language limit via setupTS
		if( !empty($this->tsSetup['limitToCurrentLanguage']) ) {
			$sysLanguageId = 0;
			if(!empty($TSFE->tmpl->setup['config.']['sys_language_uid'])) {
				$sysLanguageId = (int)$TSFE->tmpl->setup['config.']['sys_language_uid'];
			}
		}
		
		// Fetch sitemap list/pages
		$list = tx_tqseo_sitemap::getList($this->rootPid, $sysLanguageId);
		
		if( $list === false ) {
			return $this->showError();
		}
		
		$this->sitemapPages	= $list['tx_tqseo_sitemap'];
		$this->pages		= $list['pages'];

		// Call hook
		tx_tqseo_tools::callHook('sitemap-setup', $this, $ret);

		$ret .= $this->createSitemap();

		return $ret;
	}

	/**
	 * Show error
	 *
	 * @param	string	$msg			Message
	 */
	protected function showError($msg = null) {
		global $TSFE;

		if( $msg === null ) {
			$msg = 'Sitemap is not available, please check your configuration';
		}

		header('HTTP/1.0 503 Service Unavailable');
		echo $msg;
		exit;
	}

	/**
	 * Create sitemap
	 */
	abstract protected function createSitemap();
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_base.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_base.php']);
}
?>