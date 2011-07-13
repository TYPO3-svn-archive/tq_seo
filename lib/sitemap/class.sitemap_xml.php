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
 * Sitemap XML
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
class tx_tqseo_sitemap_xml extends tx_tqseo_sitemap_base {

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Create Sitemap
	 * (either Index or page)
	 *
	 * @return string 		XML Sitemap
	 */
	protected function createSitemap() {
		$ret = '';
		$page = t3lib_div::_GP('page');
		
		$pageLimit		= 10000;
		
		// Page limit on sitemap (DEPRECATED)
		$tmp = $this->getExtConf('sitemap_pageSitemapItemLimit', false);
		if( $tmp !== false ) {
			$pageLimit = (int)$tmp;
		}
		
		if( isset($this->tsSetup['pageLimit']) && $this->tsSetup['pageLimit'] != '' ) {
			$pageLimit = (int)$this->tsSetup['pageLimit'];
		}

		$pageItems		= count($this->sitemapPages);
		$pageItemBegin	= $pageLimit * ($page-1);
		$pageCount		= ceil($pageItems/$pageLimit);


		if(empty($page) || $page == 'index') {
			$ret = $this->createSitemapIndex($pageCount);
		} elseif(is_numeric($page)) {
			if( $pageItemBegin <= $pageItems) {
				$this->sitemapPages = array_slice($this->sitemapPages, $pageItemBegin, $pageLimit);
				$ret = $this->createSitemapPage( $page );
			}
		}

		return $ret;
	}

	/**
	 * Create Sitemap Index
	 *
	 * @return string 		XML Sitemap
	 */
	protected function createSitemapIndex($pageCount) {
		global $TSFE;

		$sitemaps = array();

		// TODO: pages?
		$linkConf = array(
			'parameter'			=> tx_tqseo_tools::getRootPid(),
			'additionalParams'	=> '',
			'useCacheHash'		=> 1,
		);

		for($i=0; $i < $pageCount; $i++) {
			$linkConf['additionalParams'] = '&type='.$TSFE->type.'&page='.($i+1);
			$sitemaps[] = t3lib_div::locationHeaderUrl($TSFE->cObj->typoLink_URL($linkConf));
		}

		$ret = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';


		foreach($sitemaps as $sitemapPage) {
			$ret .= '<sitemap><loc>'.htmlspecialchars($sitemapPage).'</loc></sitemap>';
		}

		$ret .= '</sitemapindex>';

		return $ret;
	}


	/**
	 * Create Sitemap Page
	 *
	 * @return string 		XML Sitemap
	 */
	protected function createSitemapPage() {
		$ret = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		$pagePriorityDefaultValue		= 1;
		$pagePriorityDepthMultiplier	= 1;
		$pagePriorityDepthModificator	= 1;

		#####################
		# Ext conf (DEPRECATED)
		#####################
		
		$tmp = $this->getExtConf('sitemap_pagePriorityDefaultValue', false);
		if( $tmp !== false ) {
			$pagePriorityDefaultValue = floatval($tmp);
		}
		
		$tmp = $this->getExtConf('sitemap_pagePriorityDepthMultiplier', false);
		if( $tmp !== false ) {
			$pagePriorityDepthMultiplier = floatval($tmp);
		}
		
		$tmp = $this->getExtConf('sitemap_pagePriorityDepthModificator', false);
		if( $tmp !== false ) {
			$pagePriorityDepthModificator = floatval($tmp);
		}
		
		#####################
		# SetupTS conf
		#####################
		if( isset($this->tsSetup['pagePriority']) && $this->tsSetup['pagePriority'] != '' ) {
			$pagePriorityDefaultValue = floatval($this->tsSetup['pagePriority']);
		}
		
		if( isset($this->tsSetup['pagePriorityDepthMultiplier']) && $this->tsSetup['pagePriorityDepthMultiplier'] != '' ) {
			$pagePriorityDepthMultiplier = floatval($this->tsSetup['pagePriorityDepthMultiplier']);
		}
		
		if( isset($this->tsSetup['pagePriorityDepthModificator']) && $this->tsSetup['pagePriorityDepthModificator'] != '' ) {
			$pagePriorityDepthModificator = floatval($this->tsSetup['pagePriorityDepthModificator']);
		}
		
		foreach($this->sitemapPages as $sitemapPage) {
			if(empty($this->pages[ $sitemapPage['page_uid'] ])) {
				// invalid page
				continue;
			}

			$page = $this->pages[ $sitemapPage['page_uid'] ];

			#####################################
			# Page priority
			#####################################
			$pageDepth = $sitemapPage['page_depth'];
			$pageDepthBase = 1;

			if(!empty($sitemapPage['page_hash'])) {
				// page has module-content - trade as subpage
				++$pageDepth;
			}

			$pageDepth -= $pagePriorityDepthModificator;


			if($pageDepth > 0.1) {
				$pageDepthBase = 1/$pageDepth;
			}

			$pagePriority = $pagePriorityDefaultValue * ( $pageDepthBase * $pagePriorityDepthMultiplier );
			if(!empty($page['tx_tqseo_priority'])) {
				$pagePriority = $page['tx_tqseo_priority'] / 100;
			}

			$pagePriority = number_format($pagePriority, 2);

			if($pagePriority > 1) {
				$pagePriority = '1.00';
			} elseif($pagePriority <= 0) {
				$pagePriority = '0.00';
			}

			#####################################
			# Page informations
			#####################################

			// page Url
			$pageUrl = t3lib_div::locationHeaderUrl( $sitemapPage['page_url'] );

			// Page modification date
			$pageModifictionDate = date('c', $sitemapPage['tstamp']);

			// Page change frequency
			$pageChangeFrequency = NULL;
			if( !empty($page['tx_tqseo_change_frequency']) ) {
				$pageChangeFrequency = (int)$page['tx_tqseo_change_frequency'];
			} elseif( !empty($sitemapPage['page_change_frequency']) ) {
				$pageChangeFrequency = (int)$sitemapPage['page_change_frequency'];
			}

			if( !empty($pageChangeFrequency) && !empty( $this->pageChangeFrequency[$pageChangeFrequency] ) ) {
				$pageChangeFrequency = $this->pageChangeFrequency[$pageChangeFrequency];
			} else {
				$pageChangeFrequency = NULL;
			}


			#####################################
			# Sitemal page output
			#####################################
			$ret .= '<url>';
			$ret .= '<loc>'.htmlspecialchars($pageUrl).'</loc>';
			$ret .= '<lastmod>'.$pageModifictionDate.'</lastmod>';
			$ret .= '<priority>'.$pagePriority.'</priority>';

			if( !empty($pageChangeFrequency) ) {
				$ret .= '<changefreq>'.htmlspecialchars($pageChangeFrequency).'</changefreq>';
			}

			$ret .= '</url>';
		}


		$ret .= '</urlset>';

		return $ret;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_xml.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/sitemap/class.sitemap_xml.php']);
}
?>