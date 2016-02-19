<?php

namespace OCA\Activity;

use OCP\Config;
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
		
		// Format message
		// Json:
		// $parsedArrayMessage = json_encode($arrayMessage, JSON_PRETTY_PRINT);
		// Simple array:
		// $parsedArrayMessage = print_r( $arrayMessage, true );
		
		// Version 01 - Simple log file:
		// LogActivity::writeSimpleLog($parsedArrayMessage, LogActivity::INFO);
		
		// Version 02 - SysLog(rsyslog - ubuntu):
		self::writeToSyslog( $arrayMessage);		
	}
	public static function writeSimpleLog($message, $level) {
		// Path to log file
		$rpath = realpath( "." );
		$path = Config::getAppValue( 'activity', 'logFilePath' );
		$logFile = $rpath . '' . $path;
		
		// Write
		error_log( print_r( $message . "\n", true ), 3, $logFile );
	}
	
	// dedicated file log through rsyslog configuration:
	// 1 - Console:
	// vim /etc/rsyslog.d/30-mycustomname.conf
	// # Log generated log messages to file
	// if $programname == 'oobactivity' then /var/log/oobactivity.log
	// # Uncomment the following to stop logging anything that matches the last rule.
	// 2 - ownCloud config(include):
	// 'log_type' => 'syslog',
	//
	// NOTE:
	// Based in lib/private/log/syslog.php
	public static function writeToSyslog($message) {

		
		if(array_key_exists("subject", $message )) {
			$messageSD = "[" . $message ["subject"] . " ";
			
			//Parsing array (http://tools.ietf.org/html/rfc5424#section-6.3)
			foreach( $message as $key => $value ) {
				$messageSD = $messageSD . $key . "=\"" . $value . "\" ";
			}
			$messageSD = substr($messageSD, 0, strlen( $messageSD )-1 ); // removing last space " "
			$messageSD = $messageSD . "]";
			
			//Using a specific program
			// $app = 'oobactivity';
			// openlog($app, LOG_PID | LOG_CONS, LOG_USER);

			//Write log
			syslog(LOG_SYSLOG,'{app-oobactivity} '.$messageSD );
			// closelog();
		}
	}
}

