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

namespace OCA\Ooba\Tests;

use Doctrine\DBAL\Driver\Statement;
use OCA\Ooba\Data;
use OCP\Activity\IExtension;

class DataDeleteOobasTest extends TestCase {
	/** @var \OCA\Ooba\Data */
	protected $data;

	protected function setUp() {
		parent::setUp();

		$oobas = array(
			array('affectedUser' => 'delete', 'subject' => 'subject', 'time' => 0),
			array('affectedUser' => 'delete', 'subject' => 'subject2', 'time' => time() - 2 * 365 * 24 * 3600),
			array('affectedUser' => 'otherUser', 'subject' => 'subject', 'time' => time()),
			array('affectedUser' => 'otherUser', 'subject' => 'subject2', 'time' => time()),
		);

		$queryOoba = \OC::$server->getDatabaseConnection()->prepare('INSERT INTO `*PREFIX*ooba`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		foreach ($oobas as $ooba) {
			$queryOoba->execute(array(
				'app',
				$ooba['subject'],
				json_encode([]),
				'',
				json_encode([]),
				'file',
				'link',
				'user',
				$ooba['affectedUser'],
				$ooba['time'],
				IExtension::PRIORITY_MEDIUM,
				'test',
			));
		}
		$this->data = new Data(
			$this->getMock('\OCP\Activity\IManager'),
			\OC::$server->getDatabaseConnection(),
			$this->getMock('\OCP\IUserSession')
		);
	}

	protected function tearDown() {
		$this->data->deleteOobas(array(
			'type' => 'test',
		));

		parent::tearDown();
	}

	public function deleteOobasData() {
		return array(
			array(array('affecteduser' => 'delete'), array('otherUser')),
			array(array('affecteduser' => array('delete', '=')), array('otherUser')),
			array(array('timestamp' => array(time() - 10, '<')), array('otherUser')),
			array(array('timestamp' => array(time() - 10, '>')), array('delete')),
		);
	}

	/**
	 * @dataProvider deleteOobasData
	 */
	public function testDeleteOobas($condition, $expected) {
		$this->assertUserOobas(array('delete', 'otherUser'));
		$this->data->deleteOobas($condition);
		$this->assertUserOobas($expected);
	}

	public function testExpireOobas() {
		$backgroundjob = new \OCA\Ooba\BackgroundJob\ExpireOobas();
		$this->assertUserOobas(array('delete', 'otherUser'));
		$jobList = $this->getMock('\OCP\BackgroundJob\IJobList');
		$backgroundjob->execute($jobList);
		$this->assertUserOobas(array('otherUser'));
	}

	protected function assertUserOobas($expected) {
		$query = \OC::$server->getDatabaseConnection()->prepare("SELECT `affecteduser` FROM `*PREFIX*ooba` WHERE `type` = 'test'");
		$this->assertTableKeys($expected, $query, 'affecteduser');
	}

	protected function assertTableKeys($expected, Statement $query, $keyName) {
		$query->execute();

		$users = array();
		while ($row = $query->fetch()) {
			$users[] = $row[$keyName];
		}
		$query->closeCursor();
		$users = array_unique($users);
		sort($users);
		sort($expected);

		$this->assertEquals($expected, $users);
	}
}
