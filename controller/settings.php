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
*
*/

namespace OCA\Ooba\Controller;

use OCA\Ooba\Data;
use OCA\Ooba\UserSettings;
use OCP\Activity\IExtension;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;

class Settings extends Controller {
	/** @var \OCP\IConfig */
	protected $config;

	/** @var \OCP\Security\ISecureRandom */
	protected $random;

	/** @var \OCP\IURLGenerator */
	protected $urlGenerator;

	/** @var \OCA\Ooba\Data */
	protected $data;

	/** @var \OCA\Ooba\UserSettings */
	protected $userSettings;

	/** @var \OCP\IL10N */
	protected $l10n;

	/** @var string */
	protected $user;

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ISecureRandom $random
	 * @param IURLGenerator $urlGenerator
	 * @param Data $data
	 * @param UserSettings $userSettings
	 * @param IL10N $l10n
	 * @param string $user
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								ISecureRandom $random,
								IURLGenerator $urlGenerator,
								Data $data,
								UserSettings $userSettings,
								IL10N $l10n,
								$user) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->random = $random;
		$this->urlGenerator = $urlGenerator;
		$this->data = $data;
		$this->userSettings = $userSettings;
		$this->l10n = $l10n;
		$this->user = $user;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $notify_setting_batchtime
	 * @param bool $notify_setting_self
	 * @param bool $notify_setting_selfemail
	 * @return DataResponse
	 */
	public function personal(
			$notify_setting_batchtime = UserSettings::EMAIL_SEND_HOURLY,
			$notify_setting_self = false,
			$notify_setting_selfemail = false) {
		$types = $this->data->getNotificationTypes($this->l10n);

		foreach ($types as $type => $desc) {
			$this->config->setUserValue(
				$this->user, 'ooba',
				'notify_email_' . $type,
				$this->request->getParam($type . '_email', false)
			);

			$this->config->setUserValue(
				$this->user, 'ooba',
				'notify_stream_' . $type,
				$this->request->getParam($type . '_stream', false)
			);
		}

		$email_batch_time = 3600;
		if ($notify_setting_batchtime === UserSettings::EMAIL_SEND_DAILY) {
			$email_batch_time = 3600 * 24;
		} else if ($notify_setting_batchtime === UserSettings::EMAIL_SEND_WEEKLY) {
			$email_batch_time = 3600 * 24 * 7;
		}

		$this->config->setUserValue(
			$this->user, 'ooba',
			'notify_setting_batchtime',
			$email_batch_time
		);
		$this->config->setUserValue(
			$this->user, 'ooba',
			'notify_setting_self',
			$notify_setting_self
		);
		$this->config->setUserValue(
			$this->user, 'ooba',
			'notify_setting_selfemail',
			$notify_setting_selfemail
		);

		return new DataResponse(array(
			'data'		=> array(
				'message'	=> (string) $this->l10n->t('Your settings have been updated.'),
			),
		));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function displayPanel() {
		$types = $this->data->getNotificationTypes($this->l10n);

		$oobas = array();
		foreach ($types as $type => $desc) {
			if (is_array($desc)) {
				$methods = isset($desc['methods']) ? $desc['methods'] : [IExtension::METHOD_STREAM, IExtension::METHOD_MAIL];
				$desc = isset($desc['desc']) ? $desc['desc'] : '';
			} else {
				$methods = [IExtension::METHOD_STREAM, IExtension::METHOD_MAIL];
			}

			$oobas[$type] = array(
				'desc'		=> $desc,
				IExtension::METHOD_MAIL		=> $this->userSettings->getUserSetting($this->user, 'email', $type),
				IExtension::METHOD_STREAM	=> $this->userSettings->getUserSetting($this->user, 'stream', $type),
				'methods'	=> $methods,
			);
		}

		$settingBatchTime = UserSettings::EMAIL_SEND_HOURLY;
		$currentSetting = (int) $this->userSettings->getUserSetting($this->user, 'setting', 'batchtime');
		if ($currentSetting === 3600 * 24 * 7) {
			$settingBatchTime = UserSettings::EMAIL_SEND_WEEKLY;
		} else if ($currentSetting === 3600 * 24) {
			$settingBatchTime = UserSettings::EMAIL_SEND_DAILY;
		}

		return new TemplateResponse('ooba', 'personal', [
			'oobas'		=> $oobas,
			'ooba_email'	=> $this->config->getUserValue($this->user, 'settings', 'email', ''),

			'setting_batchtime'	=> $settingBatchTime,

			'notify_self'		=> $this->userSettings->getUserSetting($this->user, 'setting', 'self'),
			'notify_selfemail'	=> $this->userSettings->getUserSetting($this->user, 'setting', 'selfemail'),

			'methods'			=> [
				IExtension::METHOD_MAIL => $this->l10n->t('Mail'),
				IExtension::METHOD_STREAM => $this->l10n->t('Stream'),
			],
		], '');
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $enable	'true' if the feed is enabled
	 * @return DataResponse
	 */
	public function feed($enable) {
		$token = $tokenUrl = '';

		if ($enable === 'true') {
			$conflicts = true;

			// Check for collisions
			while (!empty($conflicts)) {
				$token = $this->random->generate(30);
				$conflicts = $this->config->getUsersForUserValue('ooba', 'rsstoken', $token);
			}

			$tokenUrl = $this->urlGenerator->linkToRouteAbsolute('ooba.Feed.show', ['token' => $token]);
		}

		$this->config->setUserValue($this->user, 'ooba', 'rsstoken', $token);

		return new DataResponse(array(
			'data'		=> array(
				'message'	=> (string) $this->l10n->t('Your settings have been updated.'),
				'rsslink'	=> $tokenUrl,
			),
		));
	}
}
