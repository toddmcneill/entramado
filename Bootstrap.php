<?php

class Bootstrap{

  public static function run() {
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

