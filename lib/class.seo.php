<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
 * SEO Access Class
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
class tx_tqseo {

	###########################################################################
	# Attributes
	###########################################################################

	/**
	 * Data store
	 *
	 * @var array
	 */
	protected static $_store = array(
		'meta'		=> array(),
		'custom'	=> array(),
	);


	###########################################################################
	# Public methods
	###########################################################################

	/**
	 * Set meta tag
	 *
	 * @param	string	$key	Metatag name
	 * @param	string	$value	Metatag value
	 */
	public static function setMeta($key, $value) {
		$key	= (string)$key;
		$value	= (string)$value;

		self::$_store['meta'][$key] = $value;
	}

	/**
	 * Set meta tag
	 *
	 * @param	string	$key	Metatag name
	 * @param	string	$value	Metatag value
	 */
	public static function setCustomMetaTag($key, $value) {
		$key	= (string)$key;
		$value	= (string)$value;

		self::$_store['custom'][$key] = $value;
	}

	/**
	 * Disable meta tag
	 *
	 * @param	string	$key	Metatag name
	 */
	public static function disableMeta($key) {
		$key	= (string)$key;

		self::$_store['meta'][$key] = null;
	}

	/**
	 * Get store
	 *
	 * @return array
	 */
	public static function getStore() {
		return self::$_store;
	}

}

?>