<?php

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

namespace OCA\OobActivity\AppInfo;

require_once 'apps/oobactivity/ext/activityhelper.php';
require_once 'apps/oobactivity/ext/activitydata.php';
require_once 'apps/oobactivity/ext/activitylogger.php';

//TODO
//\OCA\OobActivity\ext\ActivityHelper::prepareDb();

$app = new Application();
$c = $app->getContainer();

// add an navigation entry
$navigationEntry = array(
	'id' => $c->getAppName(),	
	'order' => 1,
	'name' => $c->query('ActivityL10N')->t('Activity'),
	'href' => $c->query('URLGenerator')->linkToRoute('oobactivity.Activities.showList'),
	'icon' => $c->query('URLGenerator')->imagePath('oobactivity', 'activity.svg'),
);

// Removido: visualização de atividades
//$c->getServer()->getNavigationManager()->add($navigationEntry);

// register the hooks for filesystem operations. All other events from other apps has to be send via the public api
\OCA\OobActivity\HooksStatic::register();
\OCA\OobActivity\Consumer::register($c->getServer()->getActivityManager(), $c);

// Somente para uso de registro em banco, mas esta pendente:

// Removido: Notificacoes (para registro em log, o servidor log eh responsavel por isto)
// Personal settings for notifications and emails
//\OCP\App::registerPersonal($c->getAppName(), 'personal');

// Removido: Notificacoes (para registro em log, o servidor log eh responsavel por isto)
// Cron job for sending Emails
//\OCP\Backgroundjob::registerJob('OCA\OobActivity\BackgroundJob\EmailNotification');
//\OCP\Backgroundjob::registerJob('OCA\OobActivity\BackgroundJob\ExpireActivities');
