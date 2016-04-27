<?php
/* Copyright (c) 2014, Joas Schilling nickvergessen@owncloud.com
 * This file is licensed under the Affero General Public License version 3
 * or later. See the COPYING-README file. */

/** @var $l OC_L10N */
/** @var $_ array */
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php p($l->t('Ooba feed')); ?></title>
		<language><?php p($_['rssLang']); ?></language>
		<link><?php p($_['rssLink']); ?></link>
		<description><?php p($_['description']); ?></description>
		<pubDate><?php p($_['rssPubDate']); ?></pubDate>
		<lastBuildDate><?php p($_['rssPubDate']); ?></lastBuildDate>
		<atom:link href="<?php p($_['rssLink']); ?>" rel="self" type="application/rss+xml" />
<?php foreach ($_['oobas'] as $ooba) { ?>
		<item>
			<guid isPermaLink="false"><?php p($ooba['ooba_id']); ?></guid>
<?php if (!empty($ooba['subject'])): ?>
			<title><?php p(str_replace("\n", ' ', $ooba['subjectformatted']['full'])); ?></title>
<?php endif; ?>
<?php if (!empty($ooba['link'])): ?>
			<link><?php p($ooba['link']); ?></link>
<?php endif; ?>
<?php if (!empty($ooba['timestamp'])): ?>
			<pubDate><?php p(date('r', $ooba['timestamp'])); ?></pubDate>
<?php endif; ?>
<?php if (!empty($ooba['message'])): ?>
			<description><![CDATA[<?php print_unescaped(str_replace("\n", '<br />', \OCP\Util::sanitizeHTML($ooba['messageformatted']['full']))); ?>]]></description>
<?php endif; ?>
		</item>
<?php } ?>
	</channel>
</rss>
