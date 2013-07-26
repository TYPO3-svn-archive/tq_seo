<?php
namespace TQ\TqSeo\Sitemap\Generator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
 * Sitemap abstract generator
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
abstract class AbstractGenerator {
    ###########################################################################
    # Attributes
    ###########################################################################

    /**
     * Current root pid
     *
     * @var integer
     */
    public $rootPid = NULL;

    /**
     * Sitemap pages
     *
     * @var array
     */
    public $sitemapPages = array();

    /**
     * Page lookups
     *
     * @var array
     */
    public $pages = array();

    /**
     * Extension configuration
     *
     * @var array
     */
    protected $extConf = array();

    /**
     * Extension setup configuration
     *
     * @var array
     */
    public $tsSetup = array();

    /**
     * Page change frequency definition list
     *
     * @var array
     */
    public $pageChangeFrequency = array(
        1 => 'always',
        2 => 'hourly',
        3 => 'daily',
        4 => 'weekly',
        5 => 'monthly',
        6 => 'yearly',
        7 => 'never',
    );

    /**
     * Link template for sitemap index
     *
     * Replacemennt marker ###PAGE### for page-uid
     *
     * @var string|boolean
     */
    public $indexPathTemplate = false;

    ###########################################################################
    # Methods
    ###########################################################################

    /**
     * Fetch sitemap information and generate sitemap
     */
    public function __construct() {
        global $TSFE, $TYPO3_DB, $TYPO3_CONF_VARS;

        // INIT
        $this->rootPid		= \TQ\TqSeo\Utility\GeneralUtility::getRootPid();
        $sysLanguageId		= null;

        $this->tsSetup		= $TSFE->tmpl->setup['plugin.']['tq_seo.']['sitemap.'];

        // Language limit via setupTS
        if( \TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue('is_sitemap_language_lock', false) ) {
            $sysLanguageId = \TQ\TqSeo\Utility\GeneralUtility::getLanguageId();
        }

        // Fetch sitemap list/pages
        $list = \TQ\TqSeo\Utility\SitemapUtility::getList($this->rootPid, $sysLanguageId);

        $this->sitemapPages	= $list['tx_tqseo_sitemap'];
        $this->pages		= $list['pages'];

        // Call hook
        \TQ\TqSeo\Utility\GeneralUtility::callHook('sitemap-setup', $this, $foo);
    }

    /**
     * Return page count
     *
     * @return integer
     */
    public function pageCount() {
        $pageLimit = \TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue('sitemap_page_limit', null);

        if( empty($pageLimit) ) {
            $pageLimit = 1000;
        }

        $pageItems		= count($this->sitemapPages);
        $pageCount		= ceil($pageItems/$pageLimit);

        return $pageCount;
    }

    ###########################################################################
    # Abstract methods
    ###########################################################################

    /**
     * Create sitemap index
     *
     * @return string
     */
    abstract public function sitemapIndex();

    /**
     * Create sitemap (for page)
     *
     * @param   integer $page   Page
     * @return  string
     */
    abstract public function sitemap($page = null);

}
