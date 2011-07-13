<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Markus Blaschke (TEQneers GmbH & Co. KG) <blaschke@teqneers.de>
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
 * Page Footer
 *
 * @author		Blaschke, Markus <blaschke@teqneers.de>
 * @package 	tq_seo
 * @subpackage	lib
 * @version		$Id$
 */
class user_tqseo_pagefooter {

	/**
	 * Add Page Footer
	 *
	 * @param	string	$title	Default page title (rendered by TYPO3)
	 * @return	string			Modified page title
	 */
	public function main($title) {
		global $TSFE;

		// INIT
		$ret				= array();
		$tsSetup			= $TSFE->tmpl->setup;
		$tsServices			= array();

		$beLoggedIn			= isset($GLOBALS['BE_USER']->user['username']);

		$disabledHeaderCode = false;
		if( !empty($tsSetup['config.']['disableAllHeaderCode']) ) {
			$disabledHeaderCode = true;
		}

		if( !empty($tsSetup['plugin.']['tq_seo.']['services.']) ) {
			$tsServices = $tsSetup['plugin.']['tq_seo.']['services.'];
		}

		#########################################
		# GOOGLE ANALYTICS
		#########################################

		if( !empty($tsServices['googleAnalytics']) ) {
			$gaConf = $tsServices['googleAnalytics.'];

			$gaEnabled = true;

			if( $disabledHeaderCode && empty($gaConf['enableIfHeaderIsDisabled']) ) {
				$gaEnabled = false;
			}

			if( $gaEnabled && !(empty($gaConf['showIfBeLogin']) && $beLoggedIn) ) {
				$tmp = '';

				$tmp .= '<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." :"http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost +"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("'.htmlspecialchars($tsServices['googleAnalytics']).'");';

				if( !empty($gaConf['anonymizeIp']) ) {
					$tmp .= '
_gat._anonymizeIp();';
				}

				if( !empty($gaConf['customizationCode']) ) {
					$tmp .= "\n".$this->cObj->stdWrap($gaConf['customizationCode'], $gaConf['customizationCode.']);
				}

				$tmp .= '
pageTracker._trackPageview();
} catch(err) {}</script>';


				$ret[] = $tmp;


				if( !empty($gaConf['trackDownloads']) && !empty($gaConf['trackDownloadsScript']) ) {
					$jsFile = t3lib_div::getFileAbsFileName($gaConf['trackDownloadsScript']);
					$jsfile = preg_replace('/^'.preg_quote(PATH_site,'/').'/i','',$jsFile);
					$ret[] = '<script type="text/javascript" src="'.htmlspecialchars($jsfile).'"></script>';
				}
			} elseif($gaEnabled && $beLoggedIn) {
				$ret[] = '<!-- Google Analytics disabled - Backend-Login detected -->';
			}
		}


		#########################################
		# PIWIK
		#########################################
		if( !empty($tsServices['piwik.']) && !empty($tsServices['piwik.']['url']) && !empty($tsServices['piwik.']['id']) ) {
			$piwikConf = $tsServices['piwik.'];

			$piwikEnabled = true;

			if( $disabledHeaderCode && empty($piwikConf['enableIfHeaderIsDisabled']) ) {
				$piwikEnabled = false;
			}

			if( $piwikEnabled && !(empty($gaConf['showIfBeLogin']) && $beLoggedIn) ) {
				$tmp = '';

				$tmp .= '
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://'.htmlspecialchars($piwikConf['url']).'/" : "http://'.htmlspecialchars($piwikConf['url']).'/");
document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", '.htmlspecialchars($piwikConf['id']).');
piwikTracker.trackPageView();';

				if( !empty($piwikConf['customizationCode']) ) {
					$tmp .= "\n".$this->cObj->stdWrap($piwikConf['customizationCode'], $piwikConf['customizationCode.']);
				}

				$tmp .= '
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://'.htmlspecialchars($piwikConf['url']).'/piwik.php?idsite='.htmlspecialchars($piwikConf['id']).'" style="border:0" alt="" /></p></noscript>';

				$ret[] = $tmp;
			} elseif($piwikEnabled && $beLoggedIn) {
				$ret[] = '<!-- Piwik disabled - Backend-Login detected -->';
			}
		}

		return implode("\n", $ret);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagefooter.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tq_seo/lib/class.pagefooter.php']);
}
?>