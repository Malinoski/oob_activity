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

require_once 'apps/ooba/extension_ooba/activitydata.php';
require_once 'apps/ooba/extension_ooba/activitylogger.php';

$app = new Application();
$c = $app->getContainer();

// add an navigation entry
$navigationEntry = function () use ($c) {
	return [
		'id' => $c->getAppName(),
		'order' => 1,
		'name' => $c->query('OobaL10N')->t('Ooba'),
		'href' => $c->query('URLGenerator')->linkToRoute('ooba.Oobas.showList'),
		'icon' => $c->query('URLGenerator')->imagePath('ooba', 'ooba.svg'),
	];
};
$c->getServer()->getNavigationManager()->add($navigationEntry);

// register the hooks for filesystem operations. All other events from other apps has to be send via the public api
\OCA\Ooba\FilesHooksStatic::register();
\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Ooba\Hooks', 'deleteUser');
\OCA\Ooba\Consumer::register($c->getServer()->getActivityManager(), $c);

// Personal settings for notifications and emails
\OCP\App::registerPersonal($c->getAppName(), 'personal');
