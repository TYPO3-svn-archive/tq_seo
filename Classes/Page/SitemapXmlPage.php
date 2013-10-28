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
 * Sitemap xml page
 *
 * @author      Blaschke, Markus <blaschke@teqneers.de>
 * @package     tq_seo
 * @subpackage  Page
 * @version     $Id: class.robots_txt.php 62700 2012-05-22 15:53:22Z mblaschke $
 */
class SitemapXmlPage extends \TQ\TqSeo\Page\AbstractPage {

    // ########################################################################
    // Attributes
    // ########################################################################


    // ########################################################################
    // Methods
    // ########################################################################

    /**
     * Build sitemap xml
     *
     * @return  string
     */
    public function main() {
        // INIT
        $this->tsSetup = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tq_seo.']['sitemap.'];

        // check if sitemap is enabled in root
        if (!\TQ\TqSeo\Utility\GeneralUtility::getRootSettingValue('is_sitemap', TRUE)) {
            $this->showError('Sitemap is not available, please check your configuration [control-center]');
        }

        $ret = $this->_build();

        return $ret;
    }

    protected function _build() {
        $page = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('page');

        $generator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TQ\\TqSeo\\Sitemap\\Generator\\XmlGenerator'
        );

        if (empty($page) || $page == 'index') {
            return $generator->sitemapIndex();
        } else {
            return $generator->sitemap($page);
        }
    }

}
