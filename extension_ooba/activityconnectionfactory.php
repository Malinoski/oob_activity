<?php

namespace OCA\OobActivity;

use OC\DB\ConnectionFactory;

class ActivityConnectionFactory extends ConnectionFactory{
	
	/**
	 * Create the connection parameters for the config
	 * @return array
	 */
	public function createOOBActivityConnectionParams() {
		
		syslog(LOG_INFO,'oobactivity - createOOBActivityConnectionParams - config.php - dbuser:'.\OCP\Config::getAppValue('oobactivity', 'dbuser', 'error'));
		$type = \OCP\Config::getAppValue('oobactivity','dbtype', 'sqlite');
		
		$connectionParams = array(
			'user' => \OCP\Config::getAppValue('oobactivity','dbuser', ''),
			'password' =>  \OCP\Config::getAppValue('oobactivity','dbpassword', ''),
			'dbtype' =>  $type,
		);
		//syslog(LOG_INFO,'++++++333+++++++'.json_encode($connectionParams));
		$name =  \OCP\Config::getAppValue('oobactivity','dbname', 'owncloud');

		if ($this->normalizeType($type) === 'sqlite3') {
			$dataDir =  \OCP\Config::getAppValue('oobactivity',"datadirectory", \OC::$SERVERROOT . '/data');
			$connectionParams['path'] = $dataDir . '/' . $name . '.db';
		} else {
			$host =  \OCP\Config::getAppValue('oobactivity','dbhost', '');
			if (strpos($host, ':')) {
				// Host variable may carry a port or socket.
				list($host, $portOrSocket) = explode(':', $host, 2);
				if (ctype_digit($portOrSocket)) {
					$connectionParams['port'] = $portOrSocket;
				} else {
					$connectionParams['unix_socket'] = $portOrSocket;
				}
			}
			$connectionParams['host'] = $host;
			$connectionParams['dbname'] = $name;			
		}

		$connectionParams['tablePrefix'] =  \OCP\Config::getAppValue('oobactivity','dbtableprefix', 'oc_');
		$connectionParams['sqlite.journal_mode'] =  \OCP\Config::getAppValue('oobactivity','sqlite.journal_mode', 'WAL');

		//additional driver options, eg. for mysql ssl
		$driverOptions =  \OCP\Config::getAppValue('oobactivity','dbdriveroptions', null);
		if ($driverOptions) {
			$connectionParams['driverOptions'] = $driverOptions;
		}
		

		return $connectionParams;
	}
}
