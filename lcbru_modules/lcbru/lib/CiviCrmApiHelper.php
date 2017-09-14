<?php

/**
 * This class provides utility functions for working with Contact Api.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   CiviCrmApiHelper::createObject('contact', array(...))
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class CiviCrmApiHelper
{
    /**
     * Get objects.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return array of objects
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     */
    public static function getObjectsLimited($objectType, array $parameters = array()) {
        return CiviCrmApiHelper::apiCall($objectType, 'get', $parameters)['values'];
    }

    /**
     * Get object.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return object
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     * @throws Exception if other than one value is returned
     */
    public static function getObject($objectType, array $parameters = array()) {
        $result = CiviCrmApiHelper::getObjectOrNull($objectType, $parameters);

        if (is_null($result)) {
            throw new Exception(__FUNCTION__.' did not find an object: Object Type: '.$objectType.PHP_EOL.'Params: '.print_r($parameters, true).PHP_EOL);
        }

        return $result;
    }

    /**
     * Get object or null if it does not exist.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return object
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     */
    public static function getObjectOrNull($objectType, array $parameters = array()) {
        $result = CiviCrmApiHelper::getObjectsLimited($objectType, $parameters);

        if (count($result) == 0) {
            return null;
        }

        if (count($result) > 1) {
            throw new Exception(__FUNCTION__.' found more than one object: Object Type: '.$objectType.PHP_EOL.'Params: '.print_r($parameters, true).PHP_EOL);
        }

        return array_shift($result);
    }

    /**
     * Get all objects objects.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return array of objects
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     */
    public static function getObjectsAll($objectType, array $parameters = array(), $method="get") {
        $result = array();

        $parameters['version'] = '3';

        $offset = 0;

        do {
            $params = array_merge($parameters, array());
            $params['options'] = array(
                'limit' => '50',
                'offset' => $offset
                );

            $chunk = CiviCrmApiHelper::apiCall(
                      $objectType
                    , $method
                    , $params
                    );

            $result = array_merge($result, $chunk['values']);

            $offset += 50;
        } while ($chunk['count'] == 50);

        return $result;
    }

    /**
     * Creates the object using the API.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return array of created details
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     */
    public static function createObject($objectType, array $parameters) {
        $result = CiviCrmApiHelper::apiCall($objectType, 'create', $parameters);
        return array_shift($result['values']);
    }

    /**
     * Updates the object using the API.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param string $parameters the parameters to create the object
     *
     * @return array of created details
     * @throws Exception if $objectType is empty.
     * @throws Exception if $parameters is not an array.
     * @throws Exception if $parameters does not contain an id field.
     * @throws Exception if the api returns an error.
     */
    public static function updateObject($objectType, array $parameters) {
        Guard::AssertArray_HasFields('$parameters', $parameters, array('id'));

        return CiviCrmApiHelper::createObject($objectType, $parameters);
    }

    /**
     * Delete the object using the API.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param string $parameters the parameters to create the object
     *
     * @return array of created details
     * @throws Exception if $objectType is empty.
     * @throws Exception if $parameters is not an array.
     * @throws Exception if $parameters does not contain an id field.
     * @throws Exception if the api returns an error.
     */
    public static function deleteObject($objectType, array $parameters) {
        Guard::AssertArray_HasFields('$parameters', $parameters, array('id'));

        CiviCrmApiHelper::apiCall($objectType, 'delete', $parameters);
    }

    /**
     * Get field for the object using the API.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     *
     * @return array of object fields
     * @throws Exception if $objectType is empty.
     * @throws Exception if the api returns an error.
     */
    public static function getObjectFields($objectType) {
        return CiviCrmApiHelper::getObjectsAll($objectType, array(), 'getfields');
    }

    /**
     * Creates the object using the API.
     *
     * @param string $objectType the type of object to be created, for example 'Contact'
     * @param array $parameters the parameters to create the object
     *
     * @return the result of the API call
     * @throws Exception if $objectType is empty.
     * @throws Exception if $method is empty.
     * @throws Exception if the api returns an error.
     */
    public static function apiCall($objectType, $method, array $parameters) {
        Guard::AssertString_NotEmpty('$objectType', $objectType);
        Guard::AssertString_NotEmpty('$method', $method);
        Guard::AssertArray('$parameters', $parameters);

        $result = civicrm_api(
              $objectType
            , $method
            , array_merge(
                  array ('version' => '3')
                , $parameters
                ));

        if ($result['is_error']) {
            throw new Exception(__FUNCTION__." Error creating calling api:" . $result['error_message'].PHP_EOL.PHP_EOL.'Object Type: '.$objectType.PHP_EOL.'Method: '.$method.PHP_EOL.'Params: '.print_r($parameters, true).PHP_EOL);
        }

        return $result;
    }

}
