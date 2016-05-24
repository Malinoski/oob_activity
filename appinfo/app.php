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
require_once 'apps/ooba/extension_ooba/activityhelper.php';
require_once 'apps/ooba/extension_ooba/activityconnectionfactory.php';
require_once 'apps/ooba/extension_ooba/activityocdb.php';
require_once 'apps/ooba/extension_ooba/extendednavigation.php';
require_once 'apps/ooba/controller/activitiesextension.php';
require_once 'apps/ooba/config/config.php';

\OCP\Config::setAppValue('oobactivity', 'dbhost',    $ACTIVITY_CONFIG['dbhost']);
\OCP\Config::setAppValue('oobactivity', 'dbuser',    $ACTIVITY_CONFIG['dbuser']);
\OCP\Config::setAppValue('oobactivity', 'dbpassword',$ACTIVITY_CONFIG['dbpassword']);
\OCP\Config::setAppValue('oobactivity', 'dbname',    $ACTIVITY_CONFIG['dbname']);
\OCP\Config::setAppValue('oobactivity', 'dbtype',    $ACTIVITY_CONFIG['dbtype']);
\OCP\Config::setAppValue('oobactivity', 'oobadebug', $ACTIVITY_CONFIG['oobadebug']);
\OCP\Config::setAppValue('oobactivity', 'logType',	 $ACTIVITY_CONFIG['logType']);

openlog('oobactivity', LOG_NDELAY | LOG_PID, LOG_USER); // must be executed for activity syslo and/or debug sylog

$app = new Application();
$c = $app->getContainer();

if(!\OCA\OobActivity\ext\ActivityHelper::prepare()){
	\OCA\OobActivity\ext\ActivityHelper::oobaDebug("- oobactivity prepare failed!");
	return;
}
\OCA\OobActivity\ext\ActivityHelper::oobaDebug("- oobactivity prepare success!");

// add an navigation entry
$navigationEntry = function () use ($c) {
	return [
		'id' => $c->getAppName(),
		'order' => 1,
		'name' => $c->query('OobaL10N')->t('Ooba'),
		 // Na linha abaixo, quando a pagina é carregada, todas as atividades sao apresentadas ('all'). O controller Ooba nao sera usado, mas sim um controller como uma experiencia. 
		 // A ideia é utilizar herancas para impactar o minimo possivel nas classas originais do activity padrão (ps o controller oobas ja foi modificado e era o arquivo controller/ activities.php, mas a ideia deixalo intacto).
  		'href' => $c->query('URLGenerator')->linkToRoute('ooba.ActivitiesExtension.showList'),
		//'href' => $c->query('URLGenerator')->linkToRoute('ooba.Oobas.showList'),
		'icon' => $c->query('URLGenerator')->imagePath('ooba', 'ooba.svg'),
	];
};
$c->getServer()->getNavigationManager()->add($navigationEntry);

// register the hooks for filesystem operations. All other events from other apps has to be send via the public api
\OCA\Ooba\FilesHooksStatic::register();
\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\Ooba\Hooks', 'deleteUser');
\OCA\Ooba\Consumer::register($c->getServer()->getActivityManager(), $c);

// Personal settings for notifications and emails
//\OCP\App::registerPersonal($c->getAppName(), 'personal');
