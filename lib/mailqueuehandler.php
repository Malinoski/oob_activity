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

namespace OCA\Ooba;

use OCP\Defaults;
use OCP\IDateTimeFormatter;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Mail\IMailer;
use OCP\Template;
use OCP\Util;

/**
 * Class MailQueueHandler
 * Gets the users from the database and
 *
 * @package OCA\Ooba
 */
class MailQueueHandler {
	/** Number of entries we want to list in the email */
	const ENTRY_LIMIT = 200;

	/** @var array */
	protected $languages;

	/** @var string */
	protected $senderAddress;

	/** @var string */
	protected $senderName;

	/** @var IDateTimeFormatter */
	protected $dateFormatter;

	/** @var DataHelper */
	protected $dataHelper;

	/** @var IDBConnection */
	protected $connection;

	/** @var IMailer */
	protected $mailer;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/** @var IUserManager */
	protected $userManager;

	/**
	 * Constructor
	 *
	 * @param IDateTimeFormatter $dateFormatter
	 * @param IDBConnection $connection
	 * @param DataHelper $dataHelper
	 * @param IMailer $mailer
	 * @param IURLGenerator $urlGenerator
	 * @param IUserManager $userManager
	 */
	public function __construct(IDateTimeFormatter $dateFormatter,
								IDBConnection $connection,
								DataHelper $dataHelper,
								IMailer $mailer,
								IURLGenerator $urlGenerator,
								IUserManager $userManager) {
		$this->dateFormatter = $dateFormatter;
		$this->connection = $connection;
		$this->dataHelper = $dataHelper;
		$this->mailer = $mailer;
		$this->urlGenerator = $urlGenerator;
		$this->userManager = $userManager;
	}

	/**
	 * Get the users we want to send an email to
	 *
	 * @param int|null $limit
	 * @param int $latestSend
	 * @return array
	 */
	public function getAffectedUsers($limit, $latestSend) {
		$limit = (!$limit) ? null : (int) $limit;

		$query = $this->connection->prepare(
			'SELECT `oobamq_affecteduser`, MIN(`oobamq_latest_send`) AS `oobamq_trigger_time` '
			. ' FROM `*PREFIX*ooba_mq` '
			. ' WHERE `oobamq_latest_send` < ? '
			. ' GROUP BY `oobamq_affecteduser` '
			. ' ORDER BY `oobamq_trigger_time` ASC',
			$limit);
		$query->execute(array($latestSend));

		$affectedUsers = array();
		while ($row = $query->fetch()) {
			$affectedUsers[] = $row['oobamq_affecteduser'];
		}

		return $affectedUsers;
	}

	/**
	 * Get all items for the user we want to send an email to
	 *
	 * @param string $affectedUser
	 * @param int $maxTime
	 * @param int $maxNumItems
	 * @return array [data of the first max. 200 entries, total number of entries]
	 */
	protected function getItemsForUser($affectedUser, $maxTime, $maxNumItems = self::ENTRY_LIMIT) {
		$query = $this->connection->prepare(
			'SELECT * '
			. ' FROM `*PREFIX*ooba_mq` '
			. ' WHERE `oobamq_timestamp` <= ? '
			. ' AND `oobamq_affecteduser` = ? '
			. ' ORDER BY `oobamq_timestamp` ASC',
			$maxNumItems
		);
		$query->execute([(int) $maxTime, $affectedUser]);

		$oobas = array();
		while ($row = $query->fetch()) {
			$oobas[] = $row;
		}

		if (isset($oobas[$maxNumItems - 1])) {
			// Reached the limit, run a query to get the actual count.
			$query = $this->connection->prepare(
				'SELECT COUNT(*) AS `actual_count`'
				. ' FROM `*PREFIX*ooba_mq` '
				. ' WHERE `oobamq_timestamp` <= ? '
				. ' AND `oobamq_affecteduser` = ?'
			);
			$query->execute([(int) $maxTime, $affectedUser]);

			$row = $query->fetch();
			return [$oobas, $row['actual_count'] - $maxNumItems];
		} else {
			return [$oobas, 0];
		}
	}

	/**
	 * Get a language object for a specific language
	 *
	 * @param string $lang Language identifier
	 * @return \OCP\IL10N Language object of $lang
	 */
	protected function getLanguage($lang) {
		if (!isset($this->languages[$lang])) {
			$this->languages[$lang] = Util::getL10N('ooba', $lang);
		}

		return $this->languages[$lang];
	}

	/**
	 * Get the sender data
	 * @param string $setting Either `email` or `name`
	 * @return string
	 */
	protected function getSenderData($setting) {
		if (empty($this->senderAddress)) {
			$this->senderAddress = Util::getDefaultEmailAddress('no-reply');
		}
		if (empty($this->senderName)) {
			$defaults = new Defaults();
			$this->senderName = $defaults->getName();
		}

		if ($setting === 'email') {
			return $this->senderAddress;
		}
		return $this->senderName;
	}

	/**
	 * Send a notification to one user
	 *
	 * @param string $userName Username of the recipient
	 * @param string $email Email address of the recipient
	 * @param string $lang Selected language of the recipient
	 * @param string $timezone Selected timezone of the recipient
	 * @param int $maxTime
	 */
	public function sendEmailToUser($userName, $email, $lang, $timezone, $maxTime) {
		$user = $this->userManager->get($userName);
		if (!$user instanceof IUser) {
			return;
		}

		list($mailData, $skippedCount) = $this->getItemsForUser($userName, $maxTime);

		$l = $this->getLanguage($lang);
		$this->dataHelper->setUser($userName);
		$this->dataHelper->setL10n($l);

		$oobaList = array();
		foreach ($mailData as $ooba) {
			$relativeDateTime = $this->dateFormatter->formatDateTimeRelativeDay(
				$ooba['oobamq_timestamp'],
				'long', 'medium',
				new \DateTimeZone($timezone), $l
			);

			$oobaList[] = array(
				$this->dataHelper->translation(
					$ooba['oobamq_appid'], $ooba['oobamq_subject'], $this->dataHelper->getParameters($ooba['oobamq_subjectparams'])
				),
				$relativeDateTime,
			);
		}

		$alttext = new Template('ooba', 'email.notification', '');
		$alttext->assign('username', $user->getDisplayName());
		$alttext->assign('oobas', $oobaList);
		$alttext->assign('skippedCount', $skippedCount);
		$alttext->assign('owncloud_installation', $this->urlGenerator->getAbsoluteURL('/'));
		$alttext->assign('overwriteL10N', $l);
		$emailText = $alttext->fetchPage();

		$message = $this->mailer->createMessage();
		$message->setTo([$email => $user->getDisplayName()]);
		$message->setSubject((string) $l->t('Ooba notification'));
		$message->setPlainBody($emailText);
		$message->setFrom([$this->getSenderData('email') => $this->getSenderData('name')]);
		$this->mailer->send($message);
	}

	/**
	 * Delete all entries we dealt with
	 *
	 * @param array $affectedUsers
	 * @param int $maxTime
	 */
	public function deleteSentItems($affectedUsers, $maxTime) {
		$placeholders = implode(',', array_fill(0, sizeof($affectedUsers), '?'));
		$queryParams = $affectedUsers;
		array_unshift($queryParams, (int) $maxTime);

		$query = $this->connection->prepare(
			'DELETE FROM `*PREFIX*ooba_mq` '
			. ' WHERE `oobamq_timestamp` <= ? '
			. ' AND `oobamq_affecteduser` IN (' . $placeholders . ')');
		$query->execute($queryParams);
	}
}
