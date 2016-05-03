<?php

//namespace OCA\Activity;
namespace OCA\OobActivity;

//use OCP\ILogger;
//use OC\Log;
//use Psr\Log\AbstractLogger;
//use Psr\Log\LoggerInterface;
//use Psr\Log\LogLevel;

class ActivityLogger {
	
	function __construct() {
		if(!get_cfg_var('define_syslog_variables')){
		    define_syslog_variables();
		}
	}
	
	public static function registerLog($arrayMessage) {
		
		// Version 01 - Simple log file:
		// LogActivity::writeSimpleLog($parsedArrayMessage, LogActivity::INFO);
		
		// Version 02 - SysLog(rsyslog):
		self::writeToSyslog($arrayMessage);		
	}
	
	public static function parseArrayToJSON($arrayMessage){
		$parsedArrayMessage = json_encode($arrayMessage, JSON_PRETTY_PRINT);
		$parsedArrayMessage = print_r( $arrayMessage, true );
		return $parsedArrayMessage;
	}
	
	public static function writeSimpleLog($message, $level) {
		// Path to log file
		$rpath = realpath( "." );
		$path = \OCP\Config::getAppValue( 'activity', 'logFilePath' );
		$logFile = $rpath . '' . $path;
		
		// Write
		error_log( print_r( $message . "\n", true ), 3, $logFile );
	}
	
	
	//ex.: oc7
	// 	[app] => files
	// 	[subject] => deleted_self
	// 	[subjectparams] => a:1:{i:0;s:13:"/activityTest";}
	// 	[message] =>
	// 	[messageparams] => a:0:{}
	// 	[file] => /activityTest
	// 	[link] => http://localhost/owncloud-7.0.4-logactivity/index.php/apps/files?dir=%2F
	// 	[user] => admin
	// 	[affecteduser] => admin
	// 	[timestamp] => 1455553990
	// 	[priority] => 40
	// 	[type] => file_deleted

	// ex.: oc8
	// 	[0] => files
	// 	[1] => changed_self
	// 	[2] => a:1:{i:0;s:9:"/novo.txt";}
	// 	[3] =>
	// 	[4] => a:0:{}
	// 	[5] => /novo.txt
	// 	[6] => http://localhost/owncloud-8.0.5-logactivity/index.php/apps/files?dir=%2F
	// 	[7] => iuri
	// 	[8] => iuri
	// 	[9] => 1458587790
	// 	[10] => 40
	// 	[11] => file_changed
	
	public static function writeToSyslog($message) {

		// The "subject" tag is the id (created_self,deleted_self,shared_with_by,etc.)
		$messageSD = "[".$message['subject'] ." ";
			
		//Parsing array (http://tools.ietf.org/html/rfc5424#section-6.3)
		foreach( $message as $key => $value ) {
			$messageSD = $messageSD . $key . "=\"" . $value . "\" ";
		}
		$messageSD = substr($messageSD, 0, strlen( $messageSD )-1 ); // removing last space " "
		$messageSD = $messageSD . "]";
			
		//Ref.: http://php.net/manual/en/function.openlog.php		
		openlog('oobactivity', LOG_NDELAY | LOG_PID, LOG_USER);
		
		//Write log
		syslog(LOG_INFO,$messageSD);
	}
}

