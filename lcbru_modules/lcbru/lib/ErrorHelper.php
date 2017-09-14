<?php

/**
 * This class provides utility functions for working with arrays.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $errors = new ErrorHelper();
 *   $errors.add('An error');
 *
 *   if ($error.no_errors()) {
 *	  // Do some processing
 *   }
 *
 *   $errors.display();
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class ErrorHelper
{

    private $errors = array();

    public function add($error_message) {
    	$this->errors[] = $error_message;
    }

    public function no_errors() {
    	return (count($this->errors) == 0);
    }

    public function display() {
    	foreach ($this->errors as $e) {
    		drupal_set_message($e, 'error');
    	}
    }
}
