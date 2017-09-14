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
 *   $value = ArrayHelper::get($array, $key, $default)
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class ArrayHelper
{

    public static function get(array $array, $key, $default = NULL) {
    	$result = array_key_exists($key, $array) ? $array[$key] : $default;
    	return empty($result) ? $default : $result;
    }

	public static function objectToArray ($object) {
	    if(!is_object($object) && !is_array($object))
	        return $object;

	    return array_map('ArrayHelper::objectToArray', (array) $object);
	}

	public static function translateKeys(array $subject, array $translations) {
		Guard::AssertArray('$subject', $subject);
		Guard::AssertArrayOfStrings('$translations', $translations);

		$result = array();

		foreach ($subject as $key => $value) {
			$new_key = array_key_exists($key, $translations) ? $translations[$key] : $key;
			$result[$new_key] = $value;
		}

		return $result;
	}

	public static function selectColumns(array $row, array $columns) {
		Guard::AssertArray('$row', $row);
		Guard::AssertArrayOfStrings('$columns', $columns);

		$result = [];

		foreach ($columns as $column) {
			$result[$column] = ArrayHelper::get($row, $column, NULL);
		}

		return $result;
	}

	public static function array_iunique($array) {
    	return array_intersect_key(
        	$array,
        	array_unique(array_map("StrToLower",$array))
    	);
	}

}
