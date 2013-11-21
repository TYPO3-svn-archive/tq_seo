<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$extPath    = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);

// ############################################################################
// TABLES
// ############################################################################

// ################
// Pages
// ################

$tempColumns = array(
    'tx_tqseo_pagetitle'        => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_rel'    => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle_rel',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_prefix' => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle_prefix',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_suffix' => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle_suffix',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_inheritance'      => array(
        'exclude' => 1,
        'label'   => 'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_inheritance',
        'config'  => array(
            'type'     => 'select',
            'items'    => array(
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_inheritance.I.0',
                    0
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_inheritance.I.1',
                    1
                ),
            ),
            'size'     => 1,
            'maxitems' => 1
        )
    ),
    'tx_tqseo_is_exclude'       => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_is_exclude',
        'exclude' => 1,
        'config'  => array(
            'type' => 'check'
        )
    ),
    'tx_tqseo_canonicalurl'     => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_canonicalurl',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
            'wizards'  => Array(
                '_PADDING' => 2,
                'link'     => Array(
                    'type'         => 'popup',
                    'title'        => 'Link',
                    'icon'         => 'link_popup.gif',
                    'script'       => 'browse_links.php?mode=wizard&act=url',
                    'params'       => array(
                        'blindLinkOptions' => 'mail',
                    ),
                    'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                ),
            ),
        )
    ),
    'tx_tqseo_priority'         => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_priority',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'int',
        )
    ),
    'tx_tqseo_change_frequency' => array(
        'exclude' => 1,
        'label'   => 'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency',
        'config'  => array(
            'type'     => 'select',
            'items'    => array(
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.0',
                    0
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.1',
                    1
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.2',
                    2
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.3',
                    3
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.4',
                    4
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.5',
                    5
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.6',
                    6
                ),
                array(
                    'LLL:EXT:tq_seo/locallang_db.php:pages.tx_tqseo_change_frequency.I.7',
                    7
                ),
            ),
            'size'     => 1,
            'maxitems' => 1
        )
    ),
    'tx_tqseo_geo_lat'          => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_geo_lat',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_geo_long'         => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_geo_long',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_geo_place'        => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_geo_place',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_geo_region'       => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_geo_region',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),

);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $tempColumns, 1);

// TCA Palettes
$TCA['pages']['palettes']['tx_tqseo_pagetitle'] = array(
    'showitem'       => 'tx_tqseo_pagetitle,--linebreak--,tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix,--linebreak--,tx_tqseo_inheritance',
    'canNotCollapse' => 1
);

$TCA['pages']['palettes']['tx_tqseo_crawler'] = array(
    'showitem'       => 'tx_tqseo_is_exclude,--linebreak--,tx_tqseo_canonicalurl',
    'canNotCollapse' => 1
);

$TCA['pages']['palettes']['tx_tqseo_sitemap'] = array(
    'showitem'       => 'tx_tqseo_priority,--linebreak--,tx_tqseo_change_frequency',
    'canNotCollapse' => 1
);

$TCA['pages']['palettes']['tx_tqseo_geo'] = array(
    'showitem'       => 'tx_tqseo_geo_lat,--linebreak--,tx_tqseo_geo_long,--linebreak--,tx_tqseo_geo_place,--linebreak--,tx_tqseo_geo_region',
    'canNotCollapse' => 1
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'tx_tqseo_pagetitle_rel',
    '1,4,7,3',
    'after:title'
);

// Put it for standard page
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:tq_seo/locallang_tca.xml:pages.tab.seo;,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.pagetitle;tx_tqseo_pagetitle,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.geo;tx_tqseo_geo,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.crawler;tx_tqseo_crawler,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.sitemap;tx_tqseo_sitemap',
    '1,4,7,3',
    'after:author_email'
);

// ################
// Page overlay (lang)
// ################

$tempColumns = array(
    'tx_tqseo_pagetitle'        => array(
        'exclude' => 1,
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages_language_overlay.tx_tqseo_pagetitle',
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_rel'    => array(
        'exclude' => 1,
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages_language_overlay.tx_tqseo_pagetitle_rel',
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_prefix' => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle_prefix',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_pagetitle_suffix' => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_pagetitle_suffix',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
        )
    ),
    'tx_tqseo_canonicalurl'     => array(
        'label'   => 'LLL:EXT:tq_seo/locallang_db.xml:pages.tx_tqseo_canonicalurl',
        'exclude' => 1,
        'config'  => array(
            'type'     => 'input',
            'size'     => '30',
            'max'      => '255',
            'checkbox' => '',
            'eval'     => 'trim',
            'wizards'  => Array(
                '_PADDING' => 2,
                'link'     => Array(
                    'type'         => 'popup',
                    'title'        => 'Link',
                    'icon'         => 'link_popup.gif',
                    'script'       => 'browse_links.php?mode=wizard&act=url',
                    'params'       => array(
                        'blindLinkOptions' => 'mail',
                    ),
                    'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                ),
            ),
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $tempColumns, 1);

// TCA Palettes
$TCA['pages_language_overlay']['palettes']['tx_tqseo_pagetitle'] = array(
    'showitem'       => 'tx_tqseo_pagetitle,--linebreak--,tx_tqseo_pagetitle_prefix,tx_tqseo_pagetitle_suffix',
    'canNotCollapse' => 1
);

$TCA['pages_language_overlay']['palettes']['tx_tqseo_crawler'] = array(
    'showitem'       => 'tx_tqseo_canonicalurl',
    'canNotCollapse' => 1
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages_language_overlay',
    'tx_tqseo_pagetitle_rel',
    '',
    'after:title'
);

// Put it for standard page overlay
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages_language_overlay',
    '--div--;LLL:EXT:tq_seo/locallang_tca.xml:pages.tab.seo;,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.pagetitle;tx_tqseo_pagetitle,--palette--;LLL:EXT:tq_seo/locallang_tca.xml:pages.palette.crawler;tx_tqseo_crawler',
    '',
    'after:author_email'
);

// ################
// Domains
// ################

/*
$tempColumns = array (
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_domain',$tempColumns,1);
*/

// ################
// Settings Root
// ################
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_tqseo_setting_root');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_tqseo_setting_root');

$TCA['tx_tqseo_setting_root'] = array(
    'ctrl'        => array(
        'title'             => 'LLL:EXT:tq_seo/locallang_db.xml:tx_tqseo_setting_root',
        'label'             => 'uid',
        'adminOnly'         => true,
        'dynamicConfigFile' => $extPath . 'tca.php',
        'iconfile'          => 'page',
        'hideTable'         => true,
        'dividers2tabs'     => true,
    ),
    'feInterface' => array(),
    'interface'   => array(
        'always_description' => true,
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_tqseo_setting_root',
    'EXT:tq_seo/locallang_csh_setting_root.xml'
);

/*
$TCA['tx_tqseo_setting_page'] = array(
	'ctrl' => array(
		'title'				=> 'LLL:EXT:tq_seo/locallang_db.xml:tx_tqseo_setting_page',
		'label'				=> 'uid',
		'adminOnly'			=> 1,
		'dynamicConfigFile'	=> $extPath.'tca.php',
		'iconfile'			=> 'page',
		'hideTable'			=> TRUE,
	),
	'feInterface' => array (
	),
	'interface' => array(
	),
);
*/

// ############################################################################
// Backend
// ############################################################################

if (TYPO3_MODE == 'BE') {

    // ####################################################
    // Module category "WEB"
    // ####################################################

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'TQ.' . $_EXTKEY,
        'web',
        'pageseo',
        '', # Position
        array('BackendPageSeo' => 'main,metadata,geo,searchengines,url,pagetitle,pagetitlesim'), # Controller array
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Backend/Icons/ModuleIcon.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.pageseo.xml',
        )
    );

    // ####################################################
    // Module category "SEO"
    // ####################################################

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        $_EXTKEY,
        'tqseo',
        '',
        '',
        array(),
        array(
            'access' => 'user,group',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.main.xml',
        )
    );


    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'TQ.' . $_EXTKEY,
        'tqseo',
        'controlcenter',
        '',
        array('BackendRootSettings' => 'main'),
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Backend/Icons/ModuleIcon.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.rootsettings.xml',
        )
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'TQ.' . $_EXTKEY,
        'tqseo',
        'sitemap',
        'after:controlcenter',
        array('BackendSitemap' => 'main,sitemap'),
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Backend/Icons/ModuleIcon.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.sitemap.xml',
        )
    );
}


// ############################################################################
// CONFIGURATION
// ############################################################################

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'TEQneers SEO');
