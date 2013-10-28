<?php
namespace TQ\TqSeo\Backend\Module;

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
 * TYPO3 Backend module base
 *
 * @author      TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package     TYPO3
 * @subpackage  tq_seo
 */
abstract class AbstractModule extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    // ########################################################################
    // Attributes
    // ########################################################################

    /**
     * Backend Form Protection object
     *
     * @var \TYPO3\CMS\Core\FormProtection\BackendFormProtection
     */
    protected $_formProtection = NULL;

    // ########################################################################
    // Methods
    // ########################################################################

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction() {

        // Init form protection instance
        $this->_formProtection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\FormProtection\\BackendFormProtection'
        );

    }

    /**
     * Translate key
     *
     * @param   string      $key        Translation key
     * @param   NULL|array  $arguments  Arguments (vsprintf)
     * @return  NULL|string
     */
    protected function _translate($key, $arguments = NULL) {
        $ret = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $this->extensionName, $arguments);

        // Not translated handling
        if( $ret === NULL ) {
            $ret = '[-'.$key.'-]';
        }

        return $ret;
    }

    /**
     * Create session token
     *
     * @param    string $formName    Form name/Session token name
     * @return    string
     */
    protected function _sessionToken($formName) {
        $token = $this->_formProtection->generateToken($formName);
        return $token;
    }

}

?>