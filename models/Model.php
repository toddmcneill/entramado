<?php

abstract class Model {
	
	
	// Should return an array of object variable names.
	protected abstract function getVarNames();
	
	// Should return an associative array that can be inserted in the database.
	protected abstract function getDbSaveArray();
	
	// Should return whether this is a new object or already has been saved.
	protected abstract function isNewObject();
	
	// Should return the table that this object is stored in.
	protected abstract function getTableName();
	
	// Should return the primary key field name for this object.
	protected abstract function getPrimaryKeyName();
	
	// Should return the value of the primary key.
	public abstract function getId();
	
	// Sets the id (primary key field) for the object.
	protected function setId($id) {
		$primary_key_name = $this->getPrimaryKeyName();
		$this->$primary_key_name = $id;
	}
	
	
	// Loads this object from the database specified by the table name and the primary key (id) value.
	protected function loadFromDb($key_value) {
		$query = "SELECT * FROM ".$this->getTableName()." WHERE ".$this->getPrimaryKeyName()." = ?";
		
		$result = DB::query($query, array($key_value));
		if ($row = $result->fetch_object()) {
			$this->loadFromDbRow($row);
		}
	}
	
	
	// Accepts either an object or an associative array representing a database record.
	private function loadFromDbRow($row) {
		
		if (is_object($row)) {
			// The row is an object.
			$row_data = get_object_vars($row);
		} else if (is_array($row)) {
			// The row is an array.
			$row_data = $row;
		} else {
			// The row type is not recognized.
			return;
		}
		
		// Assign class variables from the row data.
		$var_names = $this->getVarNames();
		foreach ($row_data as $var_name => $value) {
			// Only assign the value if it's a defined class variable.
			if (in_array($var_name, $var_names)) {
				$this->$var_name = $value;
			}
		}
		
	}
	
	
	// Saves an object to the database. Inserts if new or updates if existing.
	public function saveToDb() {
		// Get the data from the object that will be saved.
		$db_save_array = $this->getDbSaveArray();
		
		// Prepare the values.
		$values = array_values($db_save_array);
		
		// Insert or update a record in the database.
		if ($this->isNewObject()) {
			// Prepare the field list.
			$field_list = implode(',', array_keys($db_save_array));
			
			// Prepare the placeholder string (a list of ?s)
			$placeholders = array();
			for ($i=0; $i < count($db_save_array); $i++) {
				$placeholders[] = '?';
			}
			$placeholder_str = implode(',', $placeholders);
			
			// Put together the query to create a new record for the object.
			$query = "
				INSERT INTO ".$this->getTableName()."
					(".$field_list.")
				VALUES
					(".$placeholder_str.")
			";
			
			// Run the query.
			DB::query($query, $values);
			
			// Set the id on the object.
			$this->setId(DB::getInsertId());
			
		} else {
			// Prepare the field list.
			$fields = array();
			foreach (array_keys($db_save_array) as $field_name) {
				$field_list[] = $field_name." = ?";
			}
			$field_list = implode(',', $fields);
			
			// Get the primary key name.
			$primary_key_name = $this->getPrimaryKeyName();
			
			// Put together the query to update the record for the object.
			$query = "
				UPDATE ".$this->getTableName()."
				SET
					".$field_list."
				WHERE
					".$primary_key_name." = ?
			";
			$values[] = $this->getId();
			
			// Run the query.
			DB::query($query, $values);
		}
		
	}
	
	
	
	// Auto getter and setter functions.
	// http://stackoverflow.com/a/8743064
	public function __call($method, $params) {
		
		// Auto getter.
		if (strncasecmp($method, 'get', 3) == 0) {
			$var = $this->convertCamelCaseToUnderscore(substr($method, 3));
			return $this->$var;
		}
		// Auto setter.
		if (strncasecmp($method, 'set', 3) == 0) {
			$var = $this->convertCamelCaseToUnderscore(substr($method, 3));
			$this->$var = $params[0];
		}
	}
	
	
	// Convert a camel case variable to underscore.
	// http://stackoverflow.com/a/1993772
	private function convertCamelCaseToUnderscore($str) {
		
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match) {
			$match = (($match == strtoupper($match)) ? strtolower($match) : lcfirst($match));
		}
		return implode('_', $ret);
		
	}
	
	
}
