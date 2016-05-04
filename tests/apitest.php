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

use OC\OobaManager;
use OCA\Ooba\Data;
use OCA\Ooba\Tests\Mock\Extension;
use OCP\Activity\IExtension;

class ApiTest extends TestCase {
	protected $originalWEBROOT;

	protected function setUp() {
		parent::setUp();

		$this->originalWEBROOT = \OC::$WEBROOT;
		\OC::$WEBROOT = '';
		\OC::$server->getUserManager()->createUser('ooba-api-user1', 'ooba-api-user1');
		\OC::$server->getUserManager()->createUser('ooba-api-user2', 'ooba-api-user2');

		$oobas = array(
			array(
				'affectedUser' => 'ooba-api-user1',
				'subject' => 'subject1',
				'subjectparams' => array('/A/B.txt'),
				'type' => 'type1',
			),
			array(
				'affectedUser' => 'ooba-api-user1',
				'subject' => 'subject2',
				'subjectparams' => array('/A/B.txt', 'User'),
				'type' => 'type2',
			),
		);

		$queryOoba = \OC::$server->getDatabaseConnection()->prepare('INSERT INTO `*PREFIX*ooba`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$loop = 0;
		foreach ($oobas as $ooba) {
			$queryOoba->execute(array(
				'app1',
				$ooba['subject'],
				json_encode($ooba['subjectparams']),
				'',
				json_encode([]),
				'file',
				'link',
				'user',
				$ooba['affectedUser'],
				time() + $loop,
				IExtension::PRIORITY_MEDIUM,
				$ooba['type'],
			));
			$loop++;
		}
	}

	protected function tearDown() {
		$data = new Data(
			$this->getMock('\OCP\Activity\IManager'),
			\OC::$server->getDatabaseConnection(),
			$this->getMock('\OCP\IUserSession')
		);

		$this->deleteUser($data, 'ooba-api-user1');
		$this->deleteUser($data, 'ooba-api-user2');

		$data->deleteOobas(array(
			'app' => 'app1',
		));
		\OC::$WEBROOT = $this->originalWEBROOT;

		parent::tearDown();
	}

	protected function deleteUser(Data $data, $uid) {
		$data->deleteOobas(array(
			'affecteduser' => $uid,
		));
		$user = \OC::$server->getUserManager()->get($uid);
		if ($user) {
			$user->delete();
		}
	}

	public function getData() {
		return array(
			array('ooba-api-user2', 0, 30, array()),
			array('ooba-api-user1', 0, 30, array(
				array(
					'link' => 'link',
					'file' => 'file',
					'date' => null,
					'id' => null,
					'message' => '',
					'subject' => 'Subject2 @User #A/B.txt',
				),
				array(
					'link' => 'link',
					'file' => 'file',
					'date' => null,
					'id' => null,
					'message' => '',
					'subject' => 'Subject1 #A/B.txt',
				),
			)),
			array('ooba-api-user1', 0, 1, array(
				array(
					'link' => 'link',
					'file' => 'file',
					'date' => null,
					'id' => null,
					'message' => '',
					'subject' => 'Subject2 @User #A/B.txt',
				),
			)),
			array('ooba-api-user1', 1, 1, array(
				array(
					'link' => 'link',
					'file' => 'file',
					'date' => null,
					'id' => null,
					'message' => '',
					'subject' => 'Subject1 #A/B.txt',
				),
			)),
		);
	}

	/**
	 * @dataProvider getData
	 */
	public function testGet($user, $start, $count, $expected) {
		$_GET['start'] = $start;
		$_GET['count'] = $count;
		\OC_User::setUserId($user);
		$sessionUser = \OC::$server->getUserSession()->getUser();
		$this->assertInstanceOf('OCP\IUser', $sessionUser);
		$this->assertEquals($user, $sessionUser->getUID());

		$oobaManager = new OobaManager(
			$this->getMock('OCP\IRequest'),
			$this->getMock('OCP\IUserSession'),
			$this->getMock('OCP\IConfig')
		);
		$oobaManager->registerExtension(function() {
			return new Extension(\OCP\Util::getL10N('ooba', 'en'), $this->getMock('\OCP\IURLGenerator'));
		});
		$this->overwriteService('OobaManager', $oobaManager);
		$result = \OCA\Ooba\Api::get(array('_route' => 'get_cloud_ooba'));
		$this->restoreService('OobaManager');

		$this->assertEquals(100, $result->getStatusCode());
		$data = $result->getData();
		$this->assertEquals(sizeof($expected), sizeof($data));

		while (!empty($expected)) {
			$assertExpected = array_shift($expected);
			$assertData = array_shift($data);
			foreach ($assertExpected as $key => $value) {
				$this->assertArrayHasKey($key, $assertData);
				if ($value !== null) {
					$this->assertEquals($value, $assertData[$key]);
				}
			}
		}
	}
}
