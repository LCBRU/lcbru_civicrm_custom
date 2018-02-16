<?php
// $Id$

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class com_lcbru_recruitmentreport extends CRM_Report_Form {

  protected $_relField = FALSE;

// $_exposeContactID is TRUE by default in Report/Form.php - which forces all forms to include the option for a 'Contact ID' column. This removes it.
  protected $_exposeContactID = FALSE;

  protected $_includeCaseDetailExtra = FALSE;
  
  protected $_customGroupExtends = array('Individual', 'Case', 'Organization');

//  protected $_customGroupGroupBy = TRUE;
 
//  protected $_autoIncludeIndexedFieldsAsOrderBys = TRUE;

  protected $_caseDetailExtra = array(); 
  
  function __construct() {

//    $this->_autoIncludeIndexedFieldsAsOrderBys = TRUE;
    
    $this->case_statuses = CRM_Case_PseudoConstant::caseStatus();
    $this->case_types    = CRM_Case_PseudoConstant::caseType();

    $rels                = CRM_Core_PseudoConstant::relationshipType();
    foreach ($rels as $relid => $v) {
      $this->rel_types[$relid] = $v['label_b_a'];
    }

    $this->_columns = array(
      'civicrm_case' =>
        array(
          'dao' => 'CRM_Case_DAO_Case',
          'fields' =>
            array(
            'id' => 
              array(
                'title' => ts('Case ID'),
              'no_display' => TRUE,
              'required' => TRUE,
            ),
          'start_date' => array('title' => ts('Start Date'),
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array('title' => ts('End Date'),
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'status_id' => array('title' => ts('Case Status')
          ),
          'case_type_id' => array('title' => ts('Case Type'),
            'required' => TRUE,
          ),
          'is_deleted' => array('title' => ts('Deleted'),
          ),
        ),
        'filters' =>
        array(
          'start_date' => array('title' => ts('Start Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'end_date' => array('title' => ts('End Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'status_id' => array('title' => ts('Case Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_statuses,
          ),
          'case_type_id' => array('title' => ts('Case Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->case_types,
          ),
          'is_deleted' => array('title' => ts('Is deleted?'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => array('0' => 'No','1' => 'Yes'),
            'default' => 0,
          ),
        ),
      ),
      'civicrm_contact' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
          array(
            'client_sort_name' => 
              array(
                'name' => 'sort_name',
                'title' => ts('Client Name'),
                'required' => TRUE,
               ),
            'id' => 
              array(
                'no_display' => TRUE,
                'required' => TRUE,
          ),
        ),
        'filters' =>
          array(
            'sort_name' => 
              array(
                'title' => ts('Client Name')
              ),
          'is_deleted' => array('title' => ts('Is deleted?'),
                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                'options' => array('0' => 'No','1' => 'Yes'),
                'default' => 0,
              ),
          ),
      ),
      'civicrm_relation' =>
      array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' =>
          array(
            'relation_sort_name' => 
              array(
                'name' => 'sort_name',
                'title' => ts('Relation'),
               ),
            'id' => 
              array(
                'no_display' => TRUE,
          ),
        ),
      ),

      'civicrm_relationship' =>
      array(
        'dao' => 'CRM_Contact_DAO_Relationship',
        'fields' =>
        array(
          'relationship' => array('name' => 'relationship_type_id',
            'title' => ts('Relationship(s)'),
          ),
        ),
        'filters' =>
        array(
          'relationship' =>
          array(
            'name' => 'relationship_type_id',
            'title' => ts('Relationship(s)'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->rel_types,
          ),
        ),
      ),
    );

    $this->_options = array(
      'my_cases' =>
      array('title' => ts('My Cases'),
        'type' => 'checkbox',
      ),
      'empty_gps' =>
      array('title' => ts('Find missing GP surgery relationships?'),
        'type' => 'checkbox',
      ),
    );
    parent::__construct();
  }

  function preProcess() {
    parent::preProcess();
  }

  function buildQuickForm() {
    parent::buildQuickForm();
    $this->caseDetailSpecialColumnsAdd();
  }

  function caseDetailSpecialColumnsAdd() {
    $elements = array();
    $this->addGroup($elements, 'case_detail_extra');

    $this->assign('caseDetailExtra', $this->_caseDetailExtra);
  }

  function select() {
    $select = array();
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) || CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            if ($tableName == 'civicrm_relationship') {
              $this->_relField = TRUE;
            }
            if ($fieldName == 'sort_name') {
              $select[] = "GROUP_CONCAT({$field['dbAlias']}  ORDER BY {$field['dbAlias']} ) 
                                         as {$tableName}_{$fieldName}";
            }

            if ($fieldName == 'relationship') {
              $select[] = "GROUP_CONCAT(DISTINCT({$field['dbAlias']}) ORDER BY {$field['dbAlias']}) as {$tableName}_{$fieldName}";
            }
            else {
              $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            }

            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
          }
        }
      }
    }

    $this->_select = 'SELECT ' . implode(', ', $select) . ' ';
  }

  function from() {

    $case = $this->_aliases['civicrm_case'];
    $contact = $this->_aliases['civicrm_contact'];
 
    $this->_from = "
             FROM civicrm_case $case
 LEFT JOIN civicrm_case_contact civireport_case_contact on civireport_case_contact.case_id = {$case}.id
 LEFT JOIN civicrm_contact $contact ON {$contact}.id = civireport_case_contact.contact_id
 ";


// nrh11 - nested the left join for looking up the relationships into an if clause so that if the option for 'missing GPs' is ticked that takes precedence.

    if (isset($this->_params['options']['empty_gps'])) {
      $this->_from .= "
             LEFT JOIN  civicrm_relationship {$this->_aliases['civicrm_relationship']} ON {$this->_aliases['civicrm_relationship']}.contact_id_a = {$contact}.id AND {$this->_aliases['civicrm_relationship']}.relationship_type_id = 33
      ";

    } else {

    if ($this->_relField) {
      $this->_from .= "
             LEFT JOIN  civicrm_relationship {$this->_aliases['civicrm_relationship']} ON {$this->_aliases['civicrm_relationship']}.contact_id_a = {$contact}.id AND {$this->_aliases['civicrm_relationship']}.is_active = 1 AND {$this->_aliases['civicrm_relationship']}.case_id = civireport_case_contact.case_id
             LEFT JOIN  civicrm_contact {$this->_aliases['civicrm_relation']} ON {$this->_aliases['civicrm_relation']}.id = {$this->_aliases['civicrm_relationship']}.contact_id_b
";

      }

    
    }

  }

  function where() {
    $clauses = array();
    $this->_having = '';
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;

          if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['dbAlias'], $relative, $from, $to, $field['type']);
          }
          else {

            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            
            if ($fieldName == 'case_type_id' && CRM_Utils_Array::value('case_type_id_value', $this->_params)) {
              $value_parameter = array();
              foreach ($this->_params['case_type_id_value'] as $key => $value) {
                  $value_parameter[$key] = "'" . $value . "'";
              }
            } else {
                $value_parameter = CRM_Utils_Array::value("{$fieldName}_value", $this->_params);
            }
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

    if (isset($this->_params['options']['my_cases'])) {
      $session   = CRM_Core_Session::singleton();
      $userID    = $session->get('userID');
      $clauses[] = "{$this->_aliases['civicrm_contact']}.id = {$userID}";
    }

    if (isset($this->_params['options']['empty_gps'])) {
      $clauses[] = "{$this->_aliases['civicrm_relationship']}.contact_id_a is NULL";
    }

    if (empty($clauses)) {
      $this->_where = 'WHERE ( 1 ) ';
    }
    else {
      $this->_where = 'WHERE ' . implode(' AND ', $clauses);
    }
  }

// Don't want to over-rule the default one
//  function groupBy() {
//    $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_case']}.id";
//  }

  function statistics(&$rows) {
    $statistics = parent::statistics($rows);

    //CaseType statistics
    if (array_key_exists('filters', $statistics)) {
      foreach ($statistics['filters'] as $id => $value) {
        if ($value['title'] == 'Case Type') {
          $statistics['filters'][$id]['value'] = 'Is ' . $this->case_types[substr($statistics['filters'][$id]
            ['value'], -3, -2
          )];
        }
      }
    }
    $statistics['counts']['case'] = array(
      'title' => ts('Total Number of Cases '),
      'value' => isset($statistics['counts']['rowsFound']) ? $statistics['counts']['rowsFound']['value'] : count($rows),
    );
//    $statistics['counts']['country'] = array(
//      'title' => ts('Total Number of Countries '),
//      'value' => $countryCount,
//    );

    return $statistics;
  }

//  Don't want to over-rule the default one
//  function orderBy() {
//    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_case']}.start_date DESC ";
//  }

  function caseDetailSpecialColumnProcess() {
    if (!$this->_includeCaseDetailExtra) {
      return;
    }

    $from = $select = array();
    $case = $this->_aliases['civicrm_case'];

    $this->_select .= ', ' . implode(', ', $select) . ' ';
    $this->_from .= ' ' . implode(' ', $from) . ' ';
  }

  function postProcess() {

    $this->beginPostProcess();

    $this->checkEnabledFields();

    $gpTableName = lcbru_get_custom_group_table_name(CIVI_TABLE_GP_DATA_NAME);

 // Over-ride the custom data 'extends' relationship so that the GP surgery data is referenced to the 'relation' not the 'contact'
    if ($this->_columns[$gpTableName]['extends'] == "Organization") {
      $this->_columns[$gpTableName]['extends'] = "Relation";
      CRM_Core_BAO_CustomQuery::$extendsMap['Relation'] = 'civicrm_relation';
    }

    $this->buildQuery(TRUE);

    $this->caseDetailSpecialColumnProcess();

    $sql = "{$this->_select} {$this->_from} {$this->_where} {$this->_groupBy} {$this->_having} {$this->_orderBy} {$this->_limit}";

    watchdog(__METHOD__, $sql);

    $rows = $graphRows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);

    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function checkEnabledFields() {

    if (isset($this->_params['relationship_value']) && !empty($this->_params['relationship_value'])
    ) {
      $this->_relField = TRUE;
    }

    foreach (array_keys($this->_caseDetailExtra) as $field) {
      if (CRM_Utils_Array::value($field, $this->_params['case_detail_extra'])) {
        $this->_includeCaseDetailExtra = TRUE;
        break;
      }
    }
  }

  function alterDisplay(&$rows) {
    $entryFound = FALSE;

    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_case_status_id', $row)) {
        if ($value = $row['civicrm_case_status_id']) {
          $rows[$rowNum]['civicrm_case_status_id'] = $this->case_statuses[$value];

          $entryFound = TRUE;
        }
      }
      if (array_key_exists('civicrm_case_case_type_id', $row)) {
        if ($value = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, '', $row['civicrm_case_case_type_id'])) {
          $rows[$rowNum]['civicrm_case_case_type_id'] = $this->case_types[$value];

          $entryFound = TRUE;
        }
      }
      if (array_key_exists('civicrm_case_subject', $row)) {
        if ($value = $row['civicrm_case_subject']) {
          $caseId = $row['civicrm_case_id'];
          $contactId = $row['civicrm_contact_id'];
          $rows[$rowNum]['civicrm_case_subject'] = "<a href= 'javascript:viewCase( $caseId,$contactId );'>$value</a>";
          $rows[$rowNum]['civicrm_case_subject_hover'] = ts('View Details of Case.');

          $entryFound = TRUE;
        }
      }
      if (array_key_exists('civicrm_relationship_relationship', $row)) {
        if ($value = $row['civicrm_relationship_relationship']) {
          $caseRoles = explode(',', $value);
          foreach ($caseRoles as $num => $caseRole) {
            $caseRoles[$num] = $this->rel_types[$caseRole];
          }
          $rows[$rowNum]['civicrm_relationship_relationship'] = implode('; ', $caseRoles);
        }
        $entryFound = TRUE;
      }

// Added by nrh11
      if (array_key_exists('civicrm_case_is_deleted', $row)) {
        if ($value = $row['civicrm_case_is_deleted']) {
          $rows[$rowNum]['civicrm_case_is_deleted'] = 'Yes';
        } else {
          $rows[$rowNum]['civicrm_case_is_deleted'] = 'No';
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_client_sort_name', $row) && array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_client_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_client_sort_name_hover'] = ts("View Contact Summary for this Contact");
        $entryFound = TRUE;
      }

      if (!$entryFound) {
        break;
      }
    }
  }
}

