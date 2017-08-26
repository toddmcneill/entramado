<?php
	
	// Adapted from: http://stackoverflow.com/a/21637531
	class Calling {
	
		public static function run(string $query_string = null) {
			// Parse out the controller name and action into the call params.
			if (is_null($query_string)) {
				$q_string = $_SERVER['QUERY_STRING'];
			} else {
				$q_string = $query_string;
			}
			$q_string = preg_replace('/(\/$|^\/)/', '', $q_string);
			$call_params = (!empty($q_string) ? explode('/', $q_string) : []);
			
			// All controllers have suffix "Controller", such as NameController.php and a matching class name.
			// If no controller name given, use IndexController.php.
			if (count($call_params) > 0) {
				$controller_name = (class_exists(ucfirst($call_params[0]).'Controller') ? ucfirst(array_shift($call_params)) : 'Error');
			} else {
				$controller_name = 'Index';
			}
			$controller_class_name = $controller_name.'Controller';
			
			// All public methods have suffix "Action", such as myMethodAction.
			// If there is no method named, use defaultAction
			if (count($call_params) > 0 && method_exists($controller_class_name, $call_params[0].'Action')) {
				$action_name = array_shift($call_params);
			} else {
				$action_name = 'Default';
			}
			$action_function_name = $action_name.'Action';
			
			// Get the params.
			$params = new stdClass();
			for($i = 0; $i < count($call_params); $i += 2) {
				$params->{$call_params[$i]} = (isset($call_params[$i + 1]) ? $call_params[$i+1] : null);
			}
			
			// Init the Controller.
			$controller = new $controller_class_name();
			
			// Start output buffering.
			ob_start();
			
			// Run the action.
			$controller->$action_function_name($params);
			
			// Pass the contents of the output buffer to be displayed as part of the view.
			$ob_contents = ob_get_clean();
			
			// Display the view.
			$controller->display($ob_contents);
		}
	}
