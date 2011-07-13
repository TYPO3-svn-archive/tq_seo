<?php
$extensionPath = t3lib_extMgm::extPath('tq_seo');

return array(
	'tx_tqseo_cache'						=> $extensionPath.'lib/class.cache.php',
	'tx_tqseo_tools'						=> $extensionPath.'lib/class.tools.php',
	'tx_tqseo_robots_txt'					=> $extensionPath.'lib/class.robots_txt.php',
	'tx_tqseo_sitemap_base'					=> $extensionPath.'lib/sitemap/class.sitemap_base.php',
	'tx_tqseo_sitemap_txt'					=> $extensionPath.'lib/sitemap/class.sitemap_txt.php',
	'tx_tqseo_sitemap_xml'					=> $extensionPath.'lib/sitemap/class.sitemap_xml.php',
);

?>