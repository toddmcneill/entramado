<?php

class Autoload {

	public function __construct() {		
		// Register the autoloader function.
		spl_autoload_register(array($this, 'listLoader'));
		spl_autoload_register(array($this, 'generalLoader'));
  }



	// Autoloads a file from a list based on the class name.
	// This function must be registered as an autoloader using spl_autoload_register.
	private function listLoader($class_name) {
		// Include the file if the location can be found.
		$file_location = $this->getFileLocation($class_name);
		if (file_exists($file_location)) {
			require_once($file_location);
		}
	}

	// Accepts the class name and returns the file location or false if the file location is not registered.
	private function getFileLocation($class_name) {
		$file_list = $this->getFileList();
		if (array_key_exists($class_name, $file_list)) {
			return $file_list[$class_name];
		}
		return false;
	}

	// Contains an associative array of [class_name > file_path] for individual files that can be autoloaded
	// that wouldn't be found in the general autoloader function.
	private function getFileList() {
		$file_list = array(
			// 'class_name' => 'file_location'
		);

		return $file_list;
	}



	// Autoloads a file based on the class name by looking in a set of directories.
	// This function must be registered as an autoloader using spl_autoload_register.
	private function generalLoader($class_name) {
		
		$autoload_dirs = array(
			'models',
			'views',
			'controllers',
			'system'
		);
		
		// Look in each directory for the file.
		foreach ($autoload_dirs as $dir) {
			// Include the file if it can be found.
			$file_location = $dir.'/'.$class_name.'.php';
			if (file_exists($file_location)) {
				require_once($file_location);
			}
		}
		
		
	}
	
}
