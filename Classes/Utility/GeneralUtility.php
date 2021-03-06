<?php
namespace TQ\TqSeo\Utility;

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
 * General utility
 *
 * @author      Blaschke, Markus <blaschke@teqneers.de>
 * @package     tq_seo
 * @subpackage  Utility
 * @version     $Id$
 */
class GeneralUtility {

    // ########################################################################
    // Attributes
    // ########################################################################

    /**
     * Page Select
     *
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected static $sysPageObj = NULL;


    // ########################################################################
    // Public methods
    // ########################################################################

    /**
     * Get current language id
     *
     * @return  integer
     */
    public static function getLanguageId() {
        $ret = 0;

        if (!empty($GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'])) {
            $ret = (int)$GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_uid'];
        }

        return $ret;
    }

    /**
     * Get current root pid
     *
     * @param   integer $uid    Page UID
     * @return  integer
     */
    public static function getRootPid($uid = NULL) {
        static $cache = array();
        $ret = NULL;

        if ($uid === NULL) {
            $ret = (int)$GLOBALS['TSFE']->rootLine[0]['uid'];
        } else {

            if (!isset($cache[$uid])) {
                $cache[$uid] = NULL;
                $rootline    = self::getRootLine($uid);

                if (!empty($rootline[0])) {
                    $cache[$uid] = $rootline[0]['uid'];
                }
            }

            $ret = $cache[$uid];
        }

        return $ret;
    }

    /**
     * Get current pid
     *
     * @return  integer
     */
    public static function getCurrentPid() {
        return $GLOBALS['TSFE']->id;
    }

    /**
     * Get current root pid
     *
     * @param   integer $uid    Page UID
     * @return  integer
     */
    public static function getRootLine($uid = NULL) {
        static $cache = array();
        $ret = array();

        if ($uid === NULL) {
            $ret = (int)$GLOBALS['TSFE']->rootLine;
        } else {
            if (!isset($cache[$uid])) {
                $cache[$uid] = self::_getSysPageObj()->getRootLine($uid);
            }

            $ret = $cache[$uid];
        }

        return $ret;
    }

    /**
     * Get domain
     *
     * @return  array
     */
    public static function getSysDomain() {
        static $ret = NULL;

        if ($ret !== NULL) {
            return $ret;
        }

        $ret = array();

        $host = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
        $rootPid = self::getRootPid();

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            'sys_domain',
            'pid = ' . (int)$rootPid . ' AND domainName = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
                $host,
                'sys_domain'
            ) . ' AND hidden = 0'
        );

        if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $ret = $row;
        }

        return $ret;
    }

    /**
     * Get root setting row
     *
     * @param   integer $rootPid    Root Page Id
     * @return  array
     */
    public static function getRootSetting($rootPid = NULL) {
        static $ret = NULL;

        if ($ret !== NULL) {
            return $ret;
        }

        $ret = array();

        if ($rootPid === NULL) {
            $rootPid = self::getRootPid();
        }

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            'tx_tqseo_setting_root',
            'pid = ' . (int)$rootPid,
            '',
            '',
            1
        );

        if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $ret = $row;
        }

        return $ret;
    }

    /**
     * Get root setting value
     *
     * @param   string $name           Name of configuration
     * @param   mixed $defaultValue   Default value
     * @param   integer $rootPid        Root Page Id
     * @return  array
     */
    public static function getRootSettingValue($name, $defaultValue = NULL, $rootPid = NULL) {
        $setting = self::getRootSetting($rootPid);

        if (isset($setting[$name])) {
            $ret = $setting[$name];
        } else {
            $ret = $defaultValue;
        }

        return $ret;
    }

    /**
     * Get extension configuration
     *
     * @param   string $name       Name of config
     * @param   boolean $default    Default value
     * @return  mixed
     */
    public static function getExtConf($name, $default = NULL) {
        static $conf = NULL;
        $ret = $default;

        if ($conf === NULL) {
            // Load ext conf
            $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tq_seo']);
            if (!is_array($conf)) {
                $conf = array();
            }
        }

        if (isset($conf[$name])) {
            $ret = $conf[$name];
        }


        return $ret;
    }

    /**
     * Call hook
     *
     * @param   string $name   Name of hook
     * @param   boolean $obj    Object
     * @param   boolean $args   Args
     * @return  mixed
     */
    public static function callHook($name, $obj, &$args) {
        static $hookConf = NULL;

        // Fetch hooks config for tq_seo, minimize array lookups
        if ($hookConf === NULL) {
            $hookConf = array();
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tq_seo']['hooks'])
                && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tq_seo']['hooks'])
            ) {
                $hookConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tq_seo']['hooks'];
            }
        }

        // Call hooks
        if (!empty($hookConf[$name]) && is_array($hookConf[$name])) {
            foreach ($hookConf[$name] as $_funcRef) {
                if ($_funcRef) {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($_funcRef, $args, $obj);
                }
            }
        }
    }

    /**
     * Full url
     *
     * Make sure the url is absolute (http://....)
     *
     * @param   string $url    URL
     * @return  string
     */
    public static function fullUrl($url) {
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($url);
        }

        // Fix url stuff
        $url = str_replace('?&', '?', $url);

        // Fallback
        //	if( !empty($GLOBALS['TSFE']) && !preg_match('/^https?:\/\//i', $url ) ) {
        //		$url = $GLOBALS['TSFE']->baseUrlWrap($url);
        //	}

        return $url;
    }

    // ########################################################################
    // Protected methods
    // ########################################################################

    /**
     * Get sys page object
     *
     * @return  \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected static function _getSysPageObj() {
        if (self::$sysPageObj === NULL) {
            self::$sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Frontend\\Page\\PageRepository'
            );
        }
        return self::$sysPageObj;
    }

}

