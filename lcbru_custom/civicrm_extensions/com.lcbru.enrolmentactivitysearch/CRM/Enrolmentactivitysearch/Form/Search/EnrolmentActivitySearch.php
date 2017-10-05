<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Enrolmentactivitysearch_Form_Search_EnrolmentActivitySearch implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;
  protected $_caseTypes;

  function __construct(&$formValues) {
    $this->_formValues = $formValues;

    $this->_caseTypes = CRM_Case_BAO_Case::buildOptions('case_type_id');

    /**
     * Define the columns for search result rows
     */
    $this->_columns = array(
      ts('Name') => 'sort_name',
      ts('Case Type') => 'case_type',
      ts('Case Status') => 'case_status',
      ts('Activity Type') => 'activity_type',
      ts('Activity Status') => 'activity_status',
      ts('Activity Subject') => 'activity_subject',
      ts('Scheduled Date') => 'activity_date',
      ts(' ') => 'activity_id',
      ts('  ') => 'activity_type_id',
      ts('   ') => 'case_id',
      ts('Location') => 'location',
      ts('Duration') => 'duration',
    );

    $this->_groupId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup',
      'activity_status',
      'id',
      'name'
    );

    //Add custom fields to columns array for inclusion in export
    $groupTree = &CRM_Core_BAO_CustomGroup::getTree('Activity', $form, NULL,
      NULL, '', NULL
    );


    //use simplified formatted groupTree
    $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree($groupTree, 1, $form);

    //cycle through custom fields and assign to _columns array
    foreach ($groupTree as $key) {
      foreach ($key['fields'] as $field) {
        $fieldlabel = $key['title'] . ": " . $field['label'];
        $this->_columns[$fieldlabel] = $field['column_name'];
      }
    }
    //end custom fields
  }

  function buildForm(&$form) {

    /**
     * You can define a custom title for the search form
     */
    $this->setTitle('Find Contacts with activities within a specific study enrolment');

    /**
     * Define the search form fields here
     */
    // Allow user to choose which type of contact to limit search on
    $contactSubType =array('' => 'Find...') +  CRM_Contact_BAO_Contact::buildOptions('contact_sub_type');
    $form->add('select', 'contact_type', ts('Contact Type'),
      $contactSubType,
      FALSE
    );

    // Select box for Case Type
    $caseType = array('' => ' - select study - ') + $this->_caseTypes;
    $form->add('select', 'case_type_id', ts('Case Type'),
      $caseType,
      TRUE
    );

    // Select box for Case Status
    $caseStatus = array('' => ' - select status - ') + CRM_Case_BAO_Case::buildOptions('case_status_id');
    $form->add('select', 'case_status_id', ts('Case Status'),
      $caseStatus,
      FALSE
    );

    // Text box for Case Subject
    $form->add('text',
      'case_subject',
      ts('Case Subject')
    );

    // Radio button to include or exclude the specified activity
    $incExc = array(
      '2' => ts('Show only contacts that HAVE the defined activity in their enrolment record'),
      '1' => ts('Show ALL contacts whether or not they have the defined activity in their enrolment record(NB: Do not select an activity status)'),
      '0' => ts('Show only contacts that DO NOT HAVE the defined activity in their enrolment record (NB: Do not select an activity status)'),
    );
    $form->addRadio('incExc', ts('INCLUDE / EXCLUDE'), $incExc, NULL, '<br />', TRUE);

    // Select box for Activity Type ID
    $activityType = array('' => ' - select activity - ') + CRM_Activity_BAO_Activity::buildOptions('activity_type_id');

    $form->add('select', 'activity_type_id', ts('Activity Type'),
      $activityType,
      TRUE
    );

    // Text box for Activity Subject
    $form->add('text',
      'activity_subject',
      ts('Activity Subject')
    );

    // Select box for Activity Status
    $activityStatus = array('' => ' - select status - ') + CRM_Activity_BAO_Activity::buildOptions('activity_status_id');
    $form->add('select', 'activity_status_id', ts('Activity Status'),
      $activityStatus,
      FALSE
    );

    // Activity Date range
    $form->addDate('start_date', ts('Activity Date From'), FALSE, array('formatType' => 'custom'));
    $form->addDate('end_date', ts('...through'), FALSE, array('formatType' => 'custom'));


    // Contact Name field
    $form->add('text', 'sort_name', ts('Contact Name'));

    /**
     * If you are using the sample template, this array tells the template fields to render
     * for the search form.
     */
    $form->assign('elements', array(
      'contact_type', 'case_type_id', 'case_status_id','case_subject', 'activity_type_id',
        'activity_status_id', 'activity_subject', 'incExc', 'start_date', 'end_date', 'sort_name',
      ));
  }

  /**
   * Define the smarty template used to layout the search form and results listings.
   */
  function templateFile() {
    return 'CRM/Enrolmentactivitysearch/Form/Search/EnrolmentActivitySearch.tpl'; // Sets the specific template to use
  }

  /**
   * Construct the search query
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {

    // SELECT clause must include contact_id as an alias for civicrm_contact.id
    if ($justIDs) {
      $select = 'target.id as contact_id';
    }
    else {
      $select = '
                target.id                   as contact_id,
                target.sort_name            as sort_name,
                target.contact_type         as contact_type,
                case.id                     as case_id,
                case.case_type_id           as case_type_id,
                case.case_type_id           as case_type,
                ov4.label                   as case_status,
                activity.id                 as activity_id,
                activity.activity_type_id   as activity_type_id,
                ov1.label                   as activity_type,
                activity.subject            as activity_subject,
                activity.activity_date_time as activity_date,
                ov2.label                   as activity_status,
                cca.case_id                 as case_id,
                activity.location           as location,
                activity.duration           as duration
                '; // Probably a lot still in here we do not need - remove later
    }

    $from = $this->from();

    $where = $this->where($includeContactIDs);

    if (!empty($where)) {
      $where = "WHERE $where";
    }

    // add custom group fields to SELECT and FROM clause
    $groupTree = CRM_Core_BAO_CustomGroup::getTree('Activity', $form, NULL, NULL, '', NULL);

    foreach ($groupTree as $key) {
      if ($key['extends'] == 'Activity') {
        $select .= ", " . $key['table_name'] . ".*";
        $from .= " LEFT JOIN " . $key['table_name'] . " ON " . $key['table_name'] . ".entity_id = activity.id";
      }
    }
    // end custom groups add

    $sql = " SELECT $select FROM   $from $where ";

    // ELIMINATE DUPLICATE CASES - participants with multiple enrolments appear multiple times, but those with multiple activities do not.
    $sql .= ' GROUP BY case.id ';

    //no need to add order when only contact Ids.
    if (!$justIDs) {
      // Define ORDER BY for query in $sort, with default value
      if (!empty($sort)) {
        if (is_string($sort)) {
          $sort = CRM_Utils_Type::escape($sort, 'String');
          $sql .= " ORDER BY $sort ";
        }
        else {
          $sql .= ' ORDER BY ' . trim($sort->orderBy());
        }
      }
      else {
        $sql .= 'ORDER BY target.sort_name, activity.activity_date_time DESC, activity.activity_type_id, activity.status_id';
      }
    }

    if ($rowcount > 0 && $offset >= 0) {
      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowcount = CRM_Utils_Type::escape($rowcount, 'Int');
      $sql .= " LIMIT $offset, $rowcount ";
    }
    return $sql;
  }

  // Alters the date display in the Activity Date Column. We do this after we already have
  // the result so that sorting on the date column stays pertinent to the numeric date value
  function alterRow(&$row) {
    $row['activity_date'] = CRM_Utils_Date::customFormat($row['activity_date'], '%B %E%f, %Y %l:%M %P');

    if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['case_type'])) {
      $row['case_type'] = $this->_caseTypes[$value];
    }

  }

  // Regular JOIN statements here to limit results to contacts who have cases with / without activities, as required
  function from() {

    $params = array('version' => 3, 'sequential' => 1, 'name' => 'activity_type');
    $ov1 = civicrm_api('OptionGroup','getsingle',$params);
    $params['name'] = 'activity_status';
    $ov2 = civicrm_api('OptionGroup','getsingle',$params);
    $params['name'] = 'case_status';
    $ov4 = civicrm_api('OptionGroup','getsingle',$params);


    if ($this->_formValues['incExc'] == '2')
      {
      return "
        civicrm_contact target
        JOIN civicrm_case_contact ccc
        ON ccc.contact_id = target.id
        JOIN civicrm_case `case`
        ON case.id = ccc.case_id AND case.is_deleted = '0' AND case.case_type_id = '{$this->_formValues['case_type_id']}'
        JOIN civicrm_option_value ov4
        ON case.status_id = ov4.value AND ov4.option_group_id = '{$ov4['id']}'
        JOIN civicrm_case_activity cca 
        ON case.id = cca.case_id
        JOIN civicrm_activity activity
        ON cca.activity_id = activity.id AND activity.is_current_revision = '1' AND activity.is_deleted = '0' AND activity.activity_type_id = '{$this->_formValues['activity_type_id']}'
        JOIN civicrm_option_value ov1
        ON activity.activity_type_id = ov1.value AND ov1.option_group_id = '{$ov1['id']}'
        JOIN civicrm_option_value ov2
        ON activity.status_id = ov2.value AND ov2.option_group_id = '{$ov2['id']}'
      ";
    } else {
      return "
        civicrm_contact target
        JOIN civicrm_case_contact ccc
        ON ccc.contact_id = target.id
        JOIN civicrm_case `case`
        ON case.id = ccc.case_id AND case.is_deleted = '0' AND case.case_type_id = '{$this->_formValues['case_type_id']}'
        JOIN civicrm_option_value ov4
        ON case.status_id = ov4.value AND ov4.option_group_id = '{$ov4['id']}'
        LEFT JOIN ( civicrm_case_activity cca 
            JOIN civicrm_activity activity
            ON cca.activity_id = activity.id AND activity.is_current_revision = '1' AND activity.is_deleted = '0' AND activity.activity_type_id = '{$this->_formValues['activity_type_id']}'
            JOIN civicrm_option_value ov1
            ON activity.activity_type_id = ov1.value AND ov1.option_group_id = '{$ov1['id']}'
            JOIN civicrm_option_value ov2
            ON activity.status_id = ov2.value AND ov2.option_group_id = '{$ov2['id']}'
          )
        ON case.id = cca.case_id
      ";
      }
  }

  /*
     * WHERE clause is an array built from any required JOINS plus conditional filters based on search criteria field values
     *
     */
  function where($includeContactIDs = FALSE) {
    $clauses = array();

    // Do not include deleted or deceased participants
    $clauses[] = "target.is_deleted = '0' AND target.is_deceased = '0'";

    // add contact name search;
    $contactname = $this->_formValues['sort_name'];
    if (!empty($contactname)) {
      $dao         = new CRM_Core_DAO();
      $contactname = $dao->escape($contactname);
      $clauses[]   = "(target.sort_name LIKE '%{$contactname}%')";
    }

    if (!empty($this->_formValues['contact_type'])) {
      $clauses[] = "target.contact_type LIKE '%{$this->_formValues['contact_type']}%'";
    }

    $a_subject = $this->_formValues['activity_subject'];
    if (!empty($a_subject)) {
      $dao       = new CRM_Core_DAO();
      $a_subject   = $dao->escape($a_subject);
      $clauses[] = "activity.subject LIKE '%{$a_subject}%'";
    }

    $c_subject = $this->_formValues['case_subject'];
    if (!empty($c_subject)) {
      $dao       = new CRM_Core_DAO();
      $c_subject   = $dao->escape($c_subject);
      $clauses[] = "case.subject LIKE '%{$c_subject}%'";
    }

    if (!empty($this->_formValues['activity_status_id'])) {
      $clauses[] = "activity.status_id = '{$this->_formValues['activity_status_id']}'";
    }

    if (!empty($this->_formValues['case_status_id'])) {
      $clauses[] = "case.status_id = '{$this->_formValues['case_status_id']}'";
    }

    $startDate = $this->_formValues['start_date'];
    if (!empty($startDate)) {
      $startDate .= '00:00:00';
      $startDateFormatted = CRM_Utils_Date::processDate($startDate);
      if ($startDateFormatted) {
        $clauses[] = "activity.activity_date_time >= $startDateFormatted";
      }
    }

    $endDate = $this->_formValues['end_date'];
    if (!empty($endDate)) {
      $endDate .= '23:59:59';
      $endDateFormatted = CRM_Utils_Date::processDate($endDate);
      if ($endDateFormatted) {
        $clauses[] = "activity.activity_date_time <= $endDateFormatted";
      }
    }

    if ($includeContactIDs) {
      $contactIDs = array();
      foreach ($this->_formValues as $id => $value) {
        if ($value &&
          substr($id, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX
        ) {
          $contactIDs[] = substr($id, CRM_Core_Form::CB_PREFIX_LEN);
        }
      }

      if (!empty($contactIDs)) {
        $contactIDs = implode(', ', $contactIDs);
        $clauses[] = "target.id IN ( $contactIDs )";
      }
    }
    
    if ($this->_formValues['incExc'] == '0') {
      $params = array('version' => 3, 'sequential' => 1, 'name' => 'activity_type');
      $ov1 = civicrm_api('OptionGroup','getsingle',$params);
      $params['name'] = 'activity_status';
      $ov2 = civicrm_api('OptionGroup','getsingle',$params);
      $params['name'] = 'case_status';
      $ov4 = civicrm_api('OptionGroup','getsingle',$params);

      $clauses[] = " target.id NOT IN (
		SELECT 
		target.id as contact_id
		FROM
		civicrm_contact target
		JOIN civicrm_case_contact ccc
		ON ccc.contact_id = target.id
		JOIN civicrm_case `case`
		ON case.id = ccc.case_id AND case.is_deleted = '0' AND case.case_type_id = '{$this->_formValues['case_type_id']}'
		JOIN civicrm_option_value ov4
		ON case.status_id = ov4.value AND ov4.option_group_id = '{$ov4['id']}'
		JOIN civicrm_case_activity cca 
		ON case.id = cca.case_id
		JOIN civicrm_activity activity
		ON cca.activity_id = activity.id AND activity.is_current_revision = '1' AND activity.is_deleted = '0' AND activity.activity_type_id = '{$this->_formValues['activity_type_id']}'
		JOIN civicrm_option_value ov1
		ON activity.activity_type_id = ov1.value AND ov1.option_group_id = '{$ov1['id']}'
		JOIN civicrm_option_value ov2
		ON activity.status_id = ov2.value AND ov2.option_group_id = '{$ov2['id']}'
		)";
    }

    return implode(' AND ', $clauses);
  }

  /*
   * Functions below generally don't need to be modified
   */
  function count() {
    $sql = $this->all();

    $dao = CRM_Core_DAO::executeQuery($sql,
      CRM_Core_DAO::$_nullArray
    );
    return $dao->N;
  }

  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  function &columns() {
    return $this->_columns;
  }

  function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }

  function summary() {
    return NULL;
  }
}

