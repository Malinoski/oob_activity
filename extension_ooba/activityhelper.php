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

class ActivityHelper {
	
	//TODO
	public static function prepareDb() {
//  		try {
// 			\\Create external activity table
//  			if (ACTIVITY_DB::connect ()) { // check connection...
// 				if (! ACTIVITY_DB::tableExists ( 'activity' )) { // check if the table exists...
// 					ActivityHelper::newActiviteLog ( 'Activity INFO: Creating external tables for the new activity...' );
// 					ACTIVITY_DB::createDbFromStructure( __DIR__ . '/../../appinfo/databaseNewActivity.xml' ); // file name renamed for total control
// 					ActivityHelper::newActiviteLog ( 'Activity INFO: The external tables for the new activity, were created successfully!' );
// 				}
//  			}
// 			//return false;
//  		} catch (Exception $ex){
 			//return false;
//  		}

    	//syslog(LOG_INFO,'0---');
		
// 		//conexao - funcionando
//  		$factory = new ActivityConnectionFactory();
//  		$connectionParams = $factory->createOOBActivityConnectionParams();
//  		$connection = $factory->getConnection($connectionParams['dbtype'], $connectionParams);		
		
//  		$schemaManager = new \OC\DB\MDB2SchemaManager($connection);
		
// 		//schema reader
// 		$schemaReader = new MDB2SchemaReader(\OC::$server->getConfig(), $this->conn->getDatabasePlatform());
// 		$toSchema = $schemaReader->loadSchemaFromFile(__DIR__.'/../appinfo/database.xml');
// 		$schemaReader->executeSchemaChange($toSchema)->createDbFromStructure(__DIR__.'/../appinfo/database.xml');
		
// 		$schemaManager->createDbFromStructure(__DIR__.'/../appinfo/database.xml');
		
		
	}
	
	/**
	 * Returns the current session
	 *
	 * @return \OCP\IDBConnection
	 */
	public static function getDatabaseConnection() {
		//return $this->query('DatabaseConnection');
		
		$factory = new ActivityConnectionFactory();
		$connectionParams = $factory->createOOBActivityConnectionParams();
		syslog(LOG_INFO,'Connection Params: '.json_encode($connectionParams));
 		$connection = $factory->getConnection($connectionParams['dbtype'], $connectionParams); 		
 		
 		//TODO Check this
 		//$connection->getConfiguration()->setSQLLogger($c->getQueryLogger());		
 		
 		return $connection;		

	}
	
// 	/**
// 	 * Creates tables from XML file
// 	 * @param string $file file to read structure from
// 	 * @return bool
// 	 *
// 	 * TODO: write more documentation
// 	 */
// 	public function createDbFromStructure($file) {
// 		$schemaReader = new MDB2SchemaReader(\OC::$server->getConfig(), $this->conn->getDatabasePlatform());
// 		$toSchema = $schemaReader->loadSchemaFromFile($file);
// 		return $this->executeSchemaChange($toSchema);
// 	}
 
}