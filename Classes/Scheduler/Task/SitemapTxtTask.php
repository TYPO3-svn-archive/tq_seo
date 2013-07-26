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
 * Scheduler Task Sitemap TXT
 *
 * @author        Blaschke, Markus <blaschke@teqneers.de>
 * @package    tq_seo
 * @subpackage    lib
 * @version        $Id$
 */
class SitemapTxtTask extends \TQ\TqSeo\Scheduler\Task\AbstractSitemapTask {

    ###########################################################################
    # Attributes
    ###########################################################################

    /**
     * Sitemap base directory
     *
     * @var string
     */
    protected $_sitemapDir = 'uploads/tx_tqseo/sitemap_txt';

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

        if ($languageId !== null) {
            // Language lock enabled
            $sitemapFileName = 'sitemap-r%s-l%s.txt.gz';
        } else {
            $sitemapFileName = 'sitemap-r%s.txt.gz';
        }

        $generator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TQ\\TqSeo\\Sitemap\\Generator\\TxtGenerator'
        );
        $content   = $generator->sitemap();

        $fileName = sprintf($sitemapFileName, $rootPageId, $languageId);
        $this->_writeToFile(PATH_site . '/' . $this->_sitemapDir . '/' . $fileName, $content);

        return true;
    }

}
