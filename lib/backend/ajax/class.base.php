<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 TEQneers GmbH & Co. KG <info@teqneers.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

/**
 * TYPO3 Backend ajax module base
 *
 * @author		TEQneers GmbH & Co. KG <info@teqneers.de>
 * @package		TYPO3
 * @subpackage	tx_seo
 */
class tx_tqseo_backend_ajax_base {

	###########################################################################
	# Attributes
	###########################################################################

	/**
	 * POST vars (transformed from json)
	 *
	 * @var array
	 */
	protected $_postVar = array();

	/**
	 * Sorting field
	 */
	protected $_sortField = null;

	/**
	 * Sorting dir
	 *
	 * @var string
	 */
	protected $_sortDir	= null;

	###########################################################################
	# Methods
	###########################################################################

	/**
	 * Execute ajax call
	 */
	public function main() {
		$ret = null;


		// Try to find method
		$function = '';
		if( !empty($_GET['cmd']) ) {
			// GET-param
			$function = (string)$_GET['cmd'];

			// security
			$function = strtolower( trim($function) );
			$function = preg_replace('[^a-z]', '' , $function);
		}

		// Call function
		if( !empty($function) ) {
			$method = '_execute'.$function;
			$call	= array($this, $method);

			$this->_fetchParams();

			if(	is_callable($call) ) {
				$ret = $this->$method();
			}
		}

		// Output json data
		header('Content-type: application/json');
		echo json_encode($ret);
		exit;
	}

	/**
	 * Collect and process POST vars and stores them into $this->_postVars
	 */
	protected function _fetchParams() {
		$rawPostVarList = t3lib_div::_POST();
		foreach($rawPostVarList as $key => $value) {
			$this->_postVar[$key] = json_decode($value);
		}

		// Sorting data
		if( !empty($rawPostVarList['sort']) ) {
			$this->_sortField = $this->_escapeSortField( (string)$rawPostVarList['sort'] );
		}

		if( !empty($rawPostVarList['dir']) ) {
			switch( strtoupper($rawPostVarList['dir']) ) {
				case 'ASC':
					$this->_sortDir = 'ASC';
					break;

				case 'DESC':
					$this->_sortDir = 'DESC';
					break;


			}
		}

	}

	/**
	 * Escape for sql sort fields
	 *
	 * @param	string	$value	Sort value
	 * @return	string
	 */
	protected function _escapeSortField($value) {
		return preg_replace('[^_a-zA-Z]', '', $value);
	}

}

?>