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
 *   $helper = new OptionValueHelper('OptionValueType')
 *   $id = $helper->getIdFromTitle($title)
 *
 * }
 * </code>
 *
 * Things to note are:
 *
*/

class OptionValueHelper
{
    const PHONE_TYPE = 'phone_type';
    const PREFERRED_COMMUNICATION_METHOD = 'preferred_communication_method';
    const INDIVIDUAL_PREFIX = 'individual_prefix';
    const GENDER = 'gender';
    const CASE_STATUS = 'case_status';
    const ACTIVITY_TYPE = 'activity_type';
    const ACTIVITY_STATUS = 'activity_status';

    private $cached = null;

    /**
     * Constructor.
     *
     * @param string $optionGroup
     */
     public function __construct($optionGroup) {
        if (is_numeric($optionGroup)) {
            Guard::AssertInteger('$optionGroup', $optionGroup);

            $this->optionGroup = CiviCrmApiHelper::getObject('OptionGroup', array('id' => $optionGroup));
        } else {
            Guard::AssertString_NotEmpty('$optionGroup', $optionGroup);

            Guard::AssertString_InArray('$optionGroup', $optionGroup, array(
                OptionValueHelper::PHONE_TYPE,
                OptionValueHelper::PREFERRED_COMMUNICATION_METHOD,
                OptionValueHelper::INDIVIDUAL_PREFIX,
                OptionValueHelper::GENDER,
                OptionValueHelper::CASE_STATUS,
                OptionValueHelper::ACTIVITY_TYPE,
                OptionValueHelper::ACTIVITY_STATUS,
              ));

            $this->optionGroup = CiviCrmApiHelper::getObject('OptionGroup', array('name' => $optionGroup));
        }
    }

    /**
     * Returns all the location types
     *
     * @return an array of all the location types
     */
    public function getAll() {
        if (empty($this->cached)) {
            $this->cached = CiviCrmApiHelper::getObjectsAll("OptionValue", array ('option_group_id' => $this->optionGroup['id']));
        }

        return $this->cached;
    }

    /**
     * Get the value of option from its label.
     *
     * @param string $label the label
     * @return the value
     * @throws Exception if $label is empty.
     * @throws Exception if $label not found.
     */
    public function getValueFromLabel($label) {
        Guard::AssertString_NotEmpty('$label', $label);

        foreach ($this->getAll() as $i) {
            if (strtolower($i['label']) == strtolower($label)) {
                return $i['value'];
            }
        }
        throw new Exception(__FUNCTION__.' OptionValue not found: Option Group: '.$this->optionGroup['name'].PHP_EOL.'Label: '.$label.PHP_EOL);
    }

    /**
     * Get the value of an option from its ID.
     *
     * @param integer $id
     * @return the option
     * @throws Exception if $id is not a valid number.
     * @throws Exception if $id not found.
     */
    public function getFromId($id) {
        Guard::AssertInteger('$id', $id);

        foreach ($this->getAll() as $i) {
            if ($i['id'] == $id) {
                return $i;
            }
        }
        throw new Exception(__FUNCTION__.' OptionValue not found: Option Group: '.$this->optionGroup['name'].PHP_EOL.'ID: '.$id.PHP_EOL);
    }

    /**
     * Get option from its value.
     *
     * @param integer $value
     * @return the option
     * @throws Exception if $value is not a valid number.
     * @throws Exception if $value not found.
     */
    public function getFromValue($value) {
        Guard::AssertInteger('$value', $value);

        foreach ($this->getAll() as $i) {
            if ($i['value'] == $value) {
                return $i;
            }
        }
        throw new Exception(__FUNCTION__.' OptionValue not found: Option Group: '.$this->optionGroup['name'].PHP_EOL.'Value: '.$value.PHP_EOL);
    }

    /**
     * Get select options for Option Values.
     *
     * @return array of option value labels keyed by value
     */
    public function getSelectOptions() {
        $result = array();

        foreach ($this->getAll() as $i) {
          $result[$i['value']] = $i['label'];
        }
        asort($result);

        return $result;
    }
}
