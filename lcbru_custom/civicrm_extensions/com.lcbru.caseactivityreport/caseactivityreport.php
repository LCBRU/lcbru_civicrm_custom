<?php

class com_lcbru_caseactivityreport extends CRM_Report_Form {

  function __construct() {
    $this->case_statuses = CRM_Case_PseudoConstant::caseStatus();
    $this->case_types = CRM_Case_PseudoConstant::caseType();
    $this->activity_statuses = CRM_Core_PseudoConstant::activityStatus();
    $this->activity_types = CRM_Core_PseudoConstant::activityType(TRUE, TRUE, FALSE, 'label', TRUE);

    asort($this->case_statuses);
    asort($this->case_types);
    asort($this->activity_statuses);
    asort($this->activity_types);

    $nhsNumberColumnName = lcbru_get_custom_field_column_name(str_replace(" ","_",CIVI_FIELD_NHS_NUMBER));
    $uhlSNumberColumnName = lcbru_get_custom_field_column_name(str_replace(" ","_",CIVI_FIELD_S_NUMBER));

    $this->_columns = array(
      'case_activity_report' => array(
        'fields' => array(
          'contact_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
            'dbAlias' => 'con.id',
          ),
          'Contact' => array(
            'title' => ts('Contact'),
            'dbAlias' => 'con.display_name',
            'default' => true,
          ),
          'NHS_Number' => array(
            'title' => ts('NHS Number'),
            'dbAlias' => $nhsNumberColumnName,
            'default' => true,
          ),
          'UHL_S_Number' => array(
            'title' => ts('UHL S Number'),
            'dbAlias' => $uhlSNumberColumnName,
            'default' => true,
          ),
          'Phone' => array(
            'title' => ts('Phone'),
            'dbAlias' => 'p.Phone',
            'default' => true,
          ),
          'Do_Not_Email' => array(
            'title' => ts('Do Not Email'),
            'dbAlias' => "CASE WHEN con.do_not_email = 0 THEN 'No' ELSE 'Yes' END",
          ),
          'Do_Not_Phone' => array(
            'title' => ts('Do Not Phone'),
            'dbAlias' => "CASE WHEN con.do_not_phone = 0 THEN 'No' ELSE 'Yes' END",
          ),
          'Do_Not_Mail' => array(
            'title' => ts('Do Not Mail'),
            'dbAlias' => "CASE WHEN con.do_not_mail = 0 THEN 'No' ELSE 'Yes' END",
          ),
          'Do_Not_SMS' => array(
            'title' => ts('Do Not SMS'),
            'dbAlias' => "CASE WHEN con.do_not_sms = 0 THEN 'No' ELSE 'Yes' END",
          ),
          'Is_Deceased' => array(
            'title' => ts('Is Deceased'),
            'dbAlias' => "CASE WHEN con.is_deceased = 0 THEN 'No' ELSE 'Yes' END",
          ),
          'case_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
            'dbAlias' => 'c.id',
          ),
          'Case' => array (
            'title' => ts('Case'),
            'dbAlias' => 'c.case_type_id',
            'default' => true,
          ),
          'Case_Status' => array (
            'title' => ts('Case Status'),
            'dbAlias' => 'c.status_id',
            'default' => true,
          ),
          'Activity' => array (
            'title' => ts('Activity'),
            'dbAlias' => 'a.activity_type_id',
            'default' => true,
          ),
          'Subject' => array (
            'title' => ts('Subject'),
            'dbAlias' => 'a.subject',
            'default' => true,
          ),
          'Activity_Status' => array (
            'title' => ts('Activity Status'),
            'dbAlias' => 'a.status_id',
            'default' => true,
          ),
          'activity_date_time' => array (
            'title' => ts('Activity Date'),
            'dbAlias' => 'a.activity_date_time',
            'default' => true,
            'type' => CRM_Utils_Type::T_DATE,
          ),
        ),
        'filters' => array(
          'case_is_deleted' => array(
            'title' => ts('Case Deleted'),
            'dbAlias' => 'c.is_deleted',
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
            'required' => true,
          ),
          'contact_is_deleted' => array(
            'title' => ts('Contact Deleted'),
            'dbAlias' => 'con.is_deleted',
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'contact_do_not_email' => array(
            'title' => ts('Do Not Email'),
            'dbAlias' => 'con.do_not_email',
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'contact_do_not_phone' => array(
            'title' => ts('Do Not Phone'),
            'dbAlias' => 'con.do_not_phone',
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'contact_do_not_mail' => array(
            'title' => ts('Do Not Mail'),
            'dbAlias' => 'con.do_not_mail',
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'contact_do_not_sms' => array(
            'title' => ts('Do Not SMS'),
            'dbAlias' => 'con.do_not_sms',
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'contact_is_Deceased' => array(
            'title' => ts('Is Deceased'),
            'dbAlias' => 'con.is_deceased',
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'activity_is_deleted' => array(
            'title' => ts('Activity Deleted'),
            'dbAlias' => 'a.is_deleted',
            'default' => 0,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'activity_is_current_revision' => array(
            'title' => ts('Activity Current Revision'),
            'dbAlias' => 'a.is_current_revision',
            'default' => 1,
            'no_display' => true,
            'type' => CRM_Utils_Type::T_BOOLEAN,
          ),
          'case_type_id' => array(
            'title' => ts('Case Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_types,
            'dbAlias' => 'c.case_type_id',
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'case_status_id' => array(
            'title' => ts('Case Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_statuses,
            'dbAlias' => 'c.status_id',
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'activity_type_id' => array(
            'title' => ts('Activity Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->activity_types,
            'dbAlias' => 'a.activity_type_id',
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'activity_status_id' => array(
            'title' => ts('Activity Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->activity_statuses,
            'dbAlias' => 'a.status_id',
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'activity_subject' => array(
            'title' => ts('Subject'),
            'operatorType' => CRM_Report_Form::OP_STRING,
            'dbAlias' => 'a.subject',
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'activity_date_time' => array(
              'title' => ts('Activity Date'),
              'dbAlias' => 'a.activity_date_time',
              'type' => CRM_Utils_Type::T_DATE,
            ),
        ),
        'order_bys' => array(
          'activity_date_time' => array(
              'title' => ts('Activity Date'),
              'dbAlias' => 'a.activity_date_time',
              'default' => true,
              'default_order' => 'ASC',
          ),
        ),
      ),
    );
    parent::__construct();
  }

  function from() {
    $contactIdsCustomDataGroupTableName = lcbru_get_custom_group_table_name(str_replace(" ","_",CIVI_FIELD_SET_IDENTIFIERS));

    $this->_from = "
      FROM  civicrm_case c
      JOIN  civicrm_case_activity ca ON ca.case_id = c.id
      JOIN  civicrm_activity a ON a.id   = ca.activity_id
      JOIN  civicrm_case_contact ccon ON ccon.case_id = c.id
      JOIN  civicrm_contact con ON con.id = ccon.contact_id
      LEFT JOIN  {$contactIdsCustomDataGroupTableName} cids ON cids.entity_id = con.id
      LEFT JOIN (
        SELECT  
              p.contact_id
                  , GROUP_CONCAT(CONCAT(l.name, ': ', p.phone) SEPARATOR '; ') AS phone
        FROM  civicrm_phone p
        JOIN  civicrm_location_type l ON l.id = p.location_type_id
          GROUP BY p.contact_id
        ) p ON p.contact_id = con.id
    ";
  }

    function alterDisplay(&$rows) {
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('case_activity_report_Case_Status', $row)) {
        if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['case_activity_report_Case_Status'])) {
          $rows[$rowNum]['case_activity_report_Case_Status'] = $this->case_statuses[$value];
        }
      }
      
      if (array_key_exists('case_activity_report_Activity_Status', $row)) {
        if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['case_activity_report_Activity_Status'])) {
          $rows[$rowNum]['case_activity_report_Activity_Status'] = $this->activity_statuses[$value];
        }
      }

      if (array_key_exists('case_activity_report_Contact', $row)) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['case_activity_report_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['case_activity_report_Contact_link'] = $url;
        $rows[$rowNum]['case_activity_report_Contact_hover'] = ts("View Contact.");
      }

      if (array_key_exists('case_activity_report_Activity', $row)) {
        if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['case_activity_report_Activity'])) {
          $rows[$rowNum]['case_activity_report_Activity'] = $this->activity_types[$value];
        }
      }

      if (array_key_exists('case_activity_report_Case', $row)) {
        if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['case_activity_report_Case'])) {
          $rows[$rowNum]['case_activity_report_Case'] = $this->case_types[$value];
        }

        $url = CRM_Utils_System::url("civicrm/contact/view/case",
          'reset=1&action=view&cid=' . $row['case_activity_report_contact_id'] . '&id=' . $row['case_activity_report_case_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['case_activity_report_Case_link'] = $url;
        $rows[$rowNum]['case_activity_report_Case_hover'] = ts("Manage Case");
      }
    }
  }

}
