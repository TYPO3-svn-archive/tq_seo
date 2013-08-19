<?php
namespace TQ\TqSeo\Controller;

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
 * TYPO3 Backend module page seo
 *
 * @author      TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package     TYPO3
 * @subpackage  tq_seo
 */
class BackendPageSeoController extends \TQ\TqSeo\Backend\Module\AbstractStandardModule {
    ###########################################################################
    # Attributes
    ###########################################################################

    ###########################################################################
    # Methods
    ###########################################################################

    /**
     * Main action
     */
    public function mainAction() {
        return $this->_handleSubAction('metadata');
    }

    /**
     * Geo action
     */
    public function geoAction() {
        return $this->_handleSubAction('geo');
    }

    /**
     * searchengines action
     */
    public function searchenginesAction() {
        return $this->_handleSubAction('searchengines');
    }

    /**
     * url action
     */
    public function urlAction() {
        return $this->_handleSubAction('url');
    }

    /**
     * pagetitle action
     */
    public function pagetitleAction() {
        return $this->_handleSubAction('pagetitle');
    }

    /**
     * pagetitle action
     */
    public function pagetitlesimAction() {
        return $this->_handleSubAction('pagetitlesim');
    }

    protected function _handleSubAction($type) {
        global $TYPO3_DB;

        $pageId		= (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');

        if( empty($pageId) ) {
            return '<div class="typo3-message message-warning">'.htmlspecialchars($this->_translate('message_no_valid_page')).'</div>';
        }

        ###############################
        # HTML
        ###############################
        // FIXME: do we really need a template engine here?
        $this->template = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $pageRenderer = $this->template->getPageRenderer();

        $pageRenderer->addJsFile(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('tq_seo') . 'Resources/Public/Backend/JavaScript/Ext.ux.plugin.FitToParent.js');
        $pageRenderer->addJsFile(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('tq_seo') . 'Resources/Public/Backend/JavaScript/TQSeo.js');
        $pageRenderer->addJsFile(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('tq_seo') . 'Resources/Public/Backend/JavaScript/TQSeo.overview.js');

        $realUrlAvailable = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl');

        // Include Ext JS inline code
        $pageRenderer->addJsInlineCode(
            'TQSeo.overview',

            'Ext.namespace("TQSeo.overview");

            TQSeo.overview.conf = {
                sessionToken			: '.json_encode($this->_sessionToken('tq_tqseo_backend_ajax_pageajax')).',
                ajaxController			: '.json_encode($this->doc->backPath. 'ajax.php?ajaxID=tx_tqseo_backend_ajax::page').',
                pid						: '.(int)$pageId .',
                renderTo				: "tx-tqseo-sitemap-grid",

                pagingSize				: 50,

                sortField				: "crdate",
                sortDir					: "DESC",

                filterIcon				: '. json_encode(\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-system-tree-search-open')) .',

                depth					: 2,

                languageFullList		: '. json_encode($languageFullList) .',

                listType				: '. json_encode($type) .',

                criteriaFulltext		: "",

                realurlAvailable		: '. json_encode($realUrlAvailable) .',

                sprite : {
                    edit	: '.json_encode( \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-open') ).',
                    info	: '.json_encode( \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-info') ).'
                }
            };

            // Localisation:
            TQSeo.overview.conf.lang = {
                title					: '. json_encode( '' ) .',
                pagingMessage			: '. json_encode( $this->_translate('pager_results') ) .',
                pagingEmpty				: '. json_encode( $this->_translate('pager_noresults') ) .',

                boolean_yes				: '. json_encode( $this->_translate('boolean_yes') ) .',
                boolean_no				: '. json_encode( $this->_translate('boolean_no') ) .',

                labelDepth				: '. json_encode( $this->_translate('label_depth') ) .',

                labelSearchFulltext		: '. json_encode( $this->_translate('label_search_fulltext') ) .',
                emptySearchFulltext		: '. json_encode( $this->_translate('empty_search_fulltext') ) .',

                page_uid				: '. json_encode( $this->_translate('header_sitemap_page_uid') ) .',
                page_title				: '. json_encode( $this->_translate('header_sitemap_page_title') ) .',
                page_keywords			: '. json_encode( $this->_translate('header_sitemap_page_keywords') ) .',
                page_description		: '. json_encode( $this->_translate('header_sitemap_page_description') ) .',
                page_abstract			: '. json_encode( $this->_translate('header_sitemap_page_abstract') ) .',
                page_author				: '. json_encode( $this->_translate('header_sitemap_page_author') ) .',
                page_author_email		: '. json_encode( $this->_translate('header_sitemap_page_author_email') ) .',
                page_lastupdated		: '. json_encode( $this->_translate('header_sitemap_page_lastupdated') ) .',

                page_geo_lat			: '. json_encode( $this->_translate('header_sitemap_page_geo_lat') ) .',
                page_geo_long			: '. json_encode( $this->_translate('header_sitemap_page_geo_long') ) .',
                page_geo_place			: '. json_encode( $this->_translate('header_sitemap_page_geo_place') ) .',
                page_geo_region			: '. json_encode( $this->_translate('header_sitemap_page_geo_region') ) .',

                page_tx_tqseo_pagetitle			: '. json_encode( $this->_translate('header_sitemap_page_tx_tqseo_pagetitle') ) .',
                page_tx_tqseo_pagetitle_rel		: '. json_encode( $this->_translate('header_sitemap_page_tx_tqseo_pagetitle_rel') ) .',
                page_tx_tqseo_pagetitle_prefix	: '. json_encode( $this->_translate('header_sitemap_page_tx_tqseo_pagetitle_prefix') ) .',
                page_tx_tqseo_pagetitle_suffix	: '. json_encode( $this->_translate('header_sitemap_page_tx_tqseo_pagetitle_suffix') ) .',

                page_title_simulated			: '. json_encode( $this->_translate('header_pagetitlesim_title_simulated') ) .',

                page_searchengine_canonicalurl	: '. json_encode( $this->_translate('header_searchengine_canonicalurl') ) .',
                page_searchengine_is_exclude	: '. json_encode( $this->_translate('header_searchengine_is_excluded') ) .',
                searchengine_is_exclude_disabled	: '. json_encode( $this->_translate('searchengine_is_exclude_disabled') ) .',
                searchengine_is_exclude_enabled	: '. json_encode( $this->_translate('searchengine_is_exclude_enabled') ) .',

                page_sitemap_priority			: '. json_encode( $this->_translate('header_sitemap_priority') ) .',

                page_url_scheme					: '. json_encode( $this->_translate('header_url_scheme') ) .',
                page_url_scheme_default			: '. json_encode( $this->_translate('page_url_scheme_default') ) .',
                page_url_scheme_http			: '. json_encode( $this->_translate('page_url_scheme_http') ) .',
                page_url_scheme_https			: '. json_encode( $this->_translate('page_url_scheme_https') ) .',
                page_url_alias					: '. json_encode( $this->_translate('header_url_alias') ) .',
                page_url_realurl_pathsegment	: '. json_encode( $this->_translate('header_url_realurl_pathsegment') ) .',
                page_url_realurl_pathoverride	: '. json_encode( $this->_translate('header_url_realurl_pathoverride') ) .',
                page_url_realurl_exclude		: '. json_encode( $this->_translate('header_url_realurl_exclude') ) .',

                qtip_pagetitle_simulate			: '. json_encode( $this->_translate('qtip_pagetitle_simulate') ) .',
                qtip_url_simulate				: '. json_encode( $this->_translate('qtip_url_simulate') ) .',

                value_default					: '. json_encode( $this->_translate('value_default') ) .'
            };
            ');
    }


}
