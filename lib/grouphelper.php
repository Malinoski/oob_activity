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

use OCP\Activity\IManager;
use OCP\IL10N;

class GroupHelper
{
	/** @var array */
	protected $oobas = array();

	/** @var array */
	protected $openGroup = array();

	/** @var string */
	protected $groupKey = '';

	/** @var int */
	protected $groupTime = 0;

	/** @var bool */
	protected $allowGrouping;

	/** @var \OCP\Activity\IManager */
	protected $oobaManager;

	/** @var \OCA\Ooba\DataHelper */
	protected $dataHelper;

	/**
	 * @param \OCP\Activity\IManager $oobaManager
	 * @param \OCA\Ooba\DataHelper $dataHelper
	 * @param bool $allowGrouping
	 */
	public function __construct(IManager $oobaManager, DataHelper $dataHelper, $allowGrouping) {
		$this->allowGrouping = $allowGrouping;

		$this->oobaManager = $oobaManager;
		$this->dataHelper = $dataHelper;
	}

	/**
	 * @param string $user
	 */
	public function setUser($user) {
		$this->dataHelper->setUser($user);
	}

	/**
	 * @param IL10N $l
	 */
	public function setL10n(IL10N $l) {
		$this->dataHelper->setL10n($l);
	}

	/**
	 * Add an ooba to the internal array
	 *
	 * @param array $ooba
	 */
	public function addOoba($ooba) {
		$ooba['subjectparams_array'] = $this->dataHelper->getParameters($ooba['subjectparams']);
		$ooba['messageparams_array'] = $this->dataHelper->getParameters($ooba['messageparams']);

		$groupKey = $this->getGroupKey($ooba);
		if ($groupKey === false) {
			if (!empty($this->openGroup)) {
				$this->oobas[] = $this->openGroup;
				$this->openGroup = array();
				$this->groupKey = '';
				$this->groupTime = 0;
			}
			$this->oobas[] = $ooba;
			return;
		}

		// Only group when the event has the same group key
		// and the time difference is not bigger than 3 days.
		if ($groupKey === $this->groupKey &&
			abs($ooba['timestamp'] - $this->groupTime) < (3 * 24 * 60 * 60)
			&& (!isset($this->openGroup['ooba_ids']) || sizeof($this->openGroup['ooba_ids']) <= 5)
		) {
			$parameter = $this->getGroupParameter($ooba);
			if ($parameter !== false) {
				if (!is_array($this->openGroup['subjectparams_array'][$parameter])) {
					$this->openGroup['subjectparams_array'][$parameter] = array($this->openGroup['subjectparams_array'][$parameter]);
				}
				if (!isset($this->openGroup['ooba_ids'])) {
					$this->openGroup['ooba_ids'] = [(int) $this->openGroup['ooba_id']];
					$this->openGroup['files'] = [
						(int) $this->openGroup['object_id'] => (string) $this->openGroup['file']
					];
				}

				$this->openGroup['subjectparams_array'][$parameter][] = $ooba['subjectparams_array'][$parameter];
				$this->openGroup['subjectparams_array'][$parameter] = array_unique($this->openGroup['subjectparams_array'][$parameter]);
				$this->openGroup['ooba_ids'][] = (int) $ooba['ooba_id'];

				$this->openGroup['files'][(int) $ooba['object_id']] = (string) $ooba['file'];
			}
		} else {
			$this->closeOpenGroup();

			$this->groupKey = $groupKey;
			$this->groupTime = $ooba['timestamp'];
			$this->openGroup = $ooba;
		}
	}

	/**
	 * Closes the currently open group and adds it to the list of oobas
	 */
	protected function closeOpenGroup() {
		if (!empty($this->openGroup)) {
			$this->oobas[] = $this->openGroup;
		}
	}

	/**
	 * Get grouping key for an ooba
	 *
	 * @param array $ooba
	 * @return false|string False, if grouping is not allowed, grouping key otherwise
	 */
	protected function getGroupKey($ooba) {
		if ($this->getGroupParameter($ooba) === false) {
			return false;
		}

		// FIXME
		// Non-local users are currently not distinguishable, so grouping them might
		// remove the information how many different users performed the same action.
		// So we do not group them anymore, until we found another solution.
		if ($ooba['user'] === '') {
			return false;
		}

		return $ooba['app'] . '|' . $ooba['user'] . '|' . $ooba['subject'] . '|' . $ooba['object_type'];
	}

	/**
	 * Get the parameter which is the varying part
	 *
	 * @param array $ooba
	 * @return bool|int False if the ooba should not be grouped, parameter position otherwise
	 */
	protected function getGroupParameter($ooba) {
		if (!$this->allowGrouping) {
			return false;
		}

		// Allow other apps to group their notifications
		return $this->oobaManager->getGroupParameter($ooba);
	}

	/**
	 * Get the prepared oobas
	 *
	 * @return array translated oobas ready for use
	 */
	public function getOobas() {
		$this->closeOpenGroup();

		$return = array();
		foreach ($this->oobas as $ooba) {
			$this->oobaManager->setFormattingObject($ooba['object_type'], $ooba['object_id']);
			$ooba = $this->dataHelper->formatStrings($ooba, 'subject');
			$ooba = $this->dataHelper->formatStrings($ooba, 'message');

			$ooba['typeicon'] = $this->oobaManager->getTypeIcon($ooba['type']);
			$return[] = $ooba;
		}
		$this->oobaManager->setFormattingObject('', 0);

		return $return;
	}
}
