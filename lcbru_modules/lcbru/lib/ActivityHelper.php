<?php

/**
 * This class provides utility functions for working with activities.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $selectOptions = ActivityHelper::getCaseTypeSelectOptions()
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class ActivityHelper
{

    /**
     * Constructor.
     */
    public function __construct() {
        $this->customFieldHelper = new CustomFieldHelper();
    }

    public function getActivitiesFromIds(array $ids, array $params = NULL) {
        Guard::AssertArrayOfIntegers('$ids', $ids);

        $p = is_null($params) ? array() : $params;

        $result = array();
        
        foreach ($ids as $id) {
            $parameters = array_merge($p, array('id' => $id));
            $a = CiviCrmApiHelper::getObjectOrNull('activity', $parameters);

            if ($a) {
                $result[] = $a;
            }
        }

        return $result;
    }

    public function saveActivity(array $activity) {
        return CiviCrmApiHelper::apiCall('Activity', 'create', $activity);
    }

}
