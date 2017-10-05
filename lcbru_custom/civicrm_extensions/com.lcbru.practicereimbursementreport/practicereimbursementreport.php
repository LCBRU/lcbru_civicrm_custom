<?php

class com_lcbru_practicereimbursementreport extends CRM_Report_Form {


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

    $case_status_group_id = civicrm_api("OptionGroup","get", array ('version' => '3', 'name' => 'case_status'))['id'];
    $excludedCaseStatusId = reset(civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_status_group_id, 'name' => CIVI_CASE_EXCLUDED))['values'])['value'];
    $pendingCaseStatusId = reset(civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_status_group_id, 'name' => CIVI_CASE_PENDING))['values'])['value'];
    $withdrawnCaseStatusId = reset(civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_status_group_id, 'name' => CIVI_CASE_WITHDRAWN))['values'])['value'];

    $this->_columns = array(
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
          'sort_name' => array(
            'title' => ts('Name'),
            'dbAlias' => 'participant.sort_name',
            'required' => true,
            ),
          'Study' => array(
            'title' => ts('Study'),
            'dbAlias' => 'case_type_id',
            'required' => true,
            ),
          'patient_study_id' => array (
              'title' => ts('Participant ID'),
              'dbAlias' => $genvascIdColumnName,
              'required' => true,
            ),
          'practice_name' => array (
              'title' => ts('Practice Name'),
              'required' => true,
              'dbAlias' => 'practice.organization_name',
              'no_display' => true,
            ),
          'is_excluded' => array (
              'title' => ts('Is Excluded'),
              'dbAlias' => "(civicrm_case.status_id = $excludedCaseStatusId)",
              'required' => true,
              'no_display' => true,
            ),
          'is_withdrawn' => array (
              'title' => ts('Is Withdrawn'),
              'dbAlias' => "(civicrm_case.status_id = $withdrawnCaseStatusId)",
              'required' => true,
              'no_display' => true,
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
          'case_is_deleted' => array(
            'title' => ts('Case Deleted'),
            'dbAlias' => 'civicrm_case.is_deleted',
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
            'required' => true,
            ),
          'is_deleted' => array(
            'title' => ts('Contact Deleted'),
            'dbAlias' => 'participant.is_deleted',
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
            ),
          'is_pending' => array(
            'title' => ts('Recruitment Pending'),
            'dbAlias' => "(civicrm_case.status_id = $pendingCaseStatusId)",
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
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
              'dbAlias' => 'ca.case_id',
              'default' => true,
              'default_order' => 'ASC',
            ),
          'practice_name' => array(
              'title' => ts('Practice'),
              'dbAlias' => 'practice.organization_name',
              'default' => true,
              'default_order' => 'ASC',
              'default_is_section' => true,
            ),
          ),
        ),
      );

    $this->_groupFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Practice Reimbursement Report'));
    parent::preProcess();
  }

  function from() {
    $genvascCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_GENVASC_DATA))['table_name'];
    $practiceCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_PRACTICE_DATA))['table_name'];
    $practiceCodeColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_PRACTICE_CODE))["column_name"];
    $ccgCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_CCG_DATA))['table_name'];
    $ccgCodeColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_CCG_CODE))["column_name"];
    $ccgGenvascPiColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_CCG_GENVASC_PI))["column_name"];
    $ccgClrnSiteIdColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_CCG_CLRN_SITE_ID))["column_name"];
    $recruiterRelationshipTypeID = find_civi_relationship_type(CIVI_REL_RECRUITING_SITE);
    $ccgRelationshipTypeID = find_civi_relationship_type(CIVI_REL_CCG);
    $case_status_group_id = civicrm_api("OptionGroup","get",array ('version' => '3', 'name' => 'case_status'))['id'];
    $submittedForReimbursementActivityType = getActivityTypeOptionValueFromTitle(CIVI_ACTIVITY_SUBMITTED_FOR_REIMBURSEMENT);
    $checkConsentActivityType = getActivityTypeOptionValueFromTitle(CIVI_ACTIVITY_CHECK_CONSENT);
    $scheduledActivityStatus = getActivityStatusOptionValueFromTitle('Scheduled');

    $this->_from = "
      FROM civicrm_case
      JOIN civicrm_case_activity ca ON ca.case_id = civicrm_case.id
      JOIN civicrm_activity sfrAct ON sfrAct.id = ca.activity_id
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
      LEFT JOIN civicrm_contact participant ON participant.id = civireport_case_contact.contact_id
      LEFT JOIN civicrm_relationship practiceRel ON practiceRel.case_id = civicrm_case.id AND practiceRel.relationship_type_id = {$recruiterRelationshipTypeID}
      LEFT JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
      LEFT JOIN civicrm_address practiceAddress ON practice.id = practiceAddress.contact_id AND practiceAddress.is_primary = 1
      LEFT JOIN {$practiceCustomDataGroupTableName} gpCustom ON gpCustom.entity_id = practice.id
        ";
  }

  function endPostProcess(&$rows = NULL) {
    $this->assign('caseType', CRM_Utils_Array::value(CRM_Utils_Array::value("case_type_id_value", $this->_params), $this->case_types));
    $this->setPracticeTotals($rows);
    parent::endPostProcess($rows);
  }

  function setPracticeTotals($rows) {
    $totals = array();

    foreach ($rows as $key => $value) {
      $practiceName = $value['recruitment_report_practice_name'];
      if (!array_key_exists($practiceName, $totals)) {
        $totals[$practiceName] = array(
          'count' => 0,
          'excluded' => 0,
          'reimbursed' => 0,
          'reimbursementValue' => 0,
          );
      }

      $totals[$practiceName]['count']++;

      if ($value['recruitment_report_is_excluded']) {
        $totals[$practiceName]['excluded']++;
      } else {
        $totals[$practiceName]['reimbursed']++;
        $totals[$practiceName]['reimbursementValue'] += 16;
      }
    }

    $this->assign_by_ref('practiceTotals', $totals);
  }

  function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['recruitment_report_Study'])) {
        $rows[$rowNum]['recruitment_report_Study'] = $this->case_types[$value];
      }
    }
  }
}
