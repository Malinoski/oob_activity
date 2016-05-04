<?php

/**
 * This class is a modified copy of owncloud/lib/public/db.php, to be exclusively used by oobactivity app.
 */

namespace OCA\OobActivity;

use OCA\OobActivity\Activity_OC_DB as OC_DB;
use OCA\OobActivity\ext\ActivityHelper;

/**
 * This class provides access to the activity database.
 */
class Activity_DB {
	/**
	 * Prepare a SQL query
	 * @param string $query Query string
	 * @param int $limit Limit of the SQL statement
	 * @param int $offset Offset of the SQL statement
	 * @return \OC_DB_StatementWrapper prepared SQL query
	 *
	 * SQL query via Doctrine prepare(), needs to be execute()'d!
	 */
	static public function prepare( $query, $limit=null, $offset=null ) {
		return(OC_DB::prepare($query, $limit, $offset));
	}

	/**
	 * Insert a row if the matching row does not exists.
	 *
	 * @param string $table The table name (will replace *PREFIX* with the actual prefix)
	 * @param array $input data that should be inserted into the table  (column name => value)
	 * @param array|null $compare List of values that should be checked for "if not exists"
	 *				If this is null or an empty array, all keys of $input will be compared
	 * @return int number of inserted rows
	 * @throws \Doctrine\DBAL\DBALException
	 *
	 */
	public static function insertIfNotExist($table, $input, array $compare = null) {
		//return \OC::$server->getDatabaseConnection()->insertIfNotExist($table, $input, $compare);
		return ActivityHelper::getDatabaseConnection()->insertIfNotExist($table, $input, $compare);
		
	}

	/**
	 * Gets last value of autoincrement
	 * @param string $table The optional table name (will replace *PREFIX*) and add sequence suffix
	 * @return string
	 *
	 * \Doctrine\DBAL\Connection lastInsertID()
	 *
	 * Call this method right after the insert command or other functions may
	 * cause trouble!
	 */
	public static function insertid($table=null) {
		return(OC_DB::insertid($table));
	}

	/**
	 * Start a transaction
	 */
	public static function beginTransaction() {
		OC_DB::beginTransaction();
	}

	/**
	 * Commit the database changes done during a transaction that is in progress
	 */
	public static function commit() {
		OC_DB::commit();
	}

	/**
	 * Rollback the database changes done during a transaction that is in progress
	 */
	public static function rollback() {
		OC_DB::rollback();
	}

	/**
	 * Check if a result is an error, works with Doctrine
	 * @param mixed $result
	 * @return bool
	 */
	public static function isError($result) {
		return(OC_DB::isError($result));
	}

	/**
	 * returns the error code and message as a string for logging
	 * works with DoctrineException
	 * @param mixed $error
	 * @return string
	 */
	public static function getErrorMessage($error) {
		return(OC_DB::getErrorMessage($error));
	}

}
