<?php

/**
 * ownCloud
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

$app = new \OCA\Ooba\AppInfo\Application();
/** @var OCA\Ooba\Controller\Settings $controller */
$controller = $app->getContainer()->query('SettingsController');
return $controller->displayPanel()->render();
