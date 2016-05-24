<?php

namespace OCA\Ooba\Controller;

use \OCA\Ooba\Controller\Oobas;
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

class ActivitiesExtension extends Oobas {

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $filter
	 * @return TemplateResponse
	 */
	public function showSyslog($filter = 'all') {
 		\OCA\OobActivity\ext\ActivityHelper::oobaDebug("------------------------------------------------ Hii ActivitiesExtension!!!!!!!!!!!!!!!");
// 		$filter = $this->data->validateFilter($filter);

// 		return new TemplateResponse('ooba', 'stream.extendedbody', [
// 			'appExtendednavigation'	=> $this->navigation->getTemplate($filter),
// 			'filter'		=> $filter,
// 		]);

		$filter = $this->data->validateFilter($filter);
		
		return new TemplateResponse('ooba', 'stream.body', [
				'appNavigation'	=> $this->navigation->getTemplate($filter),
				'filter'		=> $filter,
		]);
	}	
}
