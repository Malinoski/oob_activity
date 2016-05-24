<?php

/**
 * ownCloud - Ooba App
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
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
 */

namespace OCA\Ooba\AppInfo;

use OC\Files\View;
use OCA\Ooba\Consumer;
//use OCA\Ooba\Controller\Oobas;
use OCA\Ooba\Controller\ActivitiesExtension;
use OCA\Ooba\Controller\Feed;
use OCA\Ooba\Controller\Settings;
//use OCA\Ooba\Data; // original
use OCA\Ooba\ActivityData; //ooba
use OCA\Ooba\DataHelper;
use OCA\Ooba\GroupHelper;
use OCA\Ooba\FilesHooks;
use OCA\Ooba\MailQueueHandler;
use OCA\Ooba\Navigation;
use OCA\Ooba\ParameterHelper;
use OCA\Ooba\UserSettings;
use OCP\AppFramework\App;
use OCP\IContainer;
use OCA\OobActivity\ext\ActivityHelper;
use OCA\OobActivity\Service\DebugService;
use OCA\OobActivity\ExtendedNavigation;

class Application extends App {
	public function __construct (array $urlParams = array()) {
		parent::__construct('ooba', $urlParams);
		$container = $this->getContainer();

		/**
		 * Ooba Services
		 */
		
		$container->registerService('OobaData', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			//return new Data( //original
			return new ActivityData( // ooba
				$server->getActivityManager(),
				//$server->getDatabaseConnection(),
				\OCA\OobActivity\ext\ActivityHelper::getDatabaseConnection(),
				$server->getUserSession()
			);
		});

		$container->registerService('OobaL10N', function(IContainer $c) {
			return $c->query('ServerContainer')->getL10N('ooba');
		});


		$container->registerService('Consumer', function(IContainer $c) {
			return new Consumer(
				$c->query('OobaData'),
				$c->query('UserSettings')
			);
		});

		$container->registerService('DataHelper', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return new DataHelper(
				$server->getActivityManager(),
				new ParameterHelper(
					$server->getActivityManager(),
					$server->getUserManager(),
					$server->getURLGenerator(),
					$server->getContactsManager(),
					new View(''),
					$server->getConfig(),
					$c->query('OobaL10N'),
					$c->query('CurrentUID')
				),
				$c->query('OobaL10N')
			);
		});

		$container->registerService('GroupHelper', function(IContainer $c) {
			return new GroupHelper(
				$c->query('ServerContainer')->getActivityManager(),
				$c->query('DataHelper'),
				true
			);
		});

		$container->registerService('GroupHelperSingleEntries', function(IContainer $c) {
			return new GroupHelper(
				$c->query('ServerContainer')->getActivityManager(),
				$c->query('DataHelper'),
				false
			);
		});

		$container->registerService('Hooks', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new FilesHooks(
				$server->getActivityManager(),
				$c->query('OobaData'),
				$c->query('UserSettings'),
				$server->getGroupManager(),
				new View(''),
				$server->getDatabaseConnection(),
				$c->query('CurrentUID')
			);
		});

		$container->registerService('MailQueueHandler', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new MailQueueHandler(
				$server->getDateTimeFormatter(),
				//$server->getDatabaseConnection(),
				\OCA\OobActivity\ext\ActivityHelper::getDatabaseConnection(),
				$c->query('DataHelper'),
				$server->getMailer(),
				$server->getURLGenerator(),
				$server->getUserManager()
			);
		});

		$container->registerService('Navigation', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			$rssToken = ($c->query('CurrentUID') !== '') ? $server->getConfig()->getUserValue($c->query('CurrentUID'), 'ooba', 'rsstoken') : '';

			//return new Navigation(
			return new ExtendedNavigation(
				$c->query('OobaL10N'),
				$server->getActivityManager(),
				$server->getURLGenerator(),
				$c->query('UserSettings'),
				$c->query('CurrentUID'),
				$rssToken
			);
		});

		$container->registerService('UserSettings', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return new UserSettings(
				$server->getActivityManager(),
				$server->getConfig(),
				$c->query('OobaData')
			);
		});

		/**
		 * Core Services
		 */
		$container->registerService('URLGenerator', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return $server->getURLGenerator();
		});

		$container->registerService('CurrentUID', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			$user = $server->getUserSession()->getUser();
			return ($user) ? $user->getUID() : '';
		});

		/**
		 * Controller
		 */
		$container->registerService('SettingsController', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new Settings(
				$c->query('AppName'),
				$server->getRequest(),
				$server->getConfig(),
				$server->getSecureRandom()->getMediumStrengthGenerator(),
				$server->getURLGenerator(),
				$c->query('OobaData'),
				$c->query('UserSettings'),
				$c->query('OobaL10N'),
				$c->query('CurrentUID')
			);
		});

		//$container->registerService('OobasController', function(IContainer $c) {
		$container->registerService('ActivitiesExtensionController', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			//return new Oobas(
			return new ActivitiesExtension(
				$c->query('AppName'),
				$server->getRequest(),
				$c->query('OobaData'),
				$c->query('GroupHelper'),
				$c->query('Navigation'),
				$c->query('UserSettings'),
				$server->getDateTimeFormatter(),
				$server->getPreviewManager(),
				$server->getURLGenerator(),
				$server->getMimeTypeDetector(),
				new View(''),
				$c->query('CurrentUID')
			);
		});

		$container->registerService('FeedController', function(IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new Feed(
				$c->query('AppName'),
				$server->getRequest(),
				$c->query('OobaData'),
				$c->query('GroupHelperSingleEntries'),
				$c->query('UserSettings'),
				$c->query('URLGenerator'),
				$server->getActivityManager(),
				$server->getL10NFactory(),
				$server->getConfig(),
				$c->query('CurrentUID')
			);
		});
		
	
// 		$container->registerService('Logger', function($c) {
// 			return $c->query('ServerContainer')->getLogger();
// 		});
	}
}
