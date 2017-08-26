<?php

// Define DB connection parameters.
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'job_hunt_user');
define('DB_PASSWORD', 'K6NdbvSjV6LXy2mK');
define('DB_NAME', 'job_hunt');

class DB {
	
	// This is the single static link used by all database-related functionality.
	public static $link = null;
	
	
	// Establishes a connection to the database if none exists.
	public static function connectToDb() {
		if (is_null(self::$link)) {
			self::$link = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
			
			// Kill the script if there's a database connetion error.
			if (self::$link->connect_error) {
				die("DB connection error [ ".self::$link->connect_errno." ]: ".self::$link->connect_error);
			}
		}
	}
	
	
	// Executes a query and returns the result.
	// If all 3 parameters are used, the query is processed as a prepared statement.
	// $query: A string containing the sql query.
	// $params (optional): An array containing the parameters used in a prepared statement.
	public static function query($query, $params = null) {
		// Make sure a connection exists.
		self::connectToDb();
		
		// Check to see if the query should be executed as a prepared statement.
		if (!is_null($params)) {
			return self::preparedStatementQuery($query, $params);
		}
		
		// Execute the query and get the query result.
		if (!$result = self::$link->query($query)) {
			echo "DB query error [ ".self::$link->errno." ]: ".self::$link->error;
			return false;
		}
		
		// Return the query result.
		return $result;
	}
	
	
	// Executes a query using a prepared statement and returns the result.
	public static function preparedStatementQuery($query, $params) {
		// Make sure a connection exists.
		self::connectToDb();
		
		// Prepare the query.
		$statement = self::$link->prepare($query);
		if (self::$link->errno != 0) {
			echo "DB statement preparation error [ ".self::$link->errno." ]: ".self::$link->error;
			return false;
		}
		
		// Bind parameters.
		// Inspiration taken from https://github.com/joshcam/PHP-MySQLi-Database-Class/blob/master/MysqliDb.php
		$b_params = array('');
		foreach ($params as $key => $value) {
			// Add in the type.
			$b_params[0] .= DB::determineBindType($value);
			
			// Add in the value.
			array_push($b_params, $params[$key]);
		}
		
		call_user_func_array(array($statement, 'bind_param'), DB::convertToReference($b_params));
		if ($statement->errno != 0) {
			echo "DB binding parameters error [ ".$statement->errno." ]: ".$statement->error;
			return false;
		}
		
		// Execute the query.
		$statement->execute();
		if ($statement->errno != 0) {
			echo "DB statement execution error [ ".$statement->errno." ]: ".$statement->error;
			return false;
		}
		
		// Get the query result.
		$result = $statement->get_result();
		if ($statement->errno != 0) {
			echo "DB result fetch error [ ".$statement->errno." ]: ".$statement->error;
			return false;
		}
		
		// Return the query result.
		return $result;
	}
	
	
	// Returns a string containing s, i, b, or d corresponding to the type of variable.
	public static function determineBindType($var) {
		switch (gettype($var)) {
			case 'NULL':
			case 'string':
				return 's';
				break;
			
			case 'boolean':
			case 'integer':
				return 'i';
				break;
			
			case 'blob':
				return 'b';
				break;
			
			case 'double':
				return 'd';
				break;
		}
		return '';
	}
	
	
	// Converts values in an array to references.
	public static function convertToReference(array $arr) {
		$refs = array();
		foreach ($arr as $key => $value) {
			$refs[$key] =& $arr[$key];
		}
		return $refs;
	}
	
	
	// Returns the id auto-generated on the last query.
	public static function getInsertId() {
		return self::$link->insert_id;
	}
	
	
}
