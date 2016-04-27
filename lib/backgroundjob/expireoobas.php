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

namespace OCA\Ooba\BackgroundJob;

use OC\BackgroundJob\TimedJob;
use OCA\Ooba\AppInfo\Application;
use OCA\Ooba\Data;
use OCP\IConfig;

/**
 * Class ExpireOobas
 *
 * @package OCA\Ooba\BackgroundJob
 */
class ExpireOobas extends TimedJob {
	/** @var Data */
	protected $data;
	/** @var IConfig */
	protected $config;

	/**
	 * @param Data $data
	 * @param IConfig $config
	 */
	public function __construct(Data $data = null, IConfig $config = null) {
		// Run once per day
		$this->setInterval(60 * 60 * 24);

		if ($data === null || $config === null) {
			$this->fixDIForJobs();
		} else {
			$this->data = $data;
			$this->config = $config;
		}
	}

	protected function fixDIForJobs() {
		$application = new Application();

		$this->data = $application->getContainer()->query('OobaData');
		$this->config = \OC::$server->getConfig();
	}

	protected function run($argument) {
		// Remove oobas that are older then one year
		$expireDays = $this->config->getSystemValue('ooba_expire_days', 365);
		$this->data->expire($expireDays);
	}
}
