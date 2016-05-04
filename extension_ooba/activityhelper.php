<?php
// use \OCP\DB;
// use \OCP\Config;
// use Assetic\Exception\Exception;

//require_once 'apps/activity/lib/db/private/db.php';

// define('ROOT', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR));

namespace OCA\OobActivity\ext;

use OCA\OobActivity\ActivityConnectionFactory;
use OCA\OobActivity\Activity_OC_DB;
use OCA\OobActivity\Activity_DB;
use \OC\DB\MDB2SchemaManager;
use OCA\OobActivity\Service\DebugService;
use Psr\Log\LogLevel;

class ActivityHelper {
	
	//TODO
	public static function prepare() {
		openlog('oobactivity', LOG_NDELAY | LOG_PID, LOG_USER); // must be executed for activity syslo and/or debug sylog		
		syslog(LOG_DEBUG, "iiiiiiiiiiii");
		ActivityHelper::oobaDebug("");
		ActivityHelper::oobaDebug("----------------------");
		ActivityHelper::oobaDebug("# Preparing oobactivity");

		self::getDatabaseConnection();
		self::createOobaDbFromStructure();		
	}
	
	/**
	 * Returns the current session of oobactivity
	 *
	 * @return \OCP\IDBConnection
	 */
	public static function getDatabaseConnection() {
		try {
			ActivityHelper::oobaDebug("Preparing oobactivity connection:");
			
			$factory = new ActivityConnectionFactory();
			$connectionParams = $factory->createOOBActivityConnectionParams();
			ActivityHelper::oobaDebug("- Params:".json_encode($connectionParams));
	 		$connection = $factory->getConnection($connectionParams['dbtype'], $connectionParams);
	 		
	 		//TODO Check this
	 		$connection->getConfiguration()->setSQLLogger(\OC::$server->getQueryLogger());	 		
	 		
// 	 		ob_start();
// 			var_dump($connection->connect());
// 			$result = ob_get_clean();			
// 	 		syslog(LOG_INFO,'>>>>>>>>>>>>>>'.$result.'%%%%%%%%%%%'); 		
	 		
	 		
	 		//syslog(LOG_INFO,'- ooba conection: '.(($connection->connect()) ? 'true' : 'false'));
 			if($connection->connect()){ 				
 				ActivityHelper::oobaDebug("- ooba connection established!");
 			}else {
 				ActivityHelper::oobaDebug('- ooba connection is already open!');
 			} 			

 		} catch (\Exception $e) { 			
 			ActivityHelper::oobaDebug('- ooba prepare exception! =(');
 		}	
 		return $connection;		

	}
	
	public static function createOobaDbFromStructure() {
		ActivityHelper::oobaDebug("- Prepare oobactivity database:");
		$oobaDatabaseFile = __DIR__.'/../appinfo/oobadatabase.xml';
		ActivityHelper::oobaDebug("ooba database xml file: ".$oobaDatabaseFile);
		if(!Activity_OC_DB::tableExists('ooba') && !Activity_OC_DB::tableExists('ooba_mq')){
			ActivityHelper::oobaDebug('ooba tables create =) ');
			Activity_OC_DB::createDbFromStructure($oobaDatabaseFile);
		}else {
			ActivityHelper::oobaDebug('ooba tables are already exist');
		}
	}
	
	/**
	 * Debug mode is configured in config/config.xml.To enable debug mode set 'syslog' or 'owncloud' (owncloud.log).To disable debug mode set ''.
	 *
	 * @return \OCP\IDBConnection
	 */
	public static function oobaDebug($message){
		$oobadebug = \OCP\Config::getAppValue('oobactivity','oobadebug', 'off');
		
		if($oobadebug == "owncloud"){
			\OC_Log_Owncloud::write('oobactivty', $message, LOG_DEBUG);
		} else if($oobadebug == "syslog"){
			syslog(LOG_INFO, $message);
		} 
	}
 
}