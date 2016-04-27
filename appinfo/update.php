<?php

$installedVersion = \OC::$server->getConfig()->getAppValue('ooba', 'installed_version');
$connection = \OC::$server->getDatabaseConnection();

if (version_compare($installedVersion, '1.2.2', '<')) {
	$mistakes = [
		['*PREFIX*ooba', 'subjectparams'],
		['*PREFIX*ooba', 'messageparams'],
		['*PREFIX*ooba_mq', 'oobamq_subjectparams'],
	];

	foreach ($mistakes as $entry) {
		list($table, $column) = $entry;

		$numEntries = $connection->executeUpdate(
			'DELETE FROM `' . $table . '` WHERE `' . $column . "` NOT LIKE '%]' AND `" . $column . "` NOT LIKE '%}'"
		);

		\OC::$server->getLogger()->debug('Deleting ' . $numEntries . ' oobas with a broken ' . $column . ' value.', ['app' => 'acitivity']);
	}
}

if (version_compare($installedVersion, '1.2.2', '<')) {
	$connection->executeUpdate('UPDATE `*PREFIX*ooba` SET `app` = ? WHERE `type` = ?', array('files_sharing', 'shared'));
	$connection->executeUpdate('UPDATE `*PREFIX*ooba_mq` SET `oobamq_appid` = ? WHERE `oobamq_type` = ?', array('files_sharing', 'shared'));
}

// Cron job for sending emails and pruning the ooba list
\OC::$server->getJobList()->add('OCA\Ooba\BackgroundJob\EmailNotification');
\OC::$server->getJobList()->add('OCA\Ooba\BackgroundJob\ExpireOobas');
