<?php

/**
 * This class provides utility functions for working with Session.
 * 
 * Example usage:
 *
 * <code>
 *
 *    SessionHelper::set('module', 'variable name', $value);
 *
 *    ...
 *
 *    $value = SessionHelper::get('module', 'variable name');
 *
 * </code>
 * 
*/

class SessionHelper
{
    public static function set($module, $name, $value) {
        Guard::AssertString_NotEmpty('$module', $module);
        Guard::AssertString_NotEmpty('$name', $name);

        SessionHelper::ensure_module_exists($module);

        $_SESSION[$module][$name] = $value;
    }

    public static function get($module, $name, $default = NULL) {
        Guard::AssertString_NotEmpty('$module', $module);
        Guard::AssertString_NotEmpty('$name', $name);

        SessionHelper::ensure_module_exists($module);
 
        if (empty($_SESSION[$module][$name])) {
            return $default;
        } else {
            return $_SESSION[$module][$name];
        }
    }

    private static function ensure_module_exists($module) {
        Guard::AssertString_NotEmpty('$module', $module);

        if(!isset($_SESSION[$module])) {
            $_SESSION[$module] = array();
        }
    }
}