<?php

/**
 * @file
 * Functions for interacting with the civicrm hooks - used by the LCBRU but potentially used by other modules also. 
 *
 */

function lcbru_civicrm_tokens( &$tokens ) {
   $tokens['contact'] = array('contact.state_province_name' => 'State or Province name', 'contact.complete_address' => 'Complete Address Block', 'contact.complete_address_1_line' => 'Complete Address in one line', 'contact.s_number' => 'UHL S number', 'contact.nhs_number' => 'NHS number');
   $tokens['date'] = array('date.date_short' => 'Today\'s date: dd/mm/yy', 'date.date_med' => 'Today\'s date: dd Mon yyyy', 'date.date_long' => 'Today\'s date: dth Month, yyyy' );
   $tokens['gp_details'] = array('gp_details.gp_surname' => 'Not yet available', 'gp_details.surgery_address' => 'Address of the patient\'s GP surgery', 'gp_details.surgery_name' => 'Display name of the patient\'s GP surgery' );
   $tokens['study_subject'] = array('study_subject.all_studies' => 'List of all studies enrolled in', 'study_subject.latest_study' => 'Latest study enrolled in');
}

function lcbru_civicrm_tokenValues( &$values, $cIDs, $job = null, $tokens = array(), $context = null ) {
  require_once 'api/v3/Contact.php';
  require_once 'CRM/Utils/Address.php';

  // Address tokens - The state_province_name token replaces the broken one in core CiviCRM, and thus allows the CRM_Utils_Address::format call to work correctly, provided the label and mailing formats have been set in the CiviCRM admin pages.
  if (!empty($tokens['contact'])) {
    foreach($cIDs as $id){
      $params = array('version' => '3', 'sequential' => '1', 'id' => $id);
      $contact = civicrm_api("Contact","getsingle",$params);
      $values[$id]['contact.complete_address'] = nl2br(CRM_Utils_Address::format($contact));
      $values[$id]['contact.complete_address_1_line'] = rtrim(str_replace(array("\r","\n"), ', ', CRM_Utils_Address::format($contact))," ,");
      $values[$id]['contact.state_province_name'] = $contact["state_province_name"];      
      $params = array('version' => '3', 'sequential' => '1', 'entity_id' => $id, 'return.contact_ids:uhl_s_number' => '1');
      $s_number = civicrm_api("CustomValue","getsingle",$params);
      if (array_key_exists('latest', $s_number)) {
          $values[$id]['contact.s_number'] = $s_number["latest"];
      }
      $params = array('version' => '3', 'sequential' => '1', 'entity_id' => $id, 'return.contact_ids:nhs_number' => '1');
      $nhs_number = civicrm_api("CustomValue","getsingle",$params);
      if (array_key_exists('latest', $nhs_number)) {
          $values[$id]['contact.nhs_number'] = $nhs_number["latest"];
      }
    }
  }

  // Date tokens
  if (!empty($tokens['date'])) {
    $date = array(
      'date.date_short' => date('d/m/y'),
      'date.date_med' => date('j M Y'),
      'date.date_long' => date('jS F, Y'),
    );
    foreach($cIDs as $id){
      $values[$id] = empty($values[$id]) ? $date : $values[$id] + $date;
    }
  }

  // GP tokens
  if (!empty($tokens['gp_details'])) {
    // First lookup the relationship type and put it in $rel_type
    $params = array('version' => '3', 'sequential' => '1', 'name_a_b' => CIVI_REL_SURGERY_PATIENT, );
    $rel_type = civicrm_api("relationshipType","getsingle",$params);
    foreach($cIDs as $id) {
      $params = array('version' => '3', 'sequential' => '1', 'contact_id_a' => $id, 'relationship_type_id' => $rel_type['id'], 'is_active' => '1');
      $relationship = civicrm_api("Relationship","getsingle",$params);
      $surgery_id =  $relationship['contact_id_b']; // result of relationship lookup
      $params = array('version' => '3', 'sequential' => '1', 'id' => $surgery_id);
      $gp_surgery = civicrm_api("Contact","getsingle",$params);
      $values[$id]['gp_details.surgery_address'] = nl2br(CRM_Utils_Address::format($gp_surgery));
      $values[$id]['gp_details.surgery_name'] = $gp_surgery["display_name"];      
    }
  }

  // Study subject tokens
  if (!empty($tokens['study_subject'])) {
    // Build an array of case type labels - better to do it once.
    $case_types = CRM_Case_PseudoConstant::caseType();

    foreach($cIDs as $id) {
      // Look up enrollments for the subject
      $params = array('version' => '3', 'sequential' => '1', 'contact_id' => $id, );
      $enrollments = civicrm_api("Case","get",$params);

      // Create array of just the labels, then implode it - list will be comma separated with no 'and' before the last one, but so be it.
      $enrollment_labels_array = array();
      foreach($enrollments['values'] as $enrollment) {
        $enrollment_labels_array[] = $case_types[$enrollment['case_type_id']];
        }
      $values[$id]['study_subject.all_studies'] = implode(", ",$enrollment_labels_array);
    
      // Use array_reduce to get the element with the most recent date.
      $initial = array_shift($enrollments['values']);
      $latest = array_reduce($enrollments['values'], function($a, $b) { return (strtotime($a['start_date']) > strtotime($b['start_date']) ? $a : $b);},$initial);
      $values[$id]['study_subject.latest_study'] = $case_types[$latest["case_type_id"]];
      }
    }
}

function lcbru_civicrm_searchTasks($objectType, &$tasks ) {
  $taskClasses = array_column($tasks, 'class');

  if ($objectType=='contact')     {
    $tasks[] = array(
      'title'  => ts( 'Create case for each'),
      'class'  => 'CRM_Contact_Form_Task_CreateCaseForEach',
      'result' => false );
    $tasks[] = array(
      'title'  => ts( 'PMI Address Check'),
      'class'  => 'CRM_Contact_Form_Task_PmiAddressCheck',
      'result' => false );
    $tasks[] = array(
      'title'  => ts( 'Submit to DAPS'),
      'class'  => 'CRM_Contact_Form_Task_SubmitToDaps',
      'result' => false );
    $tasks[] = array(
      'title'  => ts('Print Labels for each Subject'),
      'class'  => 'CRM_Contact_Form_Task_PrintLabels',
      'result' => false );
  }
  if ($objectType=='activity') {
    if ((array_search('CRM_Activity_Form_Task_AddToGroup', $taskClasses) === false)) {
      $tasks[] = array(
        'title'  => ts( 'Add Activity Targets to Group'),
        'class'  => 'CRM_Activity_Form_Task_AddToGroup',
        'result' => false );
    }

    if ((array_search('CRM_Activity_Form_Task_UpdateActivityStatus', $taskClasses) === false)) {
      $tasks[] = array(
        'title'  => ts( 'Update Status from Case Status'),
        'class'  => 'CRM_Activity_Form_Task_UpdateActivityStatus',
        'result' => false );
    }
  }
  if ($objectType=='case')     {
    if ((array_search('CRM_Case_Form_Task_CreateActivityForEach', $taskClasses) === false)) {
      $tasks[] = array(
        'title'  => ts('Create activity for each'),
        'class'  => 'CRM_Case_Form_Task_CreateActivityForEach',
        'result' => false );
    }
  }
}


