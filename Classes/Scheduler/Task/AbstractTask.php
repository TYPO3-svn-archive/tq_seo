<?php
namespace TQ\TqSeo\Scheduler\Task;

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
 * Scheduler Task Sitemap Base
 *
 * @author      Blaschke, Markus <blaschke@teqneers.de>
 * @package     tq_seo
 * @subpackage  Sitemap
 * @version     $Id$
 */
abstract class AbstractTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    ###########################################################################
    # Attributes
    ###########################################################################

    /**
     * Language lock
     *
     * @var integer
     */
    protected $_languageLock = false;

    /**
     * Language list
     *
     * @var array
     */
    protected $_languageIdList = null;

    ###########################################################################
    # Methods
    ###########################################################################

    /**
     * Get list of root pages in current typo3
     *
     * @return  array
     */
    protected function _getRootPages() {
        global $TYPO3_DB;

        $ret = array();

        $query = 'SELECT uid
                    FROM pages
                   WHERE is_siteroot = 1
                      AND deleted = 0';
        $res   = $TYPO3_DB->sql_query($query);

        while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
            $uid       = $row['uid'];
            $ret[$uid] = $row;
        }

        return $ret;
    }


    /**
     * Get list of root pages in current typo3
     *
     * @return  array
     */
    protected function _initLanguages() {
        global $TYPO3_DB;

        $this->_languageIdList[0] = 0;

        $query = 'SELECT uid
                    FROM sys_language
                   WHERE hidden = 0';
        $res   = $TYPO3_DB->sql_query($query);

        while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
            $uid                         = $row['uid'];
            $this->_languageIdList[$uid] = $uid;
        }
    }

    /**
     * Set root page language
     */
    protected function _setRootPageLanguage($languageId) {
        global $TSFE;

        $TSFE->tmpl->setup['config.']['sys_language_uid'] = $languageId;
        $this->_languageLock                              = $languageId;
    }

    /**
     * Initalize root page (TSFE and stuff)
     *
     * @param   integer $rootPageId $rootPageId
     */
    protected function _initRootPage($rootPageId) {
        global $TT, $TSFE;

        $TT   = null;
        $TSFE = null;

        $TT = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TimeTracker\\NullTimeTracker');

        $TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
            $GLOBALS['TYPO3_CONF_VARS'],
            $rootPageId,
            0
        );
        $TSFE->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\Page\\PageRepository'
        );
        $TSFE->sys_page->init(true);
        $TSFE->initTemplate();
        $TSFE->rootLine = $TSFE->sys_page->getRootLine($rootPageId, '');
        $TSFE->getConfigArray();
        $TSFE->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'
        );

        // TSFE Init
        if (!empty($TSFE->config['config']['baseURL'])) {
            $TSFE->baseUrl = $TSFE->config['config']['baseURL'];
        }

        if (!empty($TSFE->config['config']['absRefPrefix'])) {
            $TSFE->absRefPrefix = $TSFE->config['config']['absRefPrefix'];
        }
    }

    /**
     * Write content to file
     *
     * @param   string $file       Filename/path
     * @param   string $content    Content
     */
    protected function _writeToFile($file, $content) {
        if (!function_exists('gzopen')) {
            throw new \Exception('tq_seo needs zlib support');
        }

        $fp = gzopen($file, 'w');

        if ($fp) {
            gzwrite($fp, $content);
            gzclose($fp);
        } else {
            throw new \Exception('Could not open ' . $file . ' for writing');
        }

    }

}
