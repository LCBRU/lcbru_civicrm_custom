<?php

class com_lcbru_pcrnreimbursementreport extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array('Membership');
  protected $_customGroupGroupBy = FALSE;

  // $_exposeContactID is TRUE by default in Report/Form.php - which forces all forms to include the option for a 'Contact ID' column. This removes it.
  protected $_exposeContactID = FALSE;

  function __construct() {

    $this->case_statuses = CRM_Case_PseudoConstant::caseStatus();

    // Only the custom fields for Genvasc has been coded so far, so limit
    // the case type to just that.
    //
    // Add additional case types as needed when the custom tables have also been added.
    $this->case_types = array_filter(CRM_Case_PseudoConstant::caseType(), function($v) {
        return $v == CIVI_CASE_TYPE_GENVASC;
      });

    $genvascIdColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_GENVASC_ID))["column_name"];
    $practiceCodeColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_PRACTICE_CODE))["column_name"];

    $this->_columns = array(
      'civicrm_contact' => array(
        ),
      'recruitment_report' => array(
        'fields' => array(
          'start_date' => array(
              'title' => ts('Study Entry Date'),
              'dbAlias' => 'consentAct.activity_date_time',
              'type' => CRM_Utils_Type::T_DATE,
              'required' => true,
            ),
          'case_status' => array(
            'title' => ts('Status'),
            'dbAlias' => 'case_status.label',
            'required' => true,
            ),
          'Study' => array(
            'title' => ts('Study'),
            'dbAlias' => 'case_type_id',
            'required' => true,
            ),
          'patient_study_id' => array (
              'title' => ts('Patient ID'),
              'dbAlias' => $genvascIdColumnName,
              'required' => true,
            ),
          'practice_code' => array(
            'title' => ts('Practice Code'),
            'dbAlias' => 'gpCustom.'.$practiceCodeColumnName,
            'required' => true,
            ),
          'practice_name' => array(
            'title' => ts('Practice Name'),
            'dbAlias' => 'practice.organization_name',
            'required' => true,
            ),
          'practice_address' => array(
            'title' => ts('Practice Address'),
            'dbAlias' => "CONCAT_WS(', ',  NULLIF(TRIM(practiceAddress.supplemental_address_1), ''),  NULLIF(TRIM(practiceAddress.street_address), ''),  NULLIF(TRIM(practiceAddress.city), ''),  NULLIF(TRIM(practiceAddress.postal_code), ''))",
            'required' => true,
            ),
          'ccg_name' => array(
            'title' => ts('CCG'),
            'dbAlias' => 'ccg.organization_name',
            'required' => true,
            ),
          ),
        'filters' => array(
          'case_type_id' => array(
            'title' => ts('Case Type'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $this->case_types,
            'dbAlias' => 'case_type_id',
            'type' => CRM_Utils_Type::T_STRING,
            ),
          'start_date' => array(
            'title' => ts('Study Entry Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'dbAlias' => 'consentAct.activity_date_time',
            'type' => CRM_Utils_Type::T_DATE,
            ),
          ),
        'order_bys' => array(
          'start_date' => array(
            'title' => ts('Study Entry Date'),
            'dbAlias' => 'consentAct.activity_date_time',
            'default' => true,
            'default_order' => 'ASC',
            ),
          'practice_code' => array(
              'title' => ts('Practice Code'),
              'dbAlias' => 'gpCustom.'.$practiceCodeColumnName,
              'default' => true,
              'default_order' => 'ASC',
            ),
          ),
        ),
      );

    $this->_groupFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('PCRN Reimbursement Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) || CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] =CRM_Utils_Array::value('column_header', $field, $field['title']);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  function from() {
    $genvascCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_GENVASC_DATA))['table_name'];
    $practiceCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_PRACTICE_DATA))['table_name'];
    $recruiterRelationshipTypeID = find_civi_relationship_type(CIVI_REL_RECRUITING_SITE);
    $ccgRelationshipTypeID = find_civi_relationship_type(CIVI_REL_CCG);
    $case_status_group_id = civicrm_api("OptionGroup","get",array ('version' => '3', 'name' => 'case_status'))['id'];
    $submittedForReimbursementActivityType = getActivityTypeOptionValueFromTitle(CIVI_ACTIVITY_SUBMITTED_FOR_REIMBURSEMENT);
    $checkConsentActivityType = getActivityTypeOptionValueFromTitle(CIVI_ACTIVITY_CHECK_CONSENT);
    $scheduledActivityStatus = getActivityStatusOptionValueFromTitle('Scheduled');

    $this->_from = "
      FROM civicrm_case
      JOIN civicrm_case_activity srfCa ON srfCa.case_id = civicrm_case.id
      JOIN civicrm_activity sfrAct ON sfrAct.id = srfCa.activity_id
        AND sfrAct.activity_type_id = {$submittedForReimbursementActivityType}
        AND sfrAct.status_id = {$scheduledActivityStatus}
        AND sfrAct.is_current_revision = 1
        AND sfrAct.is_deleted = 0
      JOIN civicrm_case_activity consentCa ON consentCa.case_id = civicrm_case.id
      JOIN civicrm_activity consentAct ON consentAct.id = consentCa.activity_id
        AND consentAct.activity_type_id = {$checkConsentActivityType}
        AND consentAct.is_current_revision = 1
        AND consentAct.is_deleted = 0
      LEFT JOIN civicrm_option_value case_status ON case_status.option_group_id = {$case_status_group_id} AND case_status.value = civicrm_case.status_id
      LEFT JOIN {$genvascCustomDataGroupTableName} genvasc_custom_data ON genvasc_custom_data.entity_id = civicrm_case.id
      LEFT JOIN civicrm_case_contact civireport_case_contact on civireport_case_contact.case_id = civicrm_case.id
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} ON {$this->_aliases['civicrm_contact']}.id = civireport_case_contact.contact_id
      LEFT JOIN civicrm_relationship practiceRel ON practiceRel.case_id = civicrm_case.id AND practiceRel.relationship_type_id = {$recruiterRelationshipTypeID}
      LEFT JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
      LEFT JOIN civicrm_relationship ccgRel ON ccgRel.contact_id_a = practice.id AND ccgRel.relationship_type_id = {$ccgRelationshipTypeID}
      LEFT JOIN civicrm_contact ccg ON ccg.id = ccgRel.contact_id_b
      LEFT JOIN civicrm_address practiceAddress ON practice.id = practiceAddress.contact_id AND practiceAddress.is_primary = 1
      LEFT JOIN {$practiceCustomDataGroupTableName} gpCustom ON gpCustom.entity_id = practice.id
        ";
  }

  // I think this should be removed and we should use the standard one
  // In order to do that we'd need to move a couple of the hard coded
  // criteria into the filters.
  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['dbAlias'], $relative, $from, $to, $field['type']);
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            $value_parameter = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                $value_parameter,
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    if ($this->_aclWhere) {
      $clauses[] = $this->_aclWhere;
    }

    // These are the hard coded criteria that need moving to the filters
    $case_status_group_id = civicrm_api("OptionGroup","get", array ('version' => '3', 'name' => 'case_status'))['id'];
    $excludedCaseStatusId = reset(civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_status_group_id, 'name' => CIVI_CASE_EXCLUDED))['values'])['value'];
    $pendingCaseStatusId = reset(civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_status_group_id, 'name' => CIVI_CASE_PENDING))['values'])['value'];

    $clauses[] = "civicrm_case.is_deleted = 0";
    $clauses[] = "civicrm_case.status_id NOT IN ($excludedCaseStatusId, $pendingCaseStatusId)";

    if (!empty($clauses)) {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);

    $sql = $this->buildQuery(TRUE);
    $rows = array();
    $this->buildRows($sql, $rows);

    $subTotalledRows = $this->addPracticeSubTotals($rows);

    $this->formatDisplay($subTotalledRows);
    $this->doTemplateAssignment($subTotalledRows);
    $this->endPostProcess($subTotalledRows);
  }

  function addPracticeSubTotals($rows) {
    $arrayIndex = 0;
    $lastPracticeCode = '';
    $currentPracticeCount = 0;

    foreach ($rows as $key => $value) {
      if ($value['recruitment_report_practice_code'] != $lastPracticeCode) {

        if ($currentPracticeCount > 0) {
          $totals = array_fill_keys(array_keys($value), "");
          $totals["recruitment_report_patient_study_id"] = "$lastPracticeCode count";
          $totals['recruitment_report_practice_code'] = $currentPracticeCount;
          array_splice($rows, $arrayIndex, 0, array($totals));
          $arrayIndex++;
        }
        $lastPracticeCode = $value['recruitment_report_practice_code'];
        $currentPracticeCount = 0;
      }
      $currentPracticeCount++;
      $arrayIndex++;
    }
    if ($currentPracticeCount > 0) {
      $totals = array_fill_keys(array_keys($value), "");
      $totals["recruitment_report_patient_study_id"] = "$lastPracticeCode count";
      $totals['recruitment_report_practice_code'] = $currentPracticeCount;
      array_splice($rows, $arrayIndex, 0, array($totals));
      $arrayIndex++;
    }
    return $rows;
  }

  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['recruitment_report_Study'])) {
        $rows[$rowNum]['recruitment_report_Study'] = $this->case_types[$value];
      }
    }
  }
}
