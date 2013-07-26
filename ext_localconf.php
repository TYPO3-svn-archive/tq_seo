<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tq_seo']);

#################################################
## BACKEND
#################################################
if (TYPO3_MODE == 'BE') {
    // AJAX
    $TYPO3_CONF_VARS['BE']['AJAX']['tx_tqseo_backend_ajax::sitemap']    = 'TQ\\TqSeo\\Backend\\Ajax\SitemapAjax->main';
    $TYPO3_CONF_VARS['BE']['AJAX']['tx_tqseo_backend_ajax::page']       = 'TQ\\TqSeo\\Backend\\Ajax\PageAjax->main';

    // Field validations
    $TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_tqseo_backend_validation_float'] = 'EXT:tq_seo/Classes/Backend/Validator/ValidatorImport.php';
}

#################################################
## SEO
#################################################

$TYPO3_CONF_VARS['FE']['pageOverlayFields'] .= ',tx_tqseo_pagetitle,tx_tqseo_pagetitle_rel,tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,tx_tqseo_canonicalurl';
$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,tx_tqseo_inheritance';

//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][] = 'EXT:tq_seo/lib/class.linkparser.php:user_tqseo_linkparser->main';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'][] = 'TQ\\TqSeo\\Hook\\SitemapIndexHook->hook_linkParse';

// Caching framework
################$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'tx_tqseo_cache->clearAll';

// HTTP Header extension
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['isOutputting']['tq_seo'] = 'TQ\\TqSeo\\Hook\\HttpHook->main';


#################################################
## SITEMAP
#################################################
// Frontend indexed
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][] = 'TQ\\TqSeo\\Hook\\SitemapIndexHook';

// Sitemap control
################$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['additionalBackendItems']['cacheActions']['clearSeoSitemap']	= 'EXT:tq_seo/hooks/sitemap/class.cache_controller_hook.php:&tx_tqseo_sitemap_cache_controller_hook';

// Sitemal controll ajax
################$TYPO3_CONF_VARS['BE']['AJAX']['tx_tqseo_sitemap::clearSeoSitemap'] = 'EXT:tq_seo/hooks/sitemap/class.cache_controller.php:tx_tqseo_sitemap_cache_controller->clearSeoSitemap';

#################################################
## TT_NEWS
#################################################
if( !empty($confArr['enableIntegrationTTNews']) ) {
    // Metatag fetch hook
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook']['tqseo'] = 'TQ\\TqSeo\\Hook\Extension\\Ttnews\\MetatagTtnews';
}

#################################################
## SCHEDULER
#################################################

// Cleanup task
$TYPO3_CONF_VARS['SC_OPTIONS']['scheduler']['tasks']['TQ\\TqSeo\\Scheduler\\Task\\GarbageCollectionTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Sitemap garbage collection',
    'description'      => 'Cleanup old sitemap entries'
);

// Sitemap XML task
$TYPO3_CONF_VARS['SC_OPTIONS']['scheduler']['tasks']['TQ\\TqSeo\\Scheduler\\Task\\SitemapXmlTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Sitemap.xml builder',
    'description'      => 'Build sitemap xml as static file (in uploads/tx_tqseo/sitemap-xml/)'
);

// Sitemap TXT task
$TYPO3_CONF_VARS['SC_OPTIONS']['scheduler']['tasks']['TQ\\TqSeo\\Scheduler\\Task\\SitemapTxtTask'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Sitemap.txt builder',
    'description'      => 'Build sitemap txt as static file (in uploads/tx_tqseo/sitemap-txt/)'
);

?>