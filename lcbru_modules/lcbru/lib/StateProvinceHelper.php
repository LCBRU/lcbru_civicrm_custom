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
 *   $helper = new StateProvibceHelper()
 *   $id = $helper->getIdFromTitle($title)
 *
 * }
 * </code>
 *
 * Things to note are:
 *
*/

class StateProvinceHelper
{

    private $cached = null;

    /**
     * Returns all the location types
     *
     * @return an array of all the location types
     */
    public function getAll() {
        if (empty($this->cached)) {
            // Using the limited function because the Constant object type doesn't seem to work normally
            // and so using the getObjectsAll just hangs.
            $this->cached = CiviCrmApiHelper::getObjectsLimited("Constant", array ('name' =>'stateProvince'));
        }

        return $this->cached;
    }

    /**
     * Get the ID of a location type from its title.
     *
     * @param string $title the title of the location type
     *
     * @return the Id
     * @throws Exception if $title is empty.
     */
    public function getIdFromTitle($title) {
        Guard::AssertString_NotEmpty('$title', $title);

        return array_search($title, $this->getAll());
    }

}
