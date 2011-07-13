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
 * Sitemap Indexer
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
class user_tqseo_sitemap_indexer {

	###########################################################################
	# Attributes
	###########################################################################

	/**
	 * Extension configuration
	 * @var array
	 */
	protected $extConf = array();

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Constructor
	 */
	public function __construct() {
		global $TYPO3_DB, $TSFE, $TYPO3_CONF_VARS;

		// Load ext conf
		$this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['tq_seo']);
		if(!is_array($this->extConf)) {
			$this->extConf = array();
		}
	}

	/**
	 * Get extension configuration (by name)
	 *
	 * @param	string	$name			Configuration settings name
	 * @param	mixed	$defaultValue	Default value (if configuration doesn't exists)
	 * @return	mixed
	 */
	protected function getExtConf($name, $defaultValue = NULL) {
		$ret = $defaultValue;
		if(!empty($this->extConf[$name])) {
			$ret = $this->extConf[$name];
		}

		return $ret;
	}

	/**
	 * Clear outdated and invalid pages from sitemap table
	 */
	public function clearExpiredSitemapPages() {
		global $TYPO3_DB, $TSFE, $TYPO3_CONF_VARS;

		#####################
		# Expired pages
		#####################
		$expireDays = (int)$this->getExtConf('sitemap_pageSitemapExpireDays', 60);

		$tsstamp = time() - $expireDays*24*60*60;

		$query = 'DELETE FROM tx_tqseo_sitemap WHERE tsstamp <= '.(int)$tsstamp;
		$res = $TYPO3_DB->sql_query($query);

		#####################
		# Deleted or
		# excluded pages
		#####################
		$deletedSitemapPages = array();

		$query = 'SELECT
						ts.uid
					FROM
						tx_tqseo_sitemap ts
						LEFT JOIN pages p
							ON		p.uid = ts.page_uid
								AND	p.deleted = 0
								AND p.hidden = 0
					WHERE
						p.uid IS NULL';
		$res = $TYPO3_DB->sql_query($query);

		while( $row = $TYPO3_DB->sql_fetch_assoc($res) ) {
			$deletedSitemapPages[ $row['uid'] ] = $row['uid'];
		}

		// delete pages
		if(!empty($deletedSitemapPages)) {
			$query = 'DELETE FROM tx_tqseo_sitemap WHERE uid IN ('.implode(',', $deletedSitemapPages).')';
			$TYPO3_DB->sql_query($query);
		}
	}

	/**
	 * Add Page to sitemap table
	 */
	public function addPageToSitemapIndex() {
		global $TYPO3_DB, $TSFE, $TYPO3_CONF_VARS;

		// check if sitemap is enabled
		if( empty($TSFE->tmpl->setup['plugin.']['tq_seo.']['sitemap.']['enable']) ) {
			return true;
		}

		// Skip non-seo-pages
		if( $_SERVER['REQUEST_METHOD'] !== 'GET' || !empty($TSFE->page['tx_tqseo_is_exclude']) || !empty($TSFE->fe_user->user['uid']) ) {
			return true;
		}

		// Skip own sitemap tools
		if( $TSFE->type == 841131 || $TSFE->type == 841132) {
			return true;
		}

		// Skip no_cache-pages
		if( !empty($TSFE->no_cache) ) {
			return true;
		}

		// Fetch chash
		$pageHash = NULL;
		if(!empty($TSFE->cHash)) {
			$pageHash = $TSFE->cHash;
		}

		// Fetch sysLanguage
		$pageLanguage = 0;
		if(!empty($TSFE->tmpl->setup['config.']['sys_language_uid'])) {
			$pageLanguage = (int)$TSFE->tmpl->setup['config.']['sys_language_uid'];
		}

		// Fetch page changeFrequency
		$pageChangeFrequency = NULL;
		if( !empty($TSFE->page['tx_tqseo_change_frequency']) ) {
			$pageChangeFrequency = (int)$TSFE->page['tx_tqseo_change_frequency'];
		} elseif(!empty($TSFE->tmpl->setup['plugin.']['tq_seo.']['sitemap.']['changeFrequency'])) {
			$pageChangeFrequency = (int)$TSFE->tmpl->setup['plugin.']['tq_seo.']['sitemap.']['changeFrequency'];
		}

		if( empty($pageChangeFrequency) ) {
			$pageChangeFrequency = 0;
		}

		// Fetch pageUrl
		if( $pageHash !== NULL ) {
			$pageUrl = $TSFE->anchorPrefix;
		} else {
			$linkConf = array(
				'parameter'	=> $TSFE->id,
			);

			$pageUrl = $TSFE->cObj->typoLink_URL($linkConf);

			if(!empty($TSFE->tmpl->setup['config.']['absRefPrefix']) && strpos($pageUrl, $TSFE->tmpl->setup['config.']['absRefPrefix']) === 0) {
				$pageUrl = substr($pageUrl, strlen($TSFE->tmpl->setup['config.']['absRefPrefix']));
			}
		}

		$tstamp = time();

		$pageData = array(
			'tstamp'				=> $tstamp,
			'crdate'				=> $tstamp,
			'page_rootpid'			=> tx_tqseo_tools::getRootPid(),
			'page_uid'				=> $TSFE->id,
			'page_language'			=> $pageLanguage,
			'page_url'				=> $pageUrl,
			'page_hash'				=> md5($pageUrl),
			'page_chash'			=> $pageHash,
			'page_depth'			=> count($TSFE->rootLine),
			'page_change_frequency'	=> $pageChangeFrequency,
		);

		// Escape/Quote data
		unset($pageDataValue);
		foreach($pageData as &$pageDataValue) {
			if($pageDataValue === NULL) {
				$pageDataValue = 'NULL';
			} elseif( is_int($pageDataValue) || is_numeric($pageDataValue) ) {
				// Don't quote numeric/integers
				$pageDataValue = (int)$pageDataValue;
			} else {
				// String
				$pageDataValue = $TYPO3_DB->fullQuoteStr($pageDataValue, 'tx_tqseo_sitemap');
			}
		}
		unset($pageDataValue);

		$query = 'SELECT
						uid
					FROM
						tx_tqseo_sitemap
					WHERE
							page_uid = '.$pageData['page_uid'].'
						AND	page_language = '.$pageData['page_language'].'
						AND page_hash = '.$pageData['page_hash'];
		$res = $TYPO3_DB->sql_query($query);

		if( $row = $TYPO3_DB->sql_fetch_assoc($res) ) {
			$query = 'UPDATE
							tx_tqseo_sitemap
						SET
							tstamp					= '.$pageData['tstamp'].',
							page_rootpid			= '.$pageData['page_rootpid'].',
							page_language			= '.$pageData['page_language'].',
							page_url				= '.$pageData['page_url'].',
							page_depth				= '.$pageData['page_depth'].',
							page_change_frequency	= '.$pageData['page_change_frequency'].'
						WHERE
							uid = '.$row['uid'];
			$TYPO3_DB->sql_query($query);
		} else {
			#####################################
			# INSERT
			#####################################
			$TYPO3_DB->exec_INSERTquery(
				'tx_tqseo_sitemap',
				$pageData,
				array_keys($pageData)
			);
		}

		return true;
	}


	###########################################################################
	# HOOKS
	###########################################################################

	public function hook_indexContent(&$pObj) {
		$this->addPageToSitemapIndex();

		$possibility = (int)$this->getExtConf('sitemap_clearCachePossibility', 1000);

		$clearCacheChance = ceil(mt_rand(0, $possibility));
		if( $clearCacheChance == 1 ) {
			$this->clearExpiredSitemapPages();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_indexer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_indexer.php']);
}
?>