<?php
$extensionPath = t3lib_extMgm::extPath('tq_seo');

return array(
	'tx_tqseo_cache'						=> $extensionPath.'lib/class.cache.php',
	'tx_tqseo_tools'						=> $extensionPath.'lib/class.tools.php',
	'tx_tqseo_robots_txt'					=> $extensionPath.'lib/class.robots_txt.php',
	'tx_tqseo_sitemap'						=> $extensionPath.'lib/sitemap/class.sitemap.php',

	'tx_tqseo_sitemap_builder_base'			=> $extensionPath.'lib/sitemap/builder/class.base.php',
	'tx_tqseo_sitemap_builder_txt'			=> $extensionPath.'lib/sitemap/builder/class.txt.php',
	'tx_tqseo_sitemap_builder_xml'			=> $extensionPath.'lib/sitemap/builder/class.xml.php',

	'tx_tqseo_sitemap_output_base'			=> $extensionPath.'lib/sitemap/output/class.base.php',
	'tx_tqseo_sitemap_output_txt'			=> $extensionPath.'lib/sitemap/output/class.txt.php',
	'tx_tqseo_sitemap_output_xml'			=> $extensionPath.'lib/sitemap/output/class.xml.php',

	'tx_tqseo_scheduler_task_cleanup'		=> $extensionPath.'lib/scheduler/class.cleanup.php',
	'tx_tqseo_scheduler_task_sitemap_base'	=> $extensionPath.'lib/scheduler/class.sitemap_base.php',
	'tx_tqseo_scheduler_task_sitemap_txt'	=> $extensionPath.'lib/scheduler/class.sitemap_txt.php',
	'tx_tqseo_scheduler_task_sitemap_xml'	=> $extensionPath.'lib/scheduler/class.sitemap_xml.php',
);

?>