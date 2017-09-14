<?php

/**
 * This class provides utility functions for working with Cases.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $helper = new LocationTypeHelper()
 *   $locationTypeId = $helper->getIdFromTitle($title)
 *
 * }
 * </code>
 *
 * Things to note are:
 *
*/

class LocationTypeHelper
{

    private $cached = null;

    /**
     * Returns all the location types
     *
     * @return an array of all the location types
     */
    public function getAll() {
        if (empty($this->cached)) {
            $this->cached = CiviCrmApiHelper::getObjectsAll('LocationType');
        }

        return $this->cached;
    }

    /**
     * Get the ID of a location type from its displayName.
     *
     * @param string $displayName the displayName of the location type
     *
     * @return the Id of the location type
     * @throws Exception if $displayName is empty.
     */
    public function getIdFromDisplayName($displayName) {
        Guard::AssertString_NotEmpty('$displayName', $displayName);

        foreach ($this->getAll() as $i) {
            if (strtolower ($i['display_name']) == strtolower ($displayName)) {
                return $i['id'];
            }
        }
    }

}
