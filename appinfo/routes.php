<?php

/**
 * ownCloud - Ooba App
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Ooba\AppInfo;

use OCP\API;

// Register an OCS API call
API::register(
	'get',
	'/cloud/ooba',
	array('OCA\Ooba\Api', 'get'),
	'ooba'
);

$application = new Application();
$application->registerRoutes($this, ['routes' => [
	['name' => 'Settings#personal', 'url' => '/settings', 'verb' => 'POST'],
	['name' => 'Settings#feed', 'url' => '/settings/feed', 'verb' => 'POST'],
	//['name' => 'Oobas#showList', 'url' => '/', 'verb' => 'GET'],
	['name' => 'ActivitiesExtension#showList', 'url' => '/', 'verb' => 'GET'], //preserving original activity by inheritance
	//['name' => 'Oobas#fetch', 'url' => '/oobas/fetch', 'verb' => 'GET'],
	['name' => 'ActivitiesExtension#fetch', 'url' => '/oobas/fetch', 'verb' => 'GET'], //preserving original activity by inheritance
	['name' => 'ActivitiesExtension#showSyslog', 'url' => '/', 'verb' => 'GET'], //ooba extension
	['name' => 'Feed#show', 'url' => '/rss.php', 'verb' => 'GET'],
]]);
