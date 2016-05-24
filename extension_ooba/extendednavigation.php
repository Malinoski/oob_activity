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
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\OobActivity;

use \OCA\Ooba\Navigation;
use \OCP\Activity\IManager;
use \OCP\IURLGenerator;
use \OCP\Template;

/**
 * Class Navigation
 *
 * @package OCA\Ooba
 */
class ExtendedNavigation extends Navigation {


	/**
	 * Get all items for the users we want to send an email to
	 *
	 * @return array Notification data (user => array of rows from the table)
	 */
	public function getLinkList() {
		$topEntries = [
			[
				'id' => 'all',
				'name' => (string) $this->l->t('All Activities'),
				'url' => $this->URLGenerator->linkToRoute('ooba.ActivitiesExtension.showList'),
			],
		];

		if ($this->user && $this->userSettings->getUserSetting($this->user, 'setting', 'self')) {
			$topEntries[] = [
				'id' => 'self',
				'name' => (string) $this->l->t('Activities by you'),
				'url' => $this->URLGenerator->linkToRoute('ooba.ActivitiesExtension.showList', array('filter' => 'self')),
			];
			$topEntries[] = [
				'id' => 'by',
				'name' => (string) $this->l->t('Activities by others'),
				'url' => $this->URLGenerator->linkToRoute('ooba.ActivitiesExtension.showList', array('filter' => 'by')),
			];
		}

		$additionalEntries = $this->oobaManager->getNavigation();
		
		$topEntries = array_merge($topEntries, $additionalEntries['top']);

		$topEntries[] = [
				'id' => 'syslog',
				'name' => 'syslog',
				'url' => $this->URLGenerator->linkToRoute('ooba.ActivitiesExtension.showSyslog', array('filter' => 'syslog')),
		];
		
		return array(
			'top'		=> $topEntries,
			'apps'		=> $additionalEntries['apps'],
		);
	}
}
