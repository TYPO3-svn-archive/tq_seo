<?php
namespace TQ\TqSeo\Hook\Extension;

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
 * TT_NEWS hook for metatags
 *
 * @author      Blaschke, Markus <blaschke@teqneers.de>
 * @package     tq_seo
 * @subpackage  lib
 * @version     $Id$
 */
class TtnewsExtension {

    /**
     * Extra item marker hook for metatag fetching
     *
     * @param    array $markerArray        Marker array
     * @param    array $row                Current tt_news row
     * @param    array $lConf                Local configuration
     * @param    object $ttnewsObj            Pi-object from tt_news
     * @return    array                        Marker array (not changed)
     */
    public function extraItemMarkerProcessor($markerArray, $row, $lConf, $ttnewsObj) {
        global $TSFE;

        $theCode = (string)strtoupper(trim($ttnewsObj->theCode));

        $connector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TQ\\TqSeo\\Connector');

        switch ($theCode) {
            case 'SINGLE':
            case 'SINGLE2':
                // Title
                if (!empty($row['title'])) {
                    $connector->setMetaTag('title', $row['title']);
                }

                // Description
                if (!empty($row['short'])) {
                    $connector->setMetaTag('description', $row['short']);
                }

                // Keywords
                if (!empty($row['keywords'])) {
                    $connector->setMetaTag('keywords', $row['keywords']);
                }

                // Short/Description
                if (!empty($row['short'])) {
                    $connector->setMetaTag('description', $row['short']);
                }

                // Author
                if (!empty($row['author'])) {
                    $connector->setMetaTag('author', $row['author']);
                }

                // E-Mail
                if (!empty($row['author_email'])) {
                    $connector->setMetaTag('email', $row['author_email']);
                }
                break;
        }

        return $markerArray;
    }
}

?>