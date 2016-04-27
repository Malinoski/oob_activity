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
use OCA\Ooba\Hooks;
use OCP\Activity\IExtension;

class HooksDeleteUserTest extends TestCase {
	protected function setUp() {
		parent::setUp();

		$oobas = array(
			array('affectedUser' => 'delete', 'subject' => 'subject'),
			array('affectedUser' => 'delete', 'subject' => 'subject2'),
			array('affectedUser' => 'otherUser', 'subject' => 'subject'),
			array('affectedUser' => 'otherUser', 'subject' => 'subject2'),
		);

		$queryOoba = \OC::$server->getDatabaseConnection()->prepare('INSERT INTO `*PREFIX*ooba`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$queryMailQueue = \OC::$server->getDatabaseConnection()->prepare('INSERT INTO `*PREFIX*ooba_mq`(`oobamq_appid`, `oobamq_subject`, `oobamq_subjectparams`, `oobamq_affecteduser`, `oobamq_timestamp`, `oobamq_type`, `oobamq_latest_send`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?)');
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
				time(),
				IExtension::PRIORITY_MEDIUM,
				'test',
			));
			$queryMailQueue->execute(array(
				'app',
				$ooba['subject'],
				json_encode([]),
				$ooba['affectedUser'],
				time(),
				'test',
				time() + 10,
			));
		}
	}

	protected function tearDown() {
		$data = new Data(
			$this->getMock('\OCP\Activity\IManager'),
			\OC::$server->getDatabaseConnection(),
			$this->getMock('\OCP\IUserSession')
		);
		$data->deleteOobas(array(
			'type' => 'test',
		));
		$query = \OC::$server->getDatabaseConnection()->prepare("DELETE FROM `*PREFIX*ooba_mq` WHERE `oobamq_type` = 'test'");
		$query->execute();

		parent::tearDown();
	}

	public function testHooksDeleteUser() {

		$this->assertUserOobas(array('delete', 'otherUser'));
		$this->assertUserMailQueue(array('delete', 'otherUser'));
		Hooks::deleteUser(array('uid' => 'delete'));
		$this->assertUserOobas(array('otherUser'));
		$this->assertUserMailQueue(array('otherUser'));
	}

	protected function assertUserOobas($expected) {
		$query = \OC::$server->getDatabaseConnection()->prepare("SELECT `affecteduser` FROM `*PREFIX*ooba` WHERE `type` = 'test'");
		$this->assertTableKeys($expected, $query, 'affecteduser');
	}

	protected function assertUserMailQueue($expected) {
		$query = \OC::$server->getDatabaseConnection()->prepare("SELECT `oobamq_affecteduser` FROM `*PREFIX*ooba_mq` WHERE `oobamq_type` = 'test'");
		$this->assertTableKeys($expected, $query, 'oobamq_affecteduser');
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
