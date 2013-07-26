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
 * Scheduler Task Sitemap XML
 *
 * @author        Blaschke, Markus <blaschke@teqneers.de>
 * @package    tq_seo
 * @subpackage    lib
 * @version        $Id$
 */
class SitemapXmlTask extends \TQ\TqSeo\Scheduler\Task\AbstractSitemapTask {

    ###########################################################################
    # Attributes
    ###########################################################################

    /**
     * Sitemap base directory
     *
     * @var string
     */
    protected $_sitemapDir = 'uploads/tx_tqseo/sitemap_xml';

    ###########################################################################
    # Methods
    ###########################################################################

    /**
     * Build sitemap
     *
     * @param   integer $rootPageId Root page id
     * @param   integer $languageId Language id
     * @return  boolean
     */
    protected function _buildSitemap($rootPageId, $languageId) {
        global $TSFE;

        if ($languageId !== null) {
            // Language lock enabled
            $rootPageLinkTempalte = 'sitemap-r%s-l%s-p###PAGE###.xml.gz';
            $sitemapIndexFileName = 'index-r%s-l%s.xml.gz';
            $sitemapPageFileName  = 'sitemap-r%s-l%s-p%s.xml.gz';
        } else {
            $rootPageLinkTempalte = 'sitemap-r%s-p###PAGE###.xml.gz';
            $sitemapIndexFileName = 'index-r%s.xml.gz';
            $sitemapPageFileName  = 'sitemap-r%s-p%3$s.xml.gz';
        }

        // Init builder
        $generator                    = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TQ\\TqSeo\\Sitemap\\Generator\\XmlGenerator'
        );
        $fileName                     = sprintf($rootPageLinkTempalte, $rootPageId, $languageId);
        $generator->indexPathTemplate = $this->_generateSitemapLinkTemplate($fileName);

        // Get list of pages
        $pageCount = $generator->pageCount();

        // Index
        $content  = $generator->sitemapIndex();
        $fileName = sprintf($sitemapIndexFileName, $rootPageId, $languageId);
        $this->_writeToFile(PATH_site . '/' . $this->_sitemapDir . '/' . $fileName, $content);

        // Page
        for ($i = 0; $i < $pageCount; $i++) {
            $content  = $generator->sitemap($i);
            $fileName = sprintf($sitemapPageFileName, $rootPageId, $languageId, $i);
            $this->_writeToFile(PATH_site . '/' . $this->_sitemapDir . '/' . $fileName, $content);
        }

        return true;
    }

}