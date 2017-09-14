<?php

/**
 * This class provides utility functions for checking guard conditions
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   Guard::AssertString($value);
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class Guard
{
    public static function AssertString_NotEmpty($name, $value) {
        if (!is_string($name)) {
            throw new InvalidArgumentException("name is not a string " . Guard::getStackTrace());
        }
        if (trim($name)==='') {
            throw new InvalidArgumentException("name cannot be empty" . Guard::getStackTrace());
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException("$name is not a string " . Guard::getStackTrace());
        }
        if (trim($value)==='') {
            throw new InvalidArgumentException("$name cannot be empty" . Guard::getStackTrace());
        }
    }

    public static function AssertString($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!is_string($value)) {
            throw new InvalidArgumentException("$name is not a string " . Guard::getStackTrace());
        }
    }

    public static function AssertString_InArray($name, $value, array $expected) {
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertString('$value', $value);

        if (!in_array($value, $expected)) {
            throw new InvalidArgumentException("$name has a value of '$value' and is not one of the expected values of '" . join('\', \'', $expected) . "' " . Guard::getStackTrace());
        }
    }

    public static function AssertBoolean($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!is_bool($value)) {
            throw new InvalidArgumentException("$name is not a boolean " . Guard::getStackTrace());
        }
    }

    public static function AssertInteger($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!is_numeric($value)) {
            throw new InvalidArgumentException("$name is not an integer " . Guard::getStackTrace());
        }
    }

    public static function AssertNumeric($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!is_numeric($value)) {
            throw new InvalidArgumentException("$name is not numeric " . Guard::getStackTrace());
        }
    }

    public static function AssertArray($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!is_array($value)) {
            throw new InvalidArgumentException("$name is not an array " . Guard::getStackTrace());
        }
    }

    public static function AssertArray_HasColumns($name, $value, array $columns) {
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertArray('$columns', $columns);

        Guard::AssertArray('$value', $value);

        foreach ($columns as $col) {
            foreach ($value as $row) {
                if (!array_key_exists($col, $row)) {
                    throw new InvalidArgumentException("Not all rows of $name contain column $col. " . Guard::getStackTrace());
                }
            }
        }
    }

    public static function AssertArray_HasFields($name, $value, array $fields) {
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertArray('$fields', $fields);

        Guard::AssertArray('$value', $value);

        foreach ($fields as $field) {
            if (!array_key_exists($field, $value)) {
                throw new InvalidArgumentException("Array does not contain field $field. " . Guard::getStackTrace());
            }
        }
    }

    public static function AssertArray_NotEmpty($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        Guard::AssertArray($name, $value);

        if (empty($value)) {
            throw new InvalidArgumentException("$name cannot be empty " . Guard::getStackTrace());
        }
    }

    public static function AssertArrayOfIntegers($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        Guard::AssertArray($name, $value);

        foreach ($value as $k => $v) {
            Guard::AssertInteger("$name instance $k", $v);
        }

    }

    public static function AssertArrayOfStrings($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        Guard::AssertArray($name, $value);

        foreach ($value as $k => $v) {
            Guard::AssertString("$name instance $k", $v);
        }

    }

    public static function AssertArrayOfStrings_NotEmpty($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        Guard::AssertArray($name, $value);

        foreach ($value as $k => $v) {
            Guard::AssertString_NotEmpty("$name instance $k", $v);
        }

    }

    public static function AssertFunction($name, $value) {
        Guard::AssertString_NotEmpty('$name', $name);

        if (!((is_string($value) && function_exists($value)) || (is_object($value) && ($value instanceof Closure)))) {
            throw new InvalidArgumentException("$name is not a function " . Guard::getStackTrace());
        }
    }

    public static function AssertModuleImplementsHook($module, $hook) {
        Guard::AssertString_NotEmpty('$module', $module);
        Guard::AssertString_NotEmpty('$hook', $hook);

        if (!module_hook($module, $hook)) {
            throw new InvalidArgumentException("$module does not implement $hook " . Guard::getStackTrace());
        }
    }

    public static function AssertMax($name, $value, $max) {
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertNumeric('$value', $value);
        Guard::AssertNumeric('$max', $max);

        if ($value > $max) {
            throw new InvalidArgumentException("$name value of '$value' exceeds maximum of '$max' " . Guard::getStackTrace());
        }
    }

    public static function AssertMin($name, $value, $min) {
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertNumeric('$value', $value);
        Guard::AssertNumeric('$min', $min);

        if ($value < $min) {
            throw new InvalidArgumentException("$name value of '$value' below minimum of '$min' " . Guard::getStackTrace());
        }
    }

    private static function getStackTrace() {

        $result = '';

        foreach (debug_backtrace() as $f) {
            $class = empty($f['class']) ? '' : 'in class '.$f['class'];

            if ($class != 'in class Guard') {
                $line = empty($f['line']) ? '' : 'at line '.$f['line'];
                $file = empty($f['file']) ? '' : 'in file '.$f['file'];
                $function = empty($f['function']) ? '' : 'in function '.$f['function'];
                $result .= PHP_EOL."$class $function $line $file;";
            }
        }

        return $result;

    }
}
