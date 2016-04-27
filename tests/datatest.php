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
use OCP\IUser;

class DataTest extends TestCase {
	/** @var \OCA\Ooba\Data */
	protected $data;

	/** @var \OCP\IL10N */
	protected $oobaLanguage;

	/** @var OobaManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $oobaManager;

	/** @var \OCP\IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	protected $session;

	protected function setUp() {
		parent::setUp();

		$this->oobaLanguage = $oobaLanguage = \OCP\Util::getL10N('ooba', 'en');
		$this->oobaManager = new OobaManager(
			$this->getMock('OCP\IRequest'),
			$this->getMock('OCP\IUserSession'),
			$this->getMock('OCP\IConfig')
		);
		$this->session = $this->getMockBuilder('OCP\IUserSession')
			->disableOriginalConstructor()
			->getMock();

		$this->oobaManager->registerExtension(function() use ($oobaLanguage) {
			return new Extension($oobaLanguage, $this->getMock('\OCP\IURLGenerator'));
		});
		$this->data = new Data(
			$this->oobaManager,
			\OC::$server->getDatabaseConnection(),
			$this->session
		);
	}

	protected function tearDown() {
		$this->restoreService('UserSession');
		parent::tearDown();
	}

	public function dataGetNotificationTypes() {
		return [
			['type1'],
		];
	}

	/**
	 * @dataProvider dataGetNotificationTypes
	 * @param string $typeKey
	 */
	public function testGetNotificationTypes($typeKey) {
		$this->assertArrayHasKey($typeKey, $this->data->getNotificationTypes($this->oobaLanguage));
		// Check cached version aswell
		$this->assertArrayHasKey($typeKey, $this->data->getNotificationTypes($this->oobaLanguage));
	}

	public function validateFilterData() {
		return array(
			// Default filters
			array('all', 'all'),
			array('by', 'by'),
			array('self', 'self'),

			// Filter from extension
			array('filter1', 'filter1'),

			// Inexistent or empty filter
			array('test', 'all'),
			array(null, 'all'),
		);
	}

	/**
	 * @dataProvider validateFilterData
	 *
	 * @param string $filter
	 * @param string $expected
	 */
	public function testValidateFilter($filter, $expected) {
		$this->assertEquals($expected, $this->data->validateFilter($filter));
	}

	public function dataSend() {
		return [
			// Default case
			['author', 'affectedUser', 'author', 'affectedUser', true],
			// Public page / Incognito mode
			['', 'affectedUser', '', 'affectedUser', true],
			// No affected user => no ooba
			['author', '', 'author', '', false],
			// No affected user and no author => no ooba
			['', '', '', '', false],
		];
	}

	/**
	 * @dataProvider dataSend
	 *
	 * @param string $actionUser
	 * @param string $affectedUser
	 * @param string $expectedAuthor
	 * @param string $expectedAffected
	 * @param bool $expectedOoba
	 */
	public function testSend($actionUser, $affectedUser, $expectedAuthor, $expectedAffected, $expectedOoba) {
		$mockSession = $this->getMockBuilder('\OC\User\Session')
			->disableOriginalConstructor()
			->getMock();

		$this->overwriteService('UserSession', $mockSession);
		$this->deleteTestOobas();

		$event = \OC::$server->getActivityManager()->generateEvent();
		$event->setApp('test')
			->setType('type')
			->setAffectedUser($affectedUser)
			->setSubject('subject', []);
		if ($actionUser !== '') {
			$event->setAuthor($actionUser);
		}

		$this->assertSame($expectedOoba, $this->data->send($event));

		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('SELECT `user`, `affecteduser` FROM `*PREFIX*ooba` WHERE `app` = ? ORDER BY `ooba_id` DESC');
		$query->execute(['test']);
		$row = $query->fetch();

		if ($expectedOoba) {
			$this->assertEquals(['user' => $expectedAuthor, 'affecteduser' => $expectedAffected], $row);
		} else {
			$this->assertFalse($row);
		}

		$this->deleteTestOobas();
		$this->restoreService('UserSession');
	}

	/**
	 * @dataProvider dataSend
	 *
	 * @param string $actionUser
	 * @param string $affectedUser
	 * @param string $expectedAuthor
	 * @param string $expectedAffected
	 * @param bool $expectedOoba
	 */
	public function testStoreMail($actionUser, $affectedUser, $expectedAuthor, $expectedAffected, $expectedOoba) {
		$mockSession = $this->getMockBuilder('\OC\User\Session')
			->disableOriginalConstructor()
			->getMock();

		$this->overwriteService('UserSession', $mockSession);
		$this->deleteTestMails();

		$time = time();

		$event = \OC::$server->getActivityManager()->generateEvent();
		$event->setApp('test')
			->setType('type')
			->setAffectedUser($affectedUser)
			->setSubject('subject', [])
			->setTimestamp($time);

		$this->assertSame($expectedOoba, $this->data->storeMail($event, $time + 10));

		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('SELECT `oobamq_latest_send`, `oobamq_affecteduser` FROM `*PREFIX*ooba_mq` WHERE `oobamq_appid` = ? ORDER BY `mail_id` DESC');
		$query->execute(['test']);
		$row = $query->fetch();

		if ($expectedOoba) {
			$this->assertEquals(['oobamq_latest_send' => $time + 10, 'oobamq_affecteduser' => $expectedAffected], $row);
		} else {
			$this->assertFalse($row);
		}

		$this->deleteTestMails();
		$this->restoreService('UserSession');
	}

	protected function getUserMock() {
		$user = $this->getMockBuilder('\OCP\IUser')
			->disableOriginalConstructor()
			->getMock();
		$user->expects($this->any())
			->method('getUID')
			->willReturn('username');
		return $user;
	}

	public function dataRead() {
		return [
			[null, 0, 10, 'all', '', null, [], '', 0, null, null, [], null, null],
			[$this->getUserMock(), 0, 10, 'all', '', 'username', [], '', 0, null, null, [], null, null],
			[$this->getUserMock(), 0, 10, 'all', 'test', 'test', [], '', 0, null, null, [], null, null],
			[$this->getUserMock(), 0, 10, 'all', '', 'username', ['file_created'], false, '', 0, null, [], ' AND `type` IN (?) AND `user` <> ?', ['username', 'file_created', 'username']],
			[$this->getUserMock(), 0, 10, 'all', '', 'username', ['file_created'], true, '', 0, null, [], ' AND `type` IN (?)', ['username', 'file_created']],
			[$this->getUserMock(), 0, 10, 'by', '', 'username', ['file_created'], null, '', 0, null, [], ' AND `type` IN (?) AND `user` <> ?', ['username', 'file_created', 'username']],
			[$this->getUserMock(), 0, 10, 'self', '', 'username', ['file_created'], null, '', 0, null, [], ' AND `type` IN (?) AND `user` = ?', ['username', 'file_created', 'username']],
			[$this->getUserMock(), 0, 10, 'all', '', 'username', ['file_created'], true, '', 0, 'OR `cond` = 1', null, ' AND `type` IN (?) OR `cond` = 1', ['username', 'file_created']],
			[$this->getUserMock(), 0, 10, 'all', '', 'username', ['file_created'], true, '', 0, 'OR `cond` = ?', ['con1'], ' AND `type` IN (?) OR `cond` = ?', ['username', 'file_created', 'con1']],
			[$this->getUserMock(), 0, 10, 'filter', '', 'username', ['file_created'], false, 'files', 42, null, [], ' AND `type` IN (?) AND `user` <> ? AND `object_type` = ? AND `object_id` = ?', ['username', 'file_created', 'username', 'files', 42]],
			[$this->getUserMock(), 0, 10, 'filter', '', 'username', ['file_created'], true, 'files', 42, null, [], ' AND `type` IN (?) AND `object_type` = ? AND `object_id` = ?', ['username', 'file_created', 'files', 42]],
		];
	}

	/**
	 * @dataProvider dataRead
	 *
	 * @param IUser $sessionUser
	 * @param int $start
	 * @param int $count
	 * @param string $filter
	 * @param string $user
	 * @param string $expectedUser
	 * @param array $notificationTypes
	 * @param bool $selfSetting
	 * @param string $objectType
	 * @param int $objectId
	 * @param array $conditions
	 * @param array $params
	 * @param string $limitOobas
	 * @param array $parameters
	 */
	public function testRead($sessionUser, $start, $count, $filter, $user, $expectedUser, $notificationTypes, $selfSetting, $objectType, $objectId, $conditions, $params, $limitOobas, $parameters) {

		/** @var \OCA\Ooba\GroupHelper|\PHPUnit_Framework_MockObject_MockObject $groupHelper */
		$groupHelper = $this->getMockBuilder('OCA\Ooba\GroupHelper')
			->disableOriginalConstructor()
			->getMock();
		$groupHelper->expects(($expectedUser === null) ? $this->never() : $this->once())
			->method('setUser')
			->with($expectedUser);

		/** @var \OCA\Ooba\UserSettings|\PHPUnit_Framework_MockObject_MockObject $settings */
		$settings = $this->getMockBuilder('OCA\Ooba\UserSettings')
			->disableOriginalConstructor()
			->getMock();
		$settings->expects(($expectedUser === null) ? $this->never() : $this->once())
			->method('getNotificationTypes')
			->with($expectedUser, 'stream')
			->willReturn(['settings']);
		$settings->expects(($selfSetting === null) ? $this->never() : $this->any())
			->method('getUserSetting')
			->with($expectedUser, 'setting', 'self')
			->willReturn($selfSetting);

		/** @var OobaManager|\PHPUnit_Framework_MockObject_MockObject $oobaManager */
		$oobaManager = $this->getMockBuilder('OCP\Activity\IManager')
			->disableOriginalConstructor()
			->getMock();
		$oobaManager->expects($this->any())
			->method('filterNotificationTypes')
			->with(['settings'], $filter)
			->willReturn($notificationTypes);
		$oobaManager->expects($this->any())
			->method('getQueryForFilter')
			->with($filter)
			->willReturn([$conditions, $params]);

		/** @var \OCA\Ooba\Data|\PHPUnit_Framework_MockObject_MockObject $data */
		$data = $this->getMockBuilder('OCA\Ooba\Data')
			->setConstructorArgs([
				$oobaManager,
				\OC::$server->getDatabaseConnection(),
				$this->session
			])
			->setMethods(['getOobas'])
			->getMock();
		$data->expects(($parameters === null && $limitOobas === null) ? $this->never() : $this->once())
			->method('getOobas')
			->with($count, $start, $limitOobas, $parameters, $groupHelper)
			->willReturn([]);

		$this->session->expects($this->any())
			->method('getUser')
			->willReturn($sessionUser);

		$this->assertEquals([], $data->read(
			$groupHelper, $settings,
			$start, $count, $filter, $user,
			$objectType, $objectId
		));
	}

	/**
	 * Delete all testing oobas
	 */
	public function deleteTestOobas() {
		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('DELETE FROM `*PREFIX*ooba` WHERE `app` = ?');
		$query->execute(['test']);
	}

	/**
	 * Delete all testing mails
	 */
	public function deleteTestMails() {
		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('DELETE FROM `*PREFIX*ooba_mq` WHERE `oobamq_appid` = ?');
		$query->execute(['test']);
	}
}
