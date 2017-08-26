<?php

class Autoload {

  private static $autoload_registered = false;
  private static $file_list = [];

	public function __construct() {
	  // Register the autoload functions if they haven't been registered yet.
	  if (!self::$autoload_registered){
	    $this->registerAutoload();
    }
  }

  private function registerAutoload(){
		// Register the autoload functions.
		spl_autoload_register([$this, 'generalLoader']);
		spl_autoload_register([$this, 'listLoader']);

		// Record that the autoload has been registered.
    self::$autoload_registered = true;
  }

	// Autoloads a file based on the class name by looking in a set of directories.
	// This function must be registered as an autoloader using spl_autoload_register.
	private function generalLoader(string $class_name) {
    // Find the folders
		$autoload_dirs = [
			'models',
			'views',
			'controllers',
			'system'
		];

		// Look in each directory for the file.
		foreach ($autoload_dirs as $dir) {
			// Include the file if it can be found.
			$file_location = $dir . '/' . $class_name . '.php';
			if (file_exists($file_location)) {
				require_once($file_location);
			}
		}
	}

	// Autoloads a file from a list based on the class name.
	// This function must be registered as an autoloader using spl_autoload_register.
	private function listLoader(string $class_name) {
		// Include the file if the location can be found.
		$file_location = $this->getFileLocation($class_name);
		if (file_exists($file_location)) {
			require_once($file_location);
		}
	}

	// Accepts the class name and returns the file location or false if the file location is not registered.
	private function getFileLocation(string $class_name) : mixed {
		$file_list = $this->getFileList();
		if (array_key_exists($class_name, $file_list)) {
			return $file_list[$class_name];
		}
		return false;
	}

	// Contains an associative array of [class_name > file_path] for individual files that can be autoloaded
	// that wouldn't be found in the general autoloader function.
	private function getFileList() {
		return self::$file_list;
	}

	// Registers a file with it's path.
  public static function registerClass(string $class_name, string $file_path){
    self::$file_list[$class_name] = $file_path;
  }

  // Registers an array of files with their paths.
  public static function registerClassList(array $class_list){
    foreach ($class_list as $class_name => $file_path){
      self::registerClass($class_name, $file_path);
    }
  }

}
