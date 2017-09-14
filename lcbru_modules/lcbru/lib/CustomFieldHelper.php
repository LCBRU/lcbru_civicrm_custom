<?php

/**
 * This class provides utility functions for working with Custom Fields.
*/

class CustomFieldHelper
{
    private $cache_fields = NULL;

    /**
     * Get a custom field name.
     *
     * @param string $customFieldName the name of the custom field
     *
     * @return string of the custom field ID name
     * @throws Exception if $customFieldName is empty.
     */
    public function getFieldIdName($customFieldName) {
        Guard::AssertString_NotEmpty('$customFieldName', $customFieldName);

        $cf = $this->getFieldbyName($customFieldName);
        return "custom_$cf[id]";
   }

    /**
     * gets the fields and caches them for later
     */
    public function getFields() {

        if(empty($this->cache_fields)) {
            $customGroups = CiviCrmApiHelper::getObjectsAll('CustomGroup', array());

            foreach ($customGroups as $g) {
              if (!empty($g['extends_entity_column_value'])) {
                  $group_extends_entity_column_value = array_shift($g['extends_entity_column_value']);
              } else {
                  $group_extends_entity_column_value = '';
              }

              foreach (CiviCrmApiHelper::getObjectsAll('CustomField', array('custom_group_id' => $g['id'])) as $f) {
                $this->cache_fields[$f['name']] = array(
                    'id' => $f['id'],
                    'name' => $f['name'],
                    'label' => $f['label'],
                    'column_name' => $f['column_name'],
                    'custom_group_id' => $g['id'],
                    'custom_group_name' => $g['name'],
                    'custom_group_table_name' => $g['table_name'],
                    'group_extends' => $g['extends'],
                    'group_extends_entity_column_value' => $group_extends_entity_column_value,
                    'option_group_id' => ArrayHelper::get($f, 'option_group_id'),
                    );
              }
            }
        }

        return $this->cache_fields;
    }

    /**
     * Get the custom fields associated with a case
     *
     * @param string $caseTypeId the case type ID
     *
     * @return a list of custom fields available for the case
     * @throws Exception if $caseTypeId is empty.
     */
    public function getFieldsForCaseType($caseTypeId) {
        Guard::AssertInteger('$caseTypeId', $caseTypeId);

        $result = array();

        foreach ($this->getFields() as $value) {
            if ($value['group_extends'] == 'Case' && $value['group_extends_entity_column_value'] == $caseTypeId) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Creates a case
     *
     * @param string $caseTypeId the case type ID
     *
     * @return a list of custom fields available for the case
     * @throws Exception if $caseTypeId is empty.
     */
    public function isCaseCustomField($caseTypeId, $name) {
        Guard::AssertInteger('$caseTypeId', $caseTypeId);
        Guard::AssertString_NotEmpty('$name', $name);

        $caseCustomFields = $this->getFieldsForCaseType($caseTypeId);
        $names = array_column($caseCustomFields, 'name');

        return in_array($name, $names);
    }

    /**
     * Gets a custom field by its name
     *
     * @param string $fieldName the field name
     *
     * @return an array of the custom field fields
     */
    public function getFieldbyName($fieldName) {
        Guard::AssertString_NotEmpty('$fieldName', $fieldName);

        $fields = $this->getFields();

        return $fields[$fieldName];
    }

    /**
     * Gets a custom field by its id
     *
     * @param integer $id the field id
     *
     * @return an array of the custom field fields
     */
    public function getFieldbyId($id) {
        Guard::AssertInteger('$id', $id);

        foreach ($this->getFields() as $f) {
            if ($f['id'] == $id) {
                return $f;
            }
        }

        return null;
    }

    /**
     * Gets a custom field options name
     *
     * @param string $fieldName the field name
     *
     * @return an array of the custom field fields
     */
    public function getFieldSelectOptionsbyName($fieldName) {
        Guard::AssertString_NotEmpty('$fieldName', $fieldName);

        $field = $this->getFields()[$fieldName];

        $ovh = new OptionValueHelper($field['option_group_id']);

        return $ovh->getSelectOptions();
    }

    /**
     * Creates the Custom Value using the API.
     *
     * @param string $entityId the id of the entity
     * @param array $name the name of the custom field
     * @param array $value the value of the custom field
     *
     * @return array of created details
     * @throws Exception if $entityId is empty.
     * @throws Exception if $name is empty.
     * @throws Exception if $value is not a string.
     * @throws Exception if the api returns an error.
     */
    public function saveValue($entityId, $name, $value) {
        Guard::AssertInteger('$entityId', $entityId);
        Guard::AssertString_NotEmpty('$name', $name);
        Guard::AssertString('$value', $value);

        $cf = $this->getFieldbyName($name);

        return CiviCrmApiHelper::apiCall('CustomValue', 'create', array(
            'entity_id' => $entityId,
            'custom_' . $cf['custom_group_name'] . ':' . $name => $value
            ));
    }

    /**
     * Sets the field as inactive.
     *
     * @param array $name the name of the custom field
     *
     * @throws Exception if $name is empty.
     */
    public function setFieldInactive($name) {
        Guard::AssertString_NotEmpty('$name', $name);

        $cf = $this->getFieldbyName($name);

        $cfFull = CiviCrmApiHelper::getObject('CustomField', array('id' => $cf['id']));

        $cfFull['is_active'] = 0;

        return CiviCrmApiHelper::updateObject('CustomField', $cfFull);
    }

    /**
     * Get auto generated custom field value
     *
     * @param string $fieldName
     *
     * @return the auto-generated field value
     */
    public function getAutoCustomFieldValue($fieldName) {
        Guard::AssertString_NotEmpty('$fieldName', $fieldName);

        foreach (module_implements('lcbru_getAutoCustomFieldValue') as $module) {
          $function = $module . '_lcbru_getAutoCustomFieldValue';
          $result = $function($fieldName);

          if ($result) {
            return $result;
          }
        }

        return null;
    }

    /**
     * Get studies that support auto-genereated IDs
     *
     * @return array of studies that support auto-generated IDs
     */
    public function getStudiesThatSupportAutoGeneratedIds() {
        $result = array();

        foreach (module_implements('lcbru_getAutoCustomFieldValue') as $module) {
            $result[] = $module;
        }

        return $result;
    }

    public function getEntityCustomData($entity_type, $entity_id) {
        Guard::AssertString_NotEmpty('$entity_type', $entity_type);
        Guard::AssertInteger('$entity_id', $entity_id);

        $custom_values = CiviCrmApiHelper::getObjectsAll("CustomValue", array(
            'entity_id' => $entity_id,
            'entity_table' => $entity_type
        ));

        $result = array();

        foreach ($custom_values as $cv) {
            if (!empty($cv['id'])) {
                $field = $this->getFieldbyId($cv['id']);
                $result[$field['name']] = $cv['latest'];
            }
        }

        return $result;
    }
}
