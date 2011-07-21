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
 * Sitemap Output Base
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
abstract class tx_tqseo_sitemap_output_base {

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Output sitemap
	 *
	 * @return	string
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

		$ret .= $this->_build();

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
		$TSFE->pageErrorHandler( true, NULL, $msg );
		exit;
	}

	###########################################################################
	# Abstract methods
	###########################################################################
	/*
	 * Build
	 *
	 * @return string
	 */
	abstract protected function _build();

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/output/class.base.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/output/class.base.php']);
}
?>