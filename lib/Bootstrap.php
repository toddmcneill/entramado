<?php

class Bootstrap{

  public static function run() {

    // TODO: move this stuff to a config file.

    // Define DB connection parameters.
    define('DB_HOST', 'localhost');
    define('DB_USERNAME', 'dbuser');
    define('DB_PASSWORD', 'supersecretpassword');
    define('DB_NAME', 'entramado');

    define('SITE_TITLE', 'Site Title');



    // Include Autoload.php and instantiate it, which registers the autoloader functions.
    require_once('system/Autoload.php');
    new Autoload();

    // Start the session.
    session_start();

    // Post/Get/Redirect.
    Params::postGetRedirect();

    // Run everything.
    Calling::run();

    // Clear params out of the session.
    Params::clearSessionParams();
  }

}

