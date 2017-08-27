<?php

class DB {
	
	// This is the single static link used by all database-related functionality.
	public static $link = null;

	// Establishes a connection to the database if none exists.
	public static function connectToDb() {
		if (is_null(self::$link)) {
			self::$link = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
			
			// Make sure the connection was successful.
			if (self::$link->connect_error) {
				throw new DBException("DB connection error [ ".self::$link->connect_errno." ]: ".self::$link->connect_error);
			}
		}
	}

	// Executes a query and returns the result.
	// If both parameters are used, the query is processed as a prepared statement.
	// $query: A string containing the sql query.
	// $params (optional): An array containing the parameters used in a prepared statement.
	public static function query(string $query, array $params = null) : mysqli_result {
		// Make sure a connection exists.
		self::connectToDb();
		
		// Check to see if the query should be executed as a prepared statement.
		if (!is_null($params)) {
			return self::preparedStatementQuery($query, $params);
		}
		
		// Execute the query and get the query result.
		if (!$result = self::$link->query($query)) {
			throw new DBException("DB query error [ ".self::$link->errno." ]: ".self::$link->error);
		}
		
		// Return the query result.
		return $result;
	}

	// Executes a query using a prepared statement and returns the result.
	public static function preparedStatementQuery(string $query, array $params) : mysqli_result {
		// Make sure a connection exists.
		self::connectToDb();
		
		// Prepare the query.
		$statement = self::$link->prepare($query);
		if (self::$link->errno != 0) {
      throw new DBException("DB statement preparation error [ ".self::$link->errno." ]: ".self::$link->error);
		}
		
		// Bind parameters.
		// Inspiration taken from https://github.com/joshcam/PHP-MySQLi-Database-Class/blob/master/MysqliDb.php
		$b_params = [''];
		foreach ($params as $key => $value) {
			// Add in the type.
			$b_params[0] .= DB::determineBindType($value);
			
			// Add in the value.
			array_push($b_params, $params[$key]);
		}
		
		call_user_func_array([$statement, 'bind_param'], DB::convertToReference($b_params));
		if ($statement->errno != 0) {
      throw new DBException("DB binding parameters error [ ".$statement->errno." ]: ".$statement->error);
		}
		
		// Execute the query.
		$statement->execute();
		if ($statement->errno != 0) {
      throw new DBException("DB statement execution error [ ".$statement->errno." ]: ".$statement->error);
		}
		
		// Get the query result.
		$result = $statement->get_result();
		if ($statement->errno != 0) {
      throw new DBException("DB result fetch error [ ".$statement->errno." ]: ".$statement->error);
		}
		
		// Return the query result.
		return $result;
	}

	// Returns a string containing s, i, b, or d corresponding to the type of variable.
	public static function determineBindType(mixed $var) : string {
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
	public static function convertToReference(array $arr) : array {
		$refs = [];
		foreach ($arr as $key => $value) {
			$refs[$key] =& $arr[$key];
		}
		return $refs;
	}
	
	// Returns the id auto-generated on the last query.
	public static function getInsertId() : mixed {
		return self::$link->insert_id;
	}
}




// Define the DBException class.
class DBException extends RuntimeException {}

