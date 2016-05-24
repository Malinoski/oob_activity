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

use OC\Files\View;
use OCA\Ooba\Data;
use OCA\Ooba\GroupHelper;
use OCA\Ooba\Navigation;
use OCA\Ooba\UserSettings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files;
use OCP\Files\IMimeTypeDetector;
use OCP\IDateTimeFormatter;
use OCP\IPreview;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Template;

class Oobas extends Controller {
	const DEFAULT_PAGE_SIZE = 30;

	const MAX_NUM_THUMBNAILS = 7;

	/** @var \OCA\Ooba\Data */
	protected $data;

	/** @var \OCA\Ooba\GroupHelper */
	protected $helper;

	/** @var \OCA\Ooba\Navigation */
	protected $navigation;

	/** @var \OCA\Ooba\UserSettings */
	protected $settings;

	/** @var IDateTimeFormatter */
	protected $dateTimeFormatter;

	/** @var IPreview */
	protected $preview;

	/** @var IURLGenerator */
	protected $urlGenerator;

	/** @var IMimeTypeDetector */
	protected $mimeTypeDetector;

	/** @var View */
	protected $view;

	/** @var string */
	protected $user;

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Data $data
	 * @param GroupHelper $helper
	 * @param Navigation $navigation
	 * @param UserSettings $settings
	 * @param IDateTimeFormatter $dateTimeFormatter
	 * @param IPreview $preview
	 * @param IURLGenerator $urlGenerator
	 * @param IMimeTypeDetector $mimeTypeDetector
	 * @param View $view
	 * @param string $user
	 */
	public function __construct($appName,
								IRequest $request,
								Data $data,
								GroupHelper $helper,
								Navigation $navigation,
								UserSettings $settings,
								IDateTimeFormatter $dateTimeFormatter,
								IPreview $preview,
								IURLGenerator $urlGenerator,
								IMimeTypeDetector $mimeTypeDetector,
								View $view,
								$user) {
		parent::__construct($appName, $request);
		$this->data = $data;
		$this->helper = $helper;
		$this->navigation = $navigation;
		$this->settings = $settings;
		$this->dateTimeFormatter = $dateTimeFormatter;
		$this->preview = $preview;
		$this->urlGenerator = $urlGenerator;
		$this->mimeTypeDetector = $mimeTypeDetector;
		$this->view = $view;
		$this->user = $user;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $filter
	 * @return TemplateResponse
	 */
	public function showList($filter = 'all') {
		\OCA\OobActivity\ext\ActivityHelper::oobaDebug("------------------------------------------------ Hii controller =((((((((( !!!!!!!!!!!!!!!");
		
		$filter = $this->data->validateFilter($filter);

		return new TemplateResponse('ooba', 'stream.body', [
			'appNavigation'	=> $this->navigation->getTemplate($filter),
			'filter'		=> $filter,
		]);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $page
	 * @param string $filter
	 * @param string $objecttype
	 * @param int $objectid
	 * @return JSONResponse
	 */
	public function fetch($page, $filter = 'all', $objecttype = '', $objectid = 0) {
		$pageOffset = $page - 1;
		$filter = $this->data->validateFilter($filter);

		$oobas = $this->data->read($this->helper, $this->settings, $pageOffset * self::DEFAULT_PAGE_SIZE, self::DEFAULT_PAGE_SIZE, $filter, '', $objecttype, $objectid);

		$preparedOobas = [];
		foreach ($oobas as $ooba) {
			$ooba['relativeTimestamp'] = (string) Template::relative_modified_date($ooba['timestamp'], true);
			$ooba['readableTimestamp'] = (string) $this->dateTimeFormatter->formatDate($ooba['timestamp']);
			$ooba['relativeDateTimestamp'] = (string) Template::relative_modified_date($ooba['timestamp']);
			$ooba['readableDateTimestamp'] = (string) $this->dateTimeFormatter->formatDateTime($ooba['timestamp']);

			if (strpos($ooba['subjectformatted']['markup']['trimmed'], '<a ') !== false) {
				// We do not link the subject as we create links for the parameters instead
				$ooba['link'] = '';
			}

			$ooba['previews'] = [];
			if ($ooba['object_type'] === 'files' && !empty($ooba['files'])) {
				foreach ($ooba['files'] as $objectId => $objectName) {
					if (((int) $objectId) === 0 || $objectName === '') {
						// No file, no preview
						continue;
					}

					$ooba['previews'][] = $this->getPreview($ooba['affecteduser'], (int) $objectId, $objectName);

					if (sizeof($ooba['previews']) >= self::MAX_NUM_THUMBNAILS) {
						// Don't want to clutter the page, so we stop after a few thumbnails
						break;
					}
				}
			} else if ($ooba['object_type'] === 'files' && $ooba['object_id']) {
				$ooba['previews'][] = $this->getPreview($ooba['affecteduser'], (int) $ooba['object_id'], $ooba['file']);
			}

			$preparedOobas[] = $ooba;
		}

		return new JSONResponse($preparedOobas);
	}

	/**
	 * @param string $owner
	 * @param int $fileId
	 * @param string $filePath
	 * @return array
	 */
	protected function getPreview($owner, $fileId, $filePath) {
		$this->view->chroot('/' . $owner . '/files');
		$path = $this->view->getPath($fileId);

		if ($path === null || $path === '' || !$this->view->file_exists($path)) {
			return $this->getPreviewFromPath($filePath);
		}

		$is_dir = $this->view->is_dir($path);

		$preview = [
			'link'			=> $this->getPreviewLink($path, $is_dir),
			'source'		=> '',
			'isMimeTypeIcon' => true,
		];

		// show a preview image if the file still exists
		if ($is_dir) {
			$preview['source'] = $this->getPreviewPathFromMimeType('dir');
		} else {
			$fileInfo = $this->view->getFileInfo($path);
			if ($this->preview->isAvailable($fileInfo)) {
				$preview['isMimeTypeIcon'] = false;
				$preview['source'] = $this->urlGenerator->linkToRoute('core_ajax_preview', [
					'file' => $path,
					'c' => $this->view->getETag($path),
					'x' => 150,
					'y' => 150,
				]);
			} else {
				$preview['source'] = $this->getPreviewPathFromMimeType($fileInfo->getMimetype());
			}
		}

		return $preview;
	}

	/**
	 * @param string $filePath
	 * @return array
	 */
	protected function getPreviewFromPath($filePath) {
		$mimeType = $this->mimeTypeDetector->detectPath($filePath);
		$preview = [
			'link'			=> $this->getPreviewLink($filePath, false),
			'source'		=> $this->getPreviewPathFromMimeType($mimeType),
			'isMimeTypeIcon' => true,
		];

		return $preview;
	}

	/**
	 * @param string $mimeType
	 * @return string
	 */
	protected function getPreviewPathFromMimeType($mimeType) {
		$mimeTypeIcon = $this->mimeTypeDetector->mimeTypeIcon($mimeType);
		if (substr($mimeTypeIcon, -4) === '.png') {
			$mimeTypeIcon = substr($mimeTypeIcon, 0, -4) . '.svg';
		}

		return $mimeTypeIcon;
	}

	/**
	 * @param string $path
	 * @param bool $isDir
	 * @return string
	 */
	protected function getPreviewLink($path, $isDir) {
		if ($isDir) {
			return $this->urlGenerator->linkTo('files', 'index.php', array('dir' => $path));
		} else {
			$parentDir = (substr_count($path, '/') === 1) ? '/' : dirname($path);
			$fileName = basename($path);
			return $this->urlGenerator->linkTo('files', 'index.php', array(
				'dir' => $parentDir,
				'scrollto' => $fileName,
			));
		}
	}
}
