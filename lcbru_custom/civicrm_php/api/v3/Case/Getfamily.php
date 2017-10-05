<?php

/**
 * Retrieve one or more phones
 *
 * @param  mixed[]  (reference ) input parameters
 *
 * {@schema Core/Phone.xml}
 * {@example PhoneDelete.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found phones else error
 * @access public
 * This function has been declared there instead than in api/v3/Phone.php for no specific reasons, beside to demonstate this feature (that might be useful in your module, eg if you want to implement a civicrm_api ('Phone','Dial') that you would then simply put in your module under api/v3/Phone/Dial.php .
 */
function civicrm_api3_case_getfamily($params) {

    civicrm_api3_verify_mandatory($params, NULL, array('graphic_family_id'));

    $options = _civicrm_api3_get_options_from_params($params);

    $customGroup = civicrm_api('CustomGroup', 'getSingle', array(
      'version' => 3,
      'name' => CIVI_CUSTOMGROUP_GRAPHIC2
    ));
    $customGroupTableName = $customGroup["table_name"];
    $familyIdCustomField = civicrm_api('CustomField', 'getSingle', array(
      'version' => 3,
      'label' => CIVI_FIELD_G2_FAM_ID
    ));
    $familyIdColumnName = $familyIdCustomField["column_name"];
    $labIdCustomField = civicrm_api('CustomField', 'getSingle', array(
      'version' => 3,
      'label' => CIVI_FIELD_G2_LAB_ID
    ));
    $labIdColumnName = $labIdCustomField["column_name"];
    $participantIdCustomField = civicrm_api('CustomField', 'getSingle', array(
      'version' => 3,
      'label' => CIVI_FIELD_G2_PAT_ID
    ));
    $participantIdColumnName = $participantIdCustomField["column_name"];

    $graphicFamilyId = CRM_Core_DAO::escapeString(CRM_Utils_Array::value('graphic_family_id', $params));

    $sql = "
        SELECT
             c.id case_id
            ,g2.$familyIdColumnName
            ,g2.$labIdColumnName
            ,g2.$participantIdColumnName
            ,client_contact.last_name
            ,client_contact.first_name
            ,client_contact.id client_id
        FROM civicrm_case c
        JOIN $customGroupTableName g2 ON g2.entity_id = c.id
        JOIN civicrm_case_contact client ON client.case_id = c.id
        JOIN civicrm_contact client_contact ON client_contact.id = client.contact_id
        WHERE g2.$familyIdColumnName = '$graphicFamilyId';
        ";

    $dao = &CRM_Core_DAO::executeQuery($sql);

    $cases = array();

    while ($dao->fetch()) {
        $cases[$dao->case_id]["case_id"] = $dao->case_id;
        $cases[$dao->case_id]['family_id'] = $dao->$familyIdColumnName;
        $cases[$dao->case_id]['lab_id'] = $dao->$labIdColumnName;
        $cases[$dao->case_id]['participant_id'] = $dao->$participantIdColumnName;
        $cases[$dao->case_id]['last_name'] = $dao->last_name;
        $cases[$dao->case_id]['first_name'] = $dao->first_name;
        $cases[$dao->case_id]['client_id'] = $dao->client_id;
    }
    
    return civicrm_api3_create_success($cases, $params, 'case', 'get');
}

