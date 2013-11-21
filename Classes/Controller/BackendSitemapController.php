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
 * TYPO3 Backend module sitemap
 *
 * @author      TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package     TYPO3
 * @subpackage  tq_seo
 */
class BackendSitemapController extends \TQ\TqSeo\Backend\Module\AbstractStandardModule {
    // ########################################################################
    // Attributes
    // ########################################################################

    // ########################################################################
    // Methods
    // ########################################################################

    /**
     * Main action
     */
    public function mainAction() {
        // Init
        $rootPageList		= \TQ\TqSeo\Utility\BackendUtility::getRootPageList();
        $rootSettingList	= \TQ\TqSeo\Utility\BackendUtility::getRootPageSettingList();

        // ############################
        // Fetch
        // ############################
        $statsList['sum_total'] = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'page_rootpid, COUNT(*) as count',
            'tx_tqseo_sitemap',
            '',
            'page_rootpid',
            '',
            '',
            'page_rootpid'
        );

        // Fetch domain name
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'pid, domainName, forced',
            'sys_domain',
            'hidden = 0',
            '',
            'forced DESC, sorting'
        );

        $domainList = array();
        while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
            $pid = $row['pid'];

            if( !empty($row['forced']) ) {
                $domainList[$pid] = $row['domainName'];
            } elseif( empty($domainList[$pid]) ) {
                $domainList[$pid] = $row['domainName'];
            }
        }

        // #################
        // Build root page list
        // #################


        unset($page);
        foreach($rootPageList as $pageId => &$page) {
            $stats = array(
                'sum_pages'		=> 0,
                'sum_total' 	=> 0,
                'sum_xml_pages'	=> 0,
            );

            // Get domain
            $domain = NULL;
            if( !empty($domainList[$pageId]) ) {
                $domain = $domainList[$pageId];
            }

            // Setting row
            $settingRow = array();
            if( !empty($rootSettingList[$pageId]) ) {
                $settingRow = $rootSettingList[$pageId];
            }


            // Calc stats
            foreach($statsList as $statsKey => $statsTmpList) {
                if( !empty($statsTmpList[$pageId]) ) {
                    $stats[$statsKey] = $statsTmpList[$pageId]['count'];
                }
            }

            // Root statistics
            $tmp = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                'DISTINCT page_uid',
                'tx_tqseo_sitemap',
                'page_rootpid = '.(int)$pageId
            );
            if( !empty($tmp) ) {
                $stats['sum_pages'] = count($tmp);
            }


            // FIXME: fix link
            //$args = array(
            //    'rootPid'	=> $pageId
            //);
            //$listLink = $this->_moduleLinkOnClick('sitemapList', $args);


            $pagesPerXmlSitemap = 1000;
            if( !empty($settingRow['sitemap_page_limit']) ) {
                $pagesPerXmlSitemap = $settingRow['sitemap_page_limit'];
            }
            $sumXmlPages = ceil( $stats['sum_total'] / $pagesPerXmlSitemap ) ;
            $stats['sum_xml_pages'] = sprintf( $this->_translate('sitemap.xml.pages.total'), $sumXmlPages );


            $page['stats'] = $stats;
        }
        unset($page);


        // check if there is any root page
        if( empty($rootPageList) ) {
            $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                $this->_translate('message.warning.noRootPage.message'),
                $this->_translate('message.warning.noRootPage.title'),
                \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING
            );
            \TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);
        }

        $this->view->assign('RootPageList', $rootPageList);
    }

    /**
     * Sitemap action
     */
    public function sitemapAction() {
        $params  = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_tqseo_tqseotqseo_tqseositemap');
        $rootPid = $params['pageId'];


        $rootPageList = \TQ\TqSeo\Utility\BackendUtility::getRootPageList();
        $rootPage	= $rootPageList[$rootPid];

        // ###############################
        // Fetch
        // ###############################
        $pageTsConf = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($rootPid);

        $languageFullList = array(
            0 => array(
                'label'	=> $this->_translate('default.language'),
                'flag'	=> '',
            ),
        );

        if( !empty($pageTsConf['mod.']['SHARED.']['defaultLanguageFlag']) ) {
            $languageFullList[0]['flag'] = $pageTsConf['mod.']['SHARED.']['defaultLanguageFlag'];
        }

        if( !empty($pageTsConf['mod.']['SHARED.']['defaultLanguageLabel']) ) {
            $languageFullList[0]['label'] = $pageTsConf['mod.']['SHARED.']['defaultLanguageLabel'];
        }

        // Fetch other flags
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'uid, title, flag',
            'sys_language',
            'hidden = 0'
        );
        while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
            $languageFullList[$row['uid']] = array(
                'label' => htmlspecialchars($row['title']),
                'flag'  => htmlspecialchars($row['flag']),
            );
        }

        // Langauges
        $languageList = array();
        $languageList[] =	array(
            -1,
            $this->_translate('empty.search.page_language'),
        );

        foreach($languageFullList as $langId => $langRow) {
            $flag = '';

            // Flag (if available)
            if( !empty($langRow['flag']) ) {
                $flag .= '<span class="t3-icon t3-icon-flags t3-icon-flags-'.$langRow['flag'].' t3-icon-'.$langRow['flag'].'"></span>';
                $flag .= '&nbsp;';
            }

            // label
            $label = $langRow['label'];

            $languageList[] = array(
                $langId,
                $label,
                $flag
            );
        }

        // Depth
        $depthList = array();
        $depthList[] =	array(
            -1,
            $this->_translate('empty.search_page_depth'),
        );

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'DISTINCT page_depth',
            'tx_tqseo_sitemap',
            'page_rootpid = '.(int)$rootPid
        );
        while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
            $depth = $row['page_depth'];
            $depthList[] = array(
                $depth,
                $depth,
            );
        }

        // ###############################
        // Page/JS
        // ###############################

        // FIXME: do we really need a template engine here?
        $this->template = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $pageRenderer = $this->template->getPageRenderer();

        $basePathJs  = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('tq_seo') . 'Resources/Public/Backend/JavaScript';
        $basePathCss = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('tq_seo') . 'Resources/Public/Backend/Css';

        $pageRenderer->addJsFile($basePathJs.'/TQSeo.js');
        $pageRenderer->addJsFile($basePathJs.'/Ext.ux.plugin.FitToParent.js');
        $pageRenderer->addJsFile($basePathJs.'/TQSeo.sitemap.js');
        $pageRenderer->addCssFile($basePathCss.'/Default.css');

        // Include Ext JS inline code
        $pageRenderer->addJsInlineCode(
            'TQSeo.sitemap',

            'Ext.namespace("TQSeo.sitemap");

            TQSeo.sitemap.conf = {
                sessionToken			: '.json_encode($this->_sessionToken('tq_tqseo_backend_ajax_sitemapajax')).',
                ajaxController			: '.json_encode($this->doc->backPath. 'ajax.php?ajaxID=tx_tqseo_backend_ajax::sitemap').',
                pid						: '.(int)$rootPid .',
                renderTo				: "tx-tqseo-sitemap-grid",

                pagingSize				: 50,

                sortField				: "crdate",
                sortDir					: "DESC",

                filterIcon				: '. json_encode(\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-system-tree-search-open')) .',

                dataLanguage			: '. json_encode($languageList) .',
                dataDepth				: '. json_encode($depthList) .',

                criteriaFulltext		: "",
                criteriaPageUid			: "",
                criteriaPageLanguage	: "",
                criteriaPageDepth		: "",
                criteriaIsBlacklisted	: 0,

                languageFullList		: '. json_encode($languageFullList) .',
            };

            // Localisation:
            TQSeo.sitemap.conf.lang = {
                title					: '. json_encode( sprintf($this->_translate('title.sitemap.list'), $rootPage['title'], $rootPid ) ) .',
                pagingMessage			: '. json_encode( $this->_translate('pager.results') ) .',
                pagingEmpty				: '. json_encode( $this->_translate('pager.noresults') ) .',
                sitemap_page_uid		: '. json_encode( $this->_translate('header.sitemap.page_uid') ) .',
                sitemap_page_url		: '. json_encode( $this->_translate('header.sitemap.page_url') ) .',
                sitemap_page_depth		: '. json_encode( $this->_translate('header.sitemap.page_depth') ) .',
                sitemap_page_language	: '. json_encode( $this->_translate('header.sitemap.page_language') ) .',
                sitemap_page_is_blacklisted : '. json_encode( $this->_translate('header.sitemap.page_is_blacklisted') ) .',
                sitemap_tstamp			: '. json_encode( $this->_translate('header.sitemap.tstamp') ) .',
                sitemap_crdate			: '. json_encode( $this->_translate('header.sitemap.crdate') ) .',

                labelSearchFulltext		: '. json_encode( $this->_translate('label.search.fulltext') ) .',
                emptySearchFulltext		: '. json_encode( $this->_translate('empty.search.fulltext') ) .',

                labelSearchPageUid		: '. json_encode( $this->_translate('label.search.page_uid') ) .',
                emptySearchPageUid		: '. json_encode( $this->_translate('empty.search.page_uid') ) .',

                labelSearchPageLanguage	: '. json_encode( $this->_translate('label.search.page_language') ) .',
                emptySearchPageLanguage	: '. json_encode( $this->_translate('empty.search.page_language') ) .',

                labelSearchPageDepth	: '. json_encode( $this->_translate('label.search.page_depth') ) .',
                emptySearchPageDepth	: '. json_encode( $this->_translate('empty.search.page_depth') ) .',

                labelSearchIsBlacklisted : '. json_encode( $this->_translate('label.search.is_blacklisted') ) .',

                labelYes				: '. json_encode( $this->_translate('label.yes') ) .',
                labelNo					: '. json_encode( $this->_translate('label.no') ) .',

                buttonYes				: '. json_encode( $this->_translate('button.yes') ) .',
                buttonNo				: '. json_encode( $this->_translate('button.no') ) .',

                buttonDelete			: '. json_encode( $this->_translate('button.delete') ) .',
                buttonDeleteHint		: '. json_encode( $this->_translate('button.delete.hint') ) .',

                buttonBlacklist		    : '. json_encode( $this->_translate('button.blacklist') ) .',
                buttonBlacklistHint	    : '. json_encode( $this->_translate('button.blacklist.hint') ) .',
                buttonWhitelist		    : '. json_encode( $this->_translate('button.whitelist') ) .',
                buttonWhitelistHint	    : '. json_encode( $this->_translate('button.whitelist.hint') ) .',

                buttonDeleteAll         : '. json_encode( $this->_translate('button.delete_all') ) .',

                messageDeleteTitle			: '. json_encode( $this->_translate('message.delete.title') ) .',
                messageDeleteQuestion		: '. json_encode( $this->_translate('message.delete.question') ) .',

                messageDeleteAllTitle		: '. json_encode( $this->_translate('message.delete_all.title') ) .',
                messageDeleteAllQuestion	: '. json_encode( $this->_translate('message.delete_all.question') ) .',

                messageBlacklistTitle		: '. json_encode( $this->_translate('message.blacklist.title') ) .',
                messageBlacklistQuestion	: '. json_encode( $this->_translate('message.blacklist.question') ) .',

                messageWhitelistTitle		: '. json_encode( $this->_translate('message.whitelist.title') ) .',
                messageWhitelistQuestion	: '. json_encode( $this->_translate('message.whitelist.question') ) .',

                errorDeleteFailedMessage	: '. json_encode( $this->_translate('message.delete.failed_body') ) .',

                errorNoSelectedItemsBody	: '. json_encode( $this->_translate('message.no_selected_items') ) .',

                today						: '. json_encode( $this->_translate('today') ) .',
                yesterday					: '. json_encode( $this->_translate('yesterday') ) .'
            };
        ');

    }


}
