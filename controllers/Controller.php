<?php

abstract class Controller {
	
	protected
		$view = null;
	
	// The default action if none is specified.
	public abstract function defaultAction($params);
	
	// Displays the view.
	public function display($ob_contents = '') {
		// Use the default view if no view has been set.
		if (is_null($this->view)) {
			$this->view = new IndexView();
		}
		
		// Print the view.
		echo $this->view->getViewContents($ob_contents);
		
	}
	
}
