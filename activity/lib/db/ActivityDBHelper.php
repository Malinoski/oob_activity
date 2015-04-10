<?php
use \OCP\DB;
use Assetic\Exception\Exception;
require_once 'apps/activity/lib/db/private/db.php';

define('ROOT', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR));


class ActivityDBHelper{
	
	public static function prepareApp(){
		
		//Local configurantion table
		try{
			if(!OC_DB::tableExists('activity_ext_db_conf')){			
				ActivityDBHelper::createConfigurationTable();
				return false;
			}else{				
				//ActivityDBHelper::newActiviteLog('INFO: Table for the new activity app configuration already exists!');

				//Create external activity table
				try {
					
					if(ACTIVITY_DB::connect()){// check connection...
						if(!ACTIVITY_DB::tableExists('activity')){					
							ACTIVITY_DB::createDbFromStructure(__DIR__.'/../../appinfo/databaseNewActivity.xml'); // file name renamed for total control
							ActivityDBHelper::newActiviteLog('INFO: The external tables, for the new activity, were created successfully!');
							ActivityDBHelper::newActiviteLog('INFO: New activity prepared!');
						}
					}
					return false;	
										
				} catch(Exception $e) {
					ActivityDBHelper::newActiviteLog('ERROR: Failed to create external activity tables. Check configuration in oc_activity_ext_db_conf table: '.$e->getMessage());
					return false;
				}
			}
		}catch (Exception $ex){
			ActivityDBHelper::newActiviteLog('ERROR: Error with external configuration table!'.$ex->getMessage());
			return false;
		}		
	}
	
	public static function createConfigurationTable(){
		try{
			OC_DB::createDbFromStructure(__DIR__.'/confdatabase.xml');			
			$query = OC_DB::prepare('INSERT INTO `*PREFIX*activity_ext_db_conf`(`dbusername`, `dbpassword`, `dbname`, `dbhost`)' . ' VALUES(?, ?, ?, ?)');
			$query->execute(array("username","password","activitydbname","external.db.ip"));
			ActivityDBHelper::newActiviteLog('INFO: The configuration tables for the new activity were created successfully. Admin needs to change these values!');
		}catch (Exception $ex){
			ActivityDBHelper::newActiviteLog('ERROR: Failed to create the new activity tables!'.$ex->getMessage());
		}
		
	}
	
	public static function newActiviteLog($message){
		OC_Log::write( 'New Activity', $message, OC_Log::FATAL );
	} 
}