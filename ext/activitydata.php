<?php

/**
 * ownCloud - Activity App
 *
 * @author Frank Karlitschek
 * @copyright 2013 Frank Karlitschek frank@owncloud.org
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

namespace OCA\OobActivity;

use OCP\Activity\IExtension;
use OCP\DB;
use OCP\User;
use OCP\Util;

/**
 * @brief Class for managing the data in the activities
 */
class ActivityData extends Data
{
	public static function send($app, $subject, $subjectparams = array(), $message = '', $messageparams = array(), $file = '', $link = '', $affecteduser = '', $type = '', $prio = IExtension::PRIORITY_MEDIUM) {
		$timestamp = time();
		$user = User::getUser();
		
		if ($affecteduser === '') {
			$auser = $user;
		} else {
			$auser = $affecteduser;
		}

		//TODO
		// store in DB
		//$query = DB::prepare('INSERT INTO `*PREFIX*oobactivity`(`app`, `subject`, `subjectparams`, `message`, `messageparams`, `file`, `link`, `user`, `affecteduser`, `timestamp`, `priority`, `type`)' . ' VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )');
		//$query->execute(array($app, $subject, serialize($subjectparams), $message, serialize($messageparams), $file, $link, $user, $auser, $timestamp, $prio, $type));

		// store in log
		$arrayMessage = array($app, $subject, serialize($subjectparams), $message, serialize($messageparams), $file, $link, $user, $auser, $timestamp, $prio, $type);
		ActivityLogger::registerLog($arrayMessage);
				
		// fire a hook so that other apps like notification systems can connect
		Util::emitHook('OC_Activity', 'post_event', array('app' => $app, 'subject' => $subject, 'user' => $user, 'affecteduser' => $affecteduser, 'message' => $message, 'file' => $file, 'link'=> $link, 'prio' => $prio, 'type' => $type));

		return true;
	}

// 	public static function storeMail($app, $subject, array $subjectParams, $affectedUser, $type, $latestSendTime) {
// 		$timestamp = time();

// 		// store in DB
// 		$query = DB::prepare('INSERT INTO `*PREFIX*oobactivity_mq` '
// 			. ' (`amq_appid`, `amq_subject`, `amq_subjectparams`, `amq_affecteduser`, `amq_timestamp`, `amq_type`, `amq_latest_send`) '
// 			. ' VALUES(?, ?, ?, ?, ?, ?, ?)');
// 		$query->execute(array(
// 			$app,
// 			$subject,
// 			serialize($subjectParams),
// 			$affectedUser,
// 			$timestamp,
// 			$type,
// 			$latestSendTime,
// 		));

// 		// fire a hook so that other apps like notification systems can connect
// 		Util::emitHook('OC_Activity', 'post_email', array(
// 			'app'			=> $app,
// 			'subject'		=> $subject,
// 			'subjectparams'	=> $subjectParams,
// 			'affecteduser'	=> $affectedUser,
// 			'timestamp'		=> $timestamp,
// 			'type'			=> $type,
// 			'latest_send'	=> $latestSendTime,
// 		));

// 		return true;
// 	}	
}
