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
use OpenCloud\Common\Log\Logger;
use OC\OCS\Exception;
use Doctrine\DBAL\DBALException;

class ActivityHelper {
	
	//TODO
	public static function prepare() {
		ActivityHelper::oobaDebug("########### Preparing oobactivity");		
		return self::checkDatabase();	
	}
	
	/**
	 * Returns the current session of oobactivity
	 *
	 * @return \OCP\IDBConnection
	 */
	public static function getDatabaseConnection() {
		$connection = null;
		try {
			ActivityHelper::oobaDebug("- getDatabaseConnection:...");
			
			$factory = new ActivityConnectionFactory();
			$connectionParams = $factory->createOOBActivityConnectionParams();
			//ActivityHelper::oobaDebug("- connection params: ".json_encode($connectionParams));			
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
 		ActivityHelper::oobaDebug("- getDatabaseConnection: OK");
 		return $connection;		

	}
	
	/**
	 * @return bool
	 */
	public static function checkDatabase() {
		ActivityHelper::oobaDebug("- checkDatabase:");
		
		//Check Database
		$username 		= \OCP\Config::getAppValue('oobactivity','dbuser');
		$password 		= \OCP\Config::getAppValue('oobactivity','dbpassword');
		$host			= \OCP\Config::getAppValue('oobactivity','dbhost');
		$oobdatabase 	= \OCP\Config::getAppValue('oobactivity','dbname');
				
		//TODO - Was not found in owncloud Doctrine how to create the database dynamicaly, so, was used checkPgSQLDatabase for this purpose.
		if(self::checkPgSQLDatabase($username, $password, $host, $oobdatabase)){
			
			//Check Tables
			ActivityHelper::oobaDebug("- check table exists (ooba and ooba_mq)");
			if(!Activity_OC_DB::tableExists('ooba') && !Activity_OC_DB::tableExists('ooba_mq')){
				//Load Schema
				$oobaDatabaseFile = __DIR__.'/../appinfo/oobadatabase.xml';
				ActivityHelper::oobaDebug("- ooba database xml file: ".$oobaDatabaseFile);
					
				Activity_OC_DB::createDbFromStructure($oobaDatabaseFile);
				ActivityHelper::oobaDebug('- ooba tables create =) ');								
			}else {
				ActivityHelper::oobaDebug('- ooba tables are already exist');				
			}	
			return true;
		}
		return false;		
	}
	
	/**
	 * Debug mode is configured in config/config.xml.To enable debug mode set 'syslog' or 'owncloud' (owncloud.log).To disable debug mode set ''.
	 *
	 * @return \OCP\IDBConnection
	 */
	public static function oobaDebug($message){
		$oobadebug = \OCP\Config::getAppValue('oobactivity','oobadebug', 'off');
		
		if($oobadebug == "owncloud"){
			\OC_Log_Owncloud::write('oobactivty', 'OOBADEBUG: '.$message, LOG_DEBUG);
		} else if($oobadebug == "syslog"){
			syslog(LOG_INFO, 'OOBADEBUG: '.$message);
		} 
	}
	
	/**
	 * This function is used to check if the $oobdatabase exists in postgresql. 
	 * If not, is used 'template1' database to connect and create $oobdatabase database.
	 *
	 * @return bool
	 */
	public static function checkPgSQLDatabase($username, $password, $host, $database){
		$result = false;
		$dbtype	= \OCP\Config::getAppValue('oobactivity','dbtype');
		//ActivityHelper::oobaDebug("- database params: username=".$username.', password='.$password.', host='.$host.', database='.$database.', dbtype='.$dbtype);
		
		if ($dbtype != 'pgsql'){
			$message = " - dbtype=".$dbtype.": Sorry, the current version of oobactivity only work with postgresql! =(. Use 'pgsql'. in config.xml for oobactivity";			
			\OC_Log_Owncloud::write('oobactivity', $message, \OCP\Util::FATAL);			
			ActivityHelper::oobaDebug($message);
			return false;
		}
		
		$connectionOptions = array(
				'driver' => 'pdo_pgsql',
				'dbname' => 'template1',
				'user' => $username,
				'password' => $password,
				'host' => $host,
		);
		$driver = new \Doctrine\DBAL\Driver\PDOPgSql\Driver;
		$conn = new \Doctrine\DBAL\Connection($connectionOptions, $driver);
		
		//Check connection
		try{
			$result = $conn->ping();
			if($result){
				ActivityHelper::oobaDebug("- postgresql connection ping success.");
				$sm = $conn->getSchemaManager();
				$databases = $sm->listDatabases();
				//ActivityHelper::oobaDebug("- check if oobactivity database \"".$database."\") exists.".json_encode($databases));
				if(!in_array($database, $databases)){
					$sm->createDatabase($database);
					ActivityHelper::oobaDebug("- the oobactivity database (".$database.") was created!");
				}else{
					ActivityHelper::oobaDebug("- the oobactivity database (".$database.") already exists");
				}			
			}else{
				ActivityHelper::oobaDebug("- postgresql connection failed. =(");			
			}
		}catch (\Exception $ex){
			
		}
		return $result;
	}
 
}