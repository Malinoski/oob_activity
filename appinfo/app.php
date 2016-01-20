<?php

use OCA\Activity\LogActivity;
use Doctrine\Common\Collections\Expr\Value;
/**
 * ownCloud - Activity App
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

require_once 'apps/activity/lib/db/ActivityDBHelper.php';
require_once 'apps/activity/lib/log.php';
require_once 'apps/activity/config/config.php';

//load log file configuration from /activity/config/config.php
OCP\Config::setAppValue('activity', 'activityLog', $ACTIVITY_CONFIG['activityLog']);
OCP\Config::setAppValue('activity', 'logFilePath', $ACTIVITY_CONFIG['logFilePath']);

//load database configuration from /activity/config/config.php
// OCP\Config::setAppValue('activity', 'activityDb', $ACTIVITY_CONFIG['activityDb']);
OCP\Config::setAppValue('activity', 'db_host',    $ACTIVITY_CONFIG['db_host']);
OCP\Config::setAppValue('activity', 'db_user',    $ACTIVITY_CONFIG['db_user']);
OCP\Config::setAppValue('activity', 'db_password',$ACTIVITY_CONFIG['db_password']);
OCP\Config::setAppValue('activity', 'db_name',    $ACTIVITY_CONFIG['db_name']);

ActivityDBHelper::prepareApp();	

$l = OC_L10N::get('activity');

// add an navigation entry
OCP\App::addNavigationEntry(array(
	'id' => 'activity',
	'order' => 1,
	'href' => OCP\Util::linkToRoute('activity.index'),
	'icon' => OCP\Util::imagePath('activity', 'activity.svg'),
	'name' => $l->t('Activity'),
));

// register the hooks for filesystem operations. All other events from other apps has to be send via the public api
OCA\Activity\Hooks::register();

// Personal settings for notifications and emails
OCP\App::registerPersonal('activity', 'personal');

// Cron job for sending Emails
OCP\Backgroundjob::registerJob('OCA\Activity\BackgroundJob\EmailNotification');
