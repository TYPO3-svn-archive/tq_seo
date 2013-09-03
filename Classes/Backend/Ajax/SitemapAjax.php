<?php
namespace TQ\TqSeo\Backend\Ajax;

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
 * TYPO3 Backend ajax module sitemap
 *
 * @author      TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package     TYPO3
 * @subpackage  tq_seo
 */
class SitemapAjax extends \TQ\TqSeo\Backend\Ajax\AbstractAjax {

    /**
     * Return sitemap entry list for root tree
     *
     * @return    array
     */
    protected function _executeGetList() {
        global $TYPO3_DB;

        // Init
        $rootPageList = \TQ\TqSeo\Utility\BackendUtility::getRootPageList();

        $rootPid      = (int)$this->_postVar['pid'];
        $offset       = (int)$this->_postVar['start'];
        $limit        = (int)$this->_postVar['limit'];
        $itemsPerPage = (int)$this->_postVar['pagingSize'];

        $searchFulltext      = trim((string)$this->_postVar['criteriaFulltext']);
        $searchPageUid       = trim((int)$this->_postVar['criteriaPageUid']);
        $searchPageLanguage  = trim((string)$this->_postVar['criteriaPageLanguage']);
        $searchPageDepth     = trim((string)$this->_postVar['criteriaPageDepth']);
        $searchIsBlacklisted = (bool)trim((string)$this->_postVar['criteriaIsBlacklisted']);

        ###############################
        # Critera
        ###############################
        $where = array();

        // Root pid limit
        $where[] = 'page_rootpid = ' . (int)$rootPid;

        // Fulltext
        if (!empty($searchFulltext)) {
            $where[] = 'page_url LIKE ' . $TYPO3_DB->fullQuoteStr('%' . $searchFulltext . '%', 'tx_tqseo_sitemap');
        }

        // Page id
        if (!empty($searchPageUid)) {
            $where[] = 'page_uid = ' . (int)$searchPageUid;
        }

        // Lannguage
        if ($searchPageLanguage != -1 && strlen($searchPageLanguage) >= 1) {
            $where[] = 'page_language = ' . (int)$searchPageLanguage;
        }

        // Depth
        if ($searchPageDepth != -1 && strlen($searchPageDepth) >= 1) {
            $where[] = 'page_depth = ' . (int)$searchPageDepth;
        }

        if ($searchIsBlacklisted) {
            $where[] = 'is_blacklisted = 1';
        }

        // Build where
        $where = '( ' . implode(' ) AND ( ', $where) . ' )';

        ###############################
        # Pager
        ###############################

        // Fetch total count of items with this filter settings
        $res       = $TYPO3_DB->exec_SELECTquery(
            'COUNT(*) as count',
            'tx_tqseo_sitemap',
            $where
        );
        $row       = $TYPO3_DB->sql_fetch_assoc($res);
        $itemCount = $row['count'];

        ###############################
        # Sort
        ###############################
        // default sort
        $sort = 'page_depth ASC, page_uid ASC';

        if (!empty($this->_sortField) && !empty($this->_sortDir)) {
            // already filered
            $sort = $this->_sortField . ' ' . $this->_sortDir;
        }

        ###############################
        # Fetch sitemap
        ###############################
        $list = $TYPO3_DB->exec_SELECTgetRows(
            'uid,
             page_rootpid,
             page_uid,
             page_language,
             page_url,
             page_depth,
             is_blacklisted,
             FROM_UNIXTIME(tstamp) as tstamp,
             FROM_UNIXTIME(crdate) as crdate',
            'tx_tqseo_sitemap',
            $where,
            '',
            $sort,
            $offset . ', ' . $itemsPerPage
        );

        $ret = array(
            'results' => $itemCount,
            'rows'    => $list,
        );

        return $ret;
    }

    /*
     * Blacklist sitemap entries
     *
     * @return    boolean
     */
    protected function _executeBlacklist() {
        global $TYPO3_DB;

        $ret = false;

        $uidList = $this->_postVar['uidList'];
        $rootPid = (int)$this->_postVar['pid'];

        $uidList = $TYPO3_DB->cleanIntArray($uidList);

        if (empty($uidList) || empty($rootPid)) {
            return false;
        }

        $where   = array();
        $where[] = 'page_rootpid = ' . (int)$rootPid;
        $where[] = 'uid IN (' . implode(',', $uidList) . ')';
        $where   = '( ' . implode(' ) AND ( ', $where) . ' )';

        $res = $TYPO3_DB->exec_UPDATEquery(
            'tx_tqseo_sitemap',
            $where,
            array(
                'is_blacklisted' => 1
            )
        );

        if ($res) {
            $ret = true;
        }

        return $ret;
    }

    /*
     * Whitelist sitemap entries
     *
     * @return    boolean
     */
    protected function _executeWhitelist() {
        global $TYPO3_DB;

        $ret = false;

        $uidList = $this->_postVar['uidList'];
        $rootPid = (int)$this->_postVar['pid'];

        $uidList = $TYPO3_DB->cleanIntArray($uidList);

        if (empty($uidList) || empty($rootPid)) {
            return false;
        }

        $where   = array();
        $where[] = 'page_rootpid = ' . (int)$rootPid;
        $where[] = 'uid IN (' . implode(',', $uidList) . ')';
        $where   = '( ' . implode(' ) AND ( ', $where) . ' )';

        $res = $TYPO3_DB->exec_UPDATEquery(
            'tx_tqseo_sitemap',
            $where,
            array(
                'is_blacklisted' => 0
            )
        );

        if ($res) {
            $ret = true;
        }

        return $ret;
    }



    /**
     * Delete sitemap entries
     *
     * @return    boolean
     */
    protected function _executeDelete() {
        global $TYPO3_DB;

        $ret = false;

        $uidList = $this->_postVar['uidList'];
        $rootPid = (int)$this->_postVar['pid'];

        $uidList = $TYPO3_DB->cleanIntArray($uidList);

        if (empty($uidList) || empty($rootPid)) {
            return false;
        }

        $where   = array();
        $where[] = 'page_rootpid = ' . (int)$rootPid;
        $where[] = 'uid IN (' . implode(',', $uidList) . ')';
        $where   = '( ' . implode(' ) AND ( ', $where) . ' )';

        $res = $TYPO3_DB->exec_DELETEquery(
            'tx_tqseo_sitemap',
            $where
        );

        if ($res) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Delete all sitemap entries
     *
     * @return    boolean
     */
    protected function _executeDeleteAll() {
        global $TYPO3_DB;

        $ret = false;

        $rootPid = (int)$this->_postVar['pid'];

        if( empty($rootPid) ) {
            return false;
        }

        $where   = array();
        $where[] = 'page_rootpid = ' . (int)$rootPid;
        $where   = '( ' . implode(' ) AND ( ', $where) . ' )';

        $res = $TYPO3_DB->exec_DELETEquery(
            'tx_tqseo_sitemap',
            $where
        );

        if ($res) {
            $ret = true;
        }

        return $ret;
    }

}

?>