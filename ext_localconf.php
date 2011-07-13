<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

#################################################
## SEO
#################################################

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_tqseo_pagetitle,tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,tx_tqseo_canonicalurl';
$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,tx_tqseo_inheritance';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][] = 'EXT:tq_seo/lib/class.linkparser.php:user_tqseo_linkparser->main';

// Caching framework
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'tx_tqseo_cache->clearAll';

// HTTP Header extension
require_once t3lib_extMgm::extPath('tq_seo').'/lib/class.http.php';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['isOutputting'][] = 'user_tqseo_http->main';


#################################################
## SITEMAP
#################################################
// Frontend indexed
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][] = 'EXT:tq_seo/lib/sitemap/class.sitemap_indexer.php:user_tqseo_sitemap_indexer';

// Sitemap control
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions']['clearSeoSitemap']	= 'EXT:tq_seo/hooks/sitemap/class.cache_controller_hook.php:&tx_tqseo_sitemap_cache_controller_hook';

// Sitemal controll ajax
$TYPO3_CONF_VARS['BE']['AJAX']['tx_tqseo_sitemap::clearSeoSitemap'] = 'EXT:tq_seo/hooks/sitemap/class.cache_controller.php:tx_tqseo_sitemap_cache_controller->clearSeoSitemap';

?>