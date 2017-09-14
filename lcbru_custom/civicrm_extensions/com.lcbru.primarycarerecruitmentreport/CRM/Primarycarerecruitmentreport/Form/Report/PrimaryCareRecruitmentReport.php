<?php

class CRM_Primarycarerecruitmentreport_Form_Report_PrimaryCareRecruitmentReport extends CRM_Report_Form {

  protected $_summary = NULL;

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
    $ccgGenvascPiColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_CCG_GENVASC_PI))["column_name"];
    $ccgClrnSiteIdColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_CCG_CLRN_SITE_ID))["column_name"];
    $practiceCodeColumnName = civicrm_api("CustomField","getsingle", array ('version' => '3', 'label' => CIVI_FIELD_PRACTICE_CODE))["column_name"];

    $this->_columns = array(
      'recruitment_report' => array(
        'fields' => array(
          'genvasc_study_id' => array(
            'title' => ts('StudyID'),
            'dbAlias' => "'12698'",
            'required' => true,
            ),
          'Acronym' => array(
            'title' => 'Acronym',
            'dbAlias' => "'The GENVASC Study:Genetics and the Vascular Health Check Programme'",
            'required' => true,
            ),
          'ccg_genvasc_pi' => array(
            'title' => ts('InvestigatorName'),
            'dbAlias' => $ccgGenvascPiColumnName,
            'required' => true,
            ),
          'ccg_genvasc_pi_id' => array(
            'title' => ts('InvestigatorID'),
            'dbAlias' => "''",
            'required' => true,
            ),
          'ccg_name' => array(
            'title' => ts('SiteName'),
            'dbAlias' => 'ccg.organization_name',
            'required' => true,
            ),
          'ccg_clrn_site_id' => array (
              'title' => ts('SiteID'),
              'dbAlias' => $ccgClrnSiteIdColumnName,
              'required' => true,
            ),
          'practice_code' => array(
            'title' => ts('Practice Code'),
            'dbAlias' => 'gpCustom.'.$practiceCodeColumnName,
            ),
          $genvascIdColumnName => array (
              'title' => ts('StudyPatientID'),
              'dbAlias' => $genvascIdColumnName,
              'required' => true,
            ),
          'start_date' => array(
              'title' => ts('StudyEntryDate'),
              'dbAlias' => 'civicrm_case.start_date',
              'type' => CRM_Utils_Type::T_DATE,
              'required' => true,
            ),
          'entry_event' => array(
            'title' => ts('EntryEvent'),
            'dbAlias' => "'Registration'",
            'required' => true,
            ),
          'entry_event_no' => array(
            'title' => ts('EntryEventNo'),
            'dbAlias' => "'1'",
            'required' => true,
            ),
          'recruit_type' => array(
            'title' => ts('RecruitType'),
            'dbAlias' => "'0'",
            'required' => true,
            ),
          'running_total' => array(
            'title' => ts('RunningTotal'),
            'dbAlias' => "@rownum := @rownum + 1",
            'required' => true,
            ),
          'gender' => array(
            'title' => ts('Gender'),
            'dbAlias' => "''",
            'required' => true,
            ),
          'DOB' => array(
            'title' => ts('DOB'),
            'dbAlias' => "''",
            'required' => true,
            ),
          'ethnicity' => array(
            'title' => ts('Ethnicity'),
            'dbAlias' => "''",
            'required' => true,
            ),
          'post_code' => array(
            'title' => ts('Postcode'),
            'dbAlias' => "''",
            'required' => true,
            ),
          ),
        'filters' => array(
          'status_id' => array(
            'title' => ts('Case Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_statuses,
            'dbAlias' => 'status_id',
            ),
          'case_type_id' => array(
            'title' => ts('Case Type'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $this->case_types,
            'dbAlias' => 'case_type_id',
            'type' => CRM_Utils_Type::T_STRING,
            ),
          'start_date' => array(
              'title' => ts('Study Entry Date'),
              'dbAlias' => 'civicrm_case.start_date',
              'type' => CRM_Utils_Type::T_DATE,
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
          ),
        'order_bys' => array(
          'case_id' => array(
              'title' => ts('Case ID'),
              'dbAlias' => 'civicrm_case.id',
              'default' => true,
              'default_order' => 'ASC',
            ),
          ),
        ),
      );

    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Recruitment Report'));
    parent::preProcess();
  }

  function from() {
    $genvascCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_GENVASC_DATA))['table_name'];
    $practiceCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_PRACTICE_DATA))['table_name'];
    $ccgCustomDataGroupTableName = civicrm_api('CustomGroup','getsingle',array('version' => '3', 'title' => CIVI_FIELD_SET_CCG_DATA))['table_name'];
    $recruiterRelationshipTypeID = find_civi_relationship_type(CIVI_REL_RECRUITING_SITE);
    $ccgRelationshipTypeID = find_civi_relationship_type(CIVI_REL_CCG);

    $this->_from = "
      FROM civicrm_case
      JOIN (SELECT @rownum := 0) r ON civicrm_case.id = civicrm_case.id
      LEFT JOIN {$genvascCustomDataGroupTableName} genvasc_custom_data ON genvasc_custom_data.entity_id = civicrm_case.id
      LEFT JOIN civicrm_case_contact civireport_case_contact on civireport_case_contact.case_id = civicrm_case.id
      LEFT JOIN civicrm_contact participant ON participant.id = civireport_case_contact.contact_id
      LEFT JOIN civicrm_relationship practiceRel ON practiceRel.case_id = civicrm_case.id AND practiceRel.relationship_type_id = {$recruiterRelationshipTypeID}
      LEFT JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
      LEFT JOIN civicrm_relationship ccgRel ON ccgRel.contact_id_a = practice.id AND ccgRel.relationship_type_id = {$ccgRelationshipTypeID}
      LEFT JOIN civicrm_contact ccg ON ccg.id = ccgRel.contact_id_b
      LEFT JOIN civicrm_address practiceAddress ON practice.id = practiceAddress.contact_id AND practiceAddress.is_primary = 1
      LEFT JOIN {$practiceCustomDataGroupTableName} gpCustom ON gpCustom.entity_id = practice.id
      LEFT JOIN {$ccgCustomDataGroupTableName} ccgCustom ON ccgCustom.entity_id = ccg.id
        ";
  }
}