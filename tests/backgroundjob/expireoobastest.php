<?php

/**
 * ownCloud
 *
 * @author Joas Schilling
 * @copyright 2015 Joas Schilling nickvergessen@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Ooba\Tests\BackgroundJob;

use OCA\Ooba\BackgroundJob\ExpireOobas;
use OCA\Ooba\Data;
use OCA\Ooba\Tests\TestCase;
use OCP\IConfig;

class ExpireOobasTest extends TestCase {
	public function dataExecute() {
		return [
			[],
			[
				$this->getMockBuilder('OCA\Ooba\Data')->disableOriginalConstructor()->getMock(),
				$this->getMockBuilder('OCP\IConfig')->disableOriginalConstructor()->getMock(),
			],
		];
	}

	/**
	 * @dataProvider dataExecute
	 *
	 * @param Data $data
	 * @param IConfig $config
	 */
	public function testExecute(Data $data = null, IConfig $config = null) {
		$backgroundJob = new ExpireOobas($data, $config);

		$jobList = $this->getMock('\OCP\BackgroundJob\IJobList');

		/** @var \OC\BackgroundJob\JobList $jobList */
		$backgroundJob->execute($jobList);
		$this->assertTrue(true);

		// NOTE: the result of execute() is further tested in
		// DataDeleteOobasTest::testExpireOobas()
	}
}
