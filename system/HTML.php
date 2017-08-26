<?php

class HTML {
	
	// Prepares a string for output.
	public static function encodeOutput($str) {
		return htmlspecialchars($str);
	}
	
	// Prepares a string for output as an html tag parameter.
	public static function encodeAttr($str) {
		return '"'.htmlspecialchars($str, ENT_QUOTES).'"';
	}
	
}
