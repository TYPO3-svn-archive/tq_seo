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
        global $TYPO3_DB;


        // Init
        $rootPageList		= \TQ\TqSeo\Utility\BackendUtility::getRootPageList();
        $rootSettingList	= \TQ\TqSeo\Utility\BackendUtility::getRootPageSettingList();

        ###############################
        # Fetch
        ###############################
        $statsList['sum_total'] = $TYPO3_DB->exec_SELECTgetRows(
            'page_rootpid, COUNT(*) as count',
            'tx_tqseo_sitemap',
            '',
            'page_rootpid',
            '',
            '',
            'page_rootpid'
        );

        // Fetch domain name
        $res = $TYPO3_DB->exec_SELECTquery(
            'pid, domainName, forced',
            'sys_domain',
            'hidden = 0',
            '',
            'forced DESC, sorting'
        );

        $domainList = array();
        while( $row = $TYPO3_DB->sql_fetch_assoc($res) ) {
            $pid = $row['pid'];

            if( !empty($row['forced']) ) {
                $domainList[$pid] = $row['domainName'];
            } elseif( empty($domainList[$pid]) ) {
                $domainList[$pid] = $row['domainName'];
            }
        }

        #####################
        # Build root page list
        ####################


        unset($page);
        foreach($rootPageList as $pageId => &$page) {
            $stats = array(
                'sum_pages'		=> 0,
                'sum_total' 	=> 0,
                'sum_xml_pages'	=> 0,
            );

            // Get domain
            $domain = null;
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
            $tmp = $TYPO3_DB->exec_SELECTgetRows(
                'DISTINCT page_uid',
                'tx_tqseo_sitemap',
                'page_rootpid = '.(int)$pageId
            );
            if( !empty($tmp) ) {
                $stats['sum_pages'] = count($tmp);
            }

            $args = array(
                'rootPid'	=> $pageId
            );
            // FIXME: fix link
            #$listLink = $this->_moduleLinkOnClick('sitemapList', $args);


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

}
