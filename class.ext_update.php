<?php

use TYPO3\CMS\Core\Messaging\FlashMessage;

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
 * Update class for the extension manager.
 *
 * @package    TYPO3
 * @subpackage tq_seo
 */
class ext_update {

    // ########################################################################
    // Attributs
    // ########################################################################

    /**
     * Message list
     *
     * @var array
     */
    protected $_messageList = array();

    /**
     * Clear cache (after update)
     *
     * @var boolean
     */
    protected $_clearCache = FALSE;

    // ########################################################################
    // Methods
    // ########################################################################

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main() {
        $this->_processUpdates();

        $ret = $this->_generateOutput();

        return $ret;
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     * @todo find a better way to determine if update is needed or not.
     */
    public function access() {
        return TRUE;
    }


    /**
     * The actual update function. Add your update task in here.
     */
    protected function _processUpdates() {
        $this->_processUpdateTypoScriptIncludes();
        $this->_processUpdateScheduler();

        $this->_processClearCache();
    }

    /**
     * Update TYPO3 TypoScript includes
     */
    protected function _processUpdateTypoScriptIncludes() {
        $msgTitle  = 'Update TypoScript includes';
        $msgStatus = FlashMessage::INFO;
        $msgText   = array();

        // Build condition
        $condition = array(
            'include_static_file LIKE \'%EXT:tq_seo/static/default/%\'',
        );
        $condition = implode(' OR ', $condition);
        $updateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('*', 'sys_template', $condition);

        if( $updateCount > 0 ) {

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_template', $condition);
            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                // Update data
                $updateData = array();

                // Migrate old typoscript paths
                if( strpos($row['include_static_file'], 'EXT:tq_seo/static/default/') !== FALSE ) {
                    $updateData['include_static_file'] = str_replace(
                        'EXT:tq_seo/static/default/',
                        'EXT:tq_seo/Configuration/TypoScript',
                        $row['include_static_file']
                    );
                }

                if( !empty($updateData) ) {
                    // Update task with update data
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_template', 'uid='.(int)$row['uid'], $updateData);
                    $msgText[] = 'TypoScript template '.$this->_messageTitleFromRow($row).' updated';
                    $msgStatus = FlashMessage::OK;
                }
            }

        } else {
            $msgText = 'Already up-to-date';
        }

        if( empty($msgText) ) {
            // Maybe the soft-condition failed
            $msgText = 'Already up-to-date';
        }
        $this->_addMessage($msgStatus, $msgTitle, $msgText);
    }

    /**
     * Update TYPO3 scheduler tasks
     */
    protected function _processUpdateScheduler() {
        $msgTitle  = 'Update scheduler tasks';
        $msgStatus = FlashMessage::INFO;
        $msgText   = array();

        // Build condition
        $condition = array(
            'serialized_task_object LIKE \'%tx_tqseo_scheduler_task_cleanup%\'',
            'serialized_task_object LIKE \'%tx_tqseo_scheduler_task_sitemap_xml%\'',
            'serialized_task_object LIKE \'%tx_tqseo_scheduler_task_sitemap_txt%\'',
        );
        $condition = implode(' OR ', $condition);
        $updateCount = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('*', 'tx_scheduler_task', $condition);

        if( $updateCount > 0 ) {

            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_scheduler_task', $condition);
            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                // Update data
                $updateData = array();

                // Migrate garbage collection task
                if( strpos($row['serialized_task_object'], 'O:31:"tx_tqseo_scheduler_task_cleanup":') !== FALSE ) {
                    $updateData['serialized_task_object'] = str_replace(
                        'O:31:"tx_tqseo_scheduler_task_cleanup":',
                        'O:45:"TQ\\TqSeo\\Scheduler\\Task\\GarbageCollectionTask":',
                        $row['serialized_task_object']
                    );
                }

                // Migrate sitemap xml task
                if( strpos($row['serialized_task_object'], 'O:35:"tx_tqseo_scheduler_task_sitemap_xml":') !== FALSE ) {
                    $updateData['serialized_task_object'] = str_replace(
                        'O:35:"tx_tqseo_scheduler_task_sitemap_xml":',
                        'O:38:"TQ\\TqSeo\\Scheduler\\Task\\SitemapXmlTask":',
                        $row['serialized_task_object']
                    );
                }

                // Migrate sitemap txt task
                if( strpos($row['serialized_task_object'], 'O:35:"tx_tqseo_scheduler_task_sitemap_txt":') !== FALSE ) {
                    $updateData['serialized_task_object'] = str_replace(
                        'O:35:"tx_tqseo_scheduler_task_sitemap_txt":',
                        'O:38:"TQ\\TqSeo\\Scheduler\\Task\\SitemapTxtTask":',
                        $row['serialized_task_object']
                    );
                }

                if( !empty($updateData) ) {
                    // Update task with update data
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_scheduler_task', 'uid='.(int)$row['uid'], $updateData);
                    $msgText[] = 'Scheduler Task '.$this->_messageTitleFromRow($row).' updated';
                    $msgStatus = FlashMessage::OK;
                }
            }
        } else {
            $msgText = 'Already up-to-date';
        }

        if( empty($msgText) ) {
            // Maybe the soft-condition failed
            $msgText = 'Already up-to-date';
        }

        $this->_addMessage($msgStatus, $msgTitle, $msgText);
    }

    /**
     * Clear cache
     */
    protected function _processClearCache() {

        if( $this->_clearCache ) {

            // Init TCE
            $TCE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
            $TCE->admin = 1;
            $TCE->clear_cacheCmd('all');

            // Add msg
            $msgTitle  = 'Clearing TYPO3 cache';
            $msgStatus = FlashMessage::INFO;
            $msgText   = 'Cleared all caches due migration';

            $this->_addMessage($msgStatus, $msgTitle, $msgText);
        }
    }

    /**
     * Add message
     *
     * @param integer $status   Status code
     * @param string  $title    Title
     * @param string  $message  Message
     */
    protected function _addMessage($status, $title, $message) {
        if( !empty($message) && is_array($message) ) {
            $liStyle = 'style="margin-bottom: 0;"';

            $message = '<ul><li '.$liStyle.'>'.implode('</li><li '.$liStyle.'>', $message).'</li></ul>';
        }

        $this->_messageList[] = array($status, $title, $message);
    }

    /**
     * Generate message title from database row (using title and uid)
     *
     * @param   array   $row    Database row
     * @return  string
     */
    protected function _messageTitleFromRow($row) {
        $ret = array();

        if( !empty($row['title']) ) {
            $ret[] = '"'.htmlspecialchars($row['title']).'"';
        }

        if( !empty($row['uid']) ) {
            $ret[] = '[UID #'.htmlspecialchars($row['uid']).']';
        }

        return implode(' ', $ret);
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function _generateOutput() {
        $output = '';

        foreach ($this->_messageList as $message) {
            $flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
                $message[2],
                $message[1],
                $message[0]);
            $output .= $flashMessage->render();
        }

        return $output;
    }

}
