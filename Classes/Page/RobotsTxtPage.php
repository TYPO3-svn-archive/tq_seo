<?php
namespace TQ\TqSeo\Page;

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
 * Robots txt Page
 *
 * @author      Blaschke, Markus <blaschke@teqneers.de>
 * @package     tq_seo
 * @subpackage  Page
 * @version     $Id$
 */
class RobotsTxtPage extends \TQ\TqSeo\Page\AbstractPage {

    // ########################################################################
    // Attributes
    // ########################################################################


    // ########################################################################
    // Methods
    // ########################################################################

    /**
     * Fetch and build robots.txt
     */
    public function main() {
        $settings = \TQ\TqSeo\Utility\GeneralUtility::getRootSetting();

        // INIT
        $tsSetup  = $GLOBALS['TSFE']->tmpl->setup;
        $cObj     = $GLOBALS['TSFE']->cObj;
        $tsfePage = $GLOBALS['TSFE']->page;
        $rootPid  = \TQ\TqSeo\Utility\GeneralUtility::getRootPid();
        $ret      = '';

        $tsSetupSeo = NULL;
        if (!empty($tsSetup['plugin.']['tq_seo.']['robotsTxt.'])) {
            $tsSetupSeo = $tsSetup['plugin.']['tq_seo.']['robotsTxt.'];
        }

        if (!empty($tsSetup['plugin.']['tq_seo.']['sitemap.'])) {
            $tsSetupSeoSitemap = $tsSetup['plugin.']['tq_seo.']['sitemap.'];
        }

        // check if sitemap is enabled in root
        if (!\TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue('is_robotstxt', TRUE)) {
            return TRUE;
        }

        $linkToStaticSitemap = \TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue(
            'is_robotstxt_sitemap_static',
            FALSE
        );

        // Language lock
        $sitemapLanguageLock = \TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue('is_sitemap_language_lock', FALSE);
        $languageId          = \TQ\TqSeo\Utility\GeneralUtility::getLanguageId();

        // ###############################
        // Fetch robots.txt content
        // ###############################
        $settings['robotstxt'] = trim($settings['robotstxt']);

        if (!empty($settings['robotstxt'])) {
            // Custom Robots.txt
            $ret .= $settings['robotstxt'];

        } elseif ($tsSetupSeo) {
            // Default robots.txt
            $ret .= $cObj->cObjGetSingle($tsSetupSeo['default'], $tsSetupSeo['default.']);
        }

        // ###############################
        // Fetch extra robots.txt content
        // ###############################
        // User additional
        if (!empty($settings['robotstxt_additional'])) {
            $ret .= "\n\n" . $settings['robotstxt_additional'];
        }

        // Setup additional
        if ($tsSetupSeo) {
            // Default robots.txt
            $tmp = $cObj->cObjGetSingle($tsSetupSeo['extra'], $tsSetupSeo['extra.']);

            if (!empty($tmp)) {
                $ret .= "\n\n" . $tmp;
            }
        }

        // ###############################
        // Marker
        // ###############################
        if (!empty($tsSetupSeo['marker.'])) {
            // Init marker list
            $markerList     = array();
            $markerConfList = array();

            foreach ($tsSetupSeo['marker.'] as $name => $data) {
                if (strpos($name, '.') === FALSE) {
                    $markerConfList[$name] = NULL;
                }
            }

            if ($linkToStaticSitemap) {
                // remove sitemap-marker because we link to static url
                unset($markerConfList['sitemap']);
            }

            // Fetch marker content
            foreach ($markerConfList as $name => $conf) {
                $markerList['%' . $name . '%'] = $cObj->cObjGetSingle(
                    $tsSetupSeo['marker.'][$name],
                    $tsSetupSeo['marker.'][$name . '.']
                );
            }

            // generate sitemap-static marker
            if ($linkToStaticSitemap) {
                if ($sitemapLanguageLock) {
                    $path = 'uploads/tx_tqseo/sitemap_xml/index-r' . (int)$rootPid . '-l' . (int)$languageId . '.xml.gz';
                } else {
                    $path = 'uploads/tx_tqseo/sitemap_xml/index-r' . (int)$rootPid . '.xml.gz';
                }

                $conf = array(
                    'parameter' => $path
                );

                $markerList['%sitemap%'] = $cObj->typolink_URL($conf);
            }

            // Fix sitemap-marker url (add prefix if needed)
            $markerList['%sitemap%'] = \TQ\TqSeo\Utility\GeneralUtility::fullUrl($markerList['%sitemap%']);

            // Call hook
            \TQ\TqSeo\Utility\GeneralUtility::callHook('robotstxt-marker', $this, $markerList);

            // Apply marker list
            if (!empty($markerList)) {
                $ret = strtr($ret, $markerList);
            }
        }

        // Call hook
        \TQ\TqSeo\Utility\GeneralUtility::callHook('robotstxt-output', $this, $ret);

        return $ret;
    }

}
