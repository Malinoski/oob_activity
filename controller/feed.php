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

namespace OCA\Ooba\Controller;

use OCA\Ooba\Data;
use OCA\Ooba\GroupHelper;
use OCA\Ooba\UserSettings;
use OCP\Activity\IManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Util;

class Feed extends Controller {
	const DEFAULT_PAGE_SIZE = 30;

	/** @var \OCA\Ooba\Data */
	protected $data;

	/** @var \OCA\Ooba\GroupHelper */
	protected $helper;

	/** @var \OCA\Ooba\UserSettings */
	protected $settings;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/** @var IManager */
	protected $oobaManager;

	/** @var IConfig */
	protected $config;

	/** @var IFactory */
	protected $l10nFactory;

	/** @var string */
	protected $user;

	/** @var string */
	protected $tokenUser;

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Data $data
	 * @param GroupHelper $helper
	 * @param UserSettings $settings
	 * @param IURLGenerator $urlGenerator
	 * @param IManager $oobaManager
	 * @param IFactory $l10nFactory
	 * @param IConfig $config
	 * @param string $user
	 */
	public function __construct($appName,
								IRequest $request,
								Data $data,
								GroupHelper $helper,
								UserSettings $settings,
								IURLGenerator $urlGenerator,
								IManager $oobaManager,
								IFactory $l10nFactory,
								IConfig $config,
								$user) {
		parent::__construct($appName, $request);
		$this->data = $data;
		$this->helper = $helper;
		$this->settings = $settings;
		$this->urlGenerator = $urlGenerator;
		$this->oobaManager = $oobaManager;
		$this->l10nFactory = $l10nFactory;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function show() {
		try {
			$user = $this->oobaManager->getCurrentUserId();

			$userLang = $this->config->getUserValue($user, 'core', 'lang');

			// Overwrite user and language in the helper
			$l = $this->l10nFactory->get('ooba', $userLang);
			$this->helper->setL10n($l);
			$this->helper->setUser($user);

			$description = (string) $l->t('Personal ooba feed for %s', $user);
			$oobas = $this->data->read($this->helper, $this->settings, 0, self::DEFAULT_PAGE_SIZE, 'all', $user);
		} catch (\UnexpectedValueException $e) {
			$l = $this->l10nFactory->get('ooba');
			$description = (string) $l->t('Your feed URL is invalid');

			$oobas = [
				[
					'ooba_id'	=> -1,
					'timestamp'		=> time(),
					'subject'		=> true,
					'subjectformatted'	=> [
						'full' => $description,
					],
				]
			];
		}

		$response = new TemplateResponse('ooba', 'rss', [
			'rssLang'		=> $l->getLanguageCode(),
			'rssLink'		=> $this->urlGenerator->linkToRouteAbsolute('ooba.Feed.show'),
			'rssPubDate'	=> date('r'),
			'description'	=> $description,
			'oobas'	=> $oobas,
		], '');

		if ($this->request->getHeader('accept') !== null && stristr($this->request->getHeader('accept'), 'application/rss+xml')) {
			$response->addHeader('Content-Type', 'application/rss+xml');
		} else {
			$response->addHeader('Content-Type', 'text/xml; charset=UTF-8');
		}

		return $response;
	}
}
