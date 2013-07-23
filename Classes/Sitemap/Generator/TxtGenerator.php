<?php
namespace TQ\TqSeo\Sitemap\Generator;

/***************************************************************
*  Copyright notice
*
*  (c) 2012 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
 * Sitemap TXT
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
class TxtGenerator extends \TQ\TqSeo\Sitemap\Generator\AbstractGenerator {

    ###########################################################################
    # Methods
    ###########################################################################

    /**
     * Create sitemap index
     *
     * @return	string
     */
    public function sitemapIndex() {
        return '';
    }

    /**
     * Create sitemap (for page)
     *
     * @param	integer	$page	Page
     * @return	string
     */
    public function sitemap($page = null) {
        $ret = array();

        foreach($this->sitemapPages as $sitemapPage) {
            if(empty($this->pages[ $sitemapPage['page_uid'] ])) {
                // invalid page
                continue;
            }

            //$page = $this->pages[ $sitemapPage['page_uid'] ];

            $ret[] = \TQ\TqSeo\Utility\GeneralUtility::fullUrl( $sitemapPage['page_url'] );
        }

        // Call hook
        \TQ\TqSeo\Utility\GeneralUtility::callHook('sitemap-text-output', $this, $ret);

        return implode("\n", $ret);
    }
}
