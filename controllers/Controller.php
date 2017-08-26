<?php

abstract class Controller {
	
	protected $view = null;
	
	// The default action if none is specified.
	public abstract function defaultAction($params);
	
	// Displays the view.
	public function display($ob_contents = '') {
		// Make sure a view has been set.
		if (is_null($this->view)) {
			throw new RuntimeException('No view specified.');
		}
		
		// Print the view.
		echo $this->view->getViewContents($ob_contents);
	}
	
}
