<?php

########################################################################
# Extension Manager/Repository config file for ext "tq_seo".
#
# Auto generated 01-07-2011 11:05
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TEQneers SEO Enhancements',
	'description' => 'Search Engine Optimization (SEO), Indexed Google-Sitemap (TXT- and XML-Sitemap) for all Extensions (pibase, extbase), Metatags, Canonical-URL, Pagetitle manipulations, Crawler verification, Piwik and Google Analytics support and some more... multi-language- and multi-tree-support',
	'category' => 'misc',
	'shy' => 0,
	'version' => '3.9.92',
	'dependencies' => '',
	'conflicts' => 'tq_seo_sitemap',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'pages,pages_language_overlay',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Markus Blaschke',
	'author_email' => 'blaschke@teqneers.de',
	'author_company' => 'TEQneers GmbH & Co. KG',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
			'tq_seo_sitemap' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:31:{s:9:"ChangeLog";s:4:"95d7";s:10:"README.txt";s:4:"878d";s:16:"ext_autoload.php";s:4:"550a";s:21:"ext_conf_template.txt";s:4:"09c3";s:12:"ext_icon.gif";s:4:"6ce1";s:17:"ext_localconf.php";s:4:"4f36";s:14:"ext_tables.php";s:4:"6b22";s:14:"ext_tables.sql";s:4:"31cb";s:16:"locallang_db.xml";s:4:"a7ed";s:17:"locallang_tca.xml";s:4:"6623";s:7:"tca.php";s:4:"95ea";s:14:"doc/manual.pdf";s:4:"6b9f";s:14:"doc/manual.sxw";s:4:"0385";s:40:"hooks/sitemap/class.cache_controller.php";s:4:"b6d4";s:45:"hooks/sitemap/class.cache_controller_hook.php";s:4:"5b2d";s:27:"hooks/sitemap/locallang.xml";s:4:"0c9f";s:19:"lib/class.cache.php";s:4:"2659";s:18:"lib/class.http.php";s:4:"5366";s:24:"lib/class.linkparser.php";s:4:"a2e1";s:22:"lib/class.metatags.php";s:4:"0067";s:24:"lib/class.pagefooter.php";s:4:"35b6";s:23:"lib/class.pagetitle.php";s:4:"1709";s:24:"lib/class.robots_txt.php";s:4:"e839";s:19:"lib/class.tools.php";s:4:"b67d";s:34:"lib/sitemap/class.sitemap_base.php";s:4:"0e0a";s:37:"lib/sitemap/class.sitemap_indexer.php";s:4:"3162";s:33:"lib/sitemap/class.sitemap_txt.php";s:4:"fcc3";s:33:"lib/sitemap/class.sitemap_xml.php";s:4:"10bc";s:24:"res/ga-track-download.js";s:4:"e80d";s:28:"static/default/constants.txt";s:4:"60b5";s:24:"static/default/setup.txt";s:4:"e6ff";}',
	'suggests' => array(
	),
);

?>