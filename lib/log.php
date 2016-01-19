<?php

namespace OCA\Activity;

class LogActivity{
	public static function registerLog($arrayMessage){
		//$parsedArrayMessage = array_values($arrayMessage);
		$parsedArrayMessage = json_encode($arrayMessage, JSON_PRETTY_PRINT);
		$rpath = realpath(".");
		error_log(print_r($parsedArrayMessage."\n",true), 3, $rpath."/data/activitylog.log");
	}
}