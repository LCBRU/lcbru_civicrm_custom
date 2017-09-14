<?php

/**
 * @file
 * Genvasc Portal
 */

require_once("GenvascPortal.php");

function _genvasc_labels_new_recruits($action = '',$id = '') {

  switch ($action) {
    case 'process':
      return _genvasc_labels_new_recruit_process($id);
      break;
    case 'process_complete':
      return _genvasc_labels_new_recruit_process_complete($id);
      break;
    case 'delete':
      return _genvasc_labels_new_recruit_delete($id);
      break;
    case 'add':
      return drupal_get_form('_genvasc_labels_add_new_recruits_form');
      break;
    default:
      return drupal_get_form('_genvasc_labels_new_recruits_form');
      break;
  }
}

function _genvasc_labels_new_recruits_form($form, &$form_state) {

  $form['Add New'] = array(
    '#type' => 'link',
    '#title' => t('Add new participant.'),
    '#href' => 'content/genvasc_portal/new_recruits/add/',
  );

  $form['outstanding'] = array(
    '#type' => 'item',
    '#title' => 'Outstanding Recruits',
    '#markup' => _genvasc_labels_new_recruits_list()
    );

  return $form;
}

function _genvasc_labels_add_new_recruits_form($form, &$form_state) {

  $form['practice'] = array(
    '#type' => 'textfield',
    '#title' => 'Recruiting Practice',
    '#maxlength' => 128,
    '#autocomplete_path' => 'content/genvasc_portal/practices',
    '#required' => True
  );
  $form['nhs_number'] = array(
    '#type' => 'textfield',
    '#title' => 'NHS Number',
    '#maxlength' => 12,
    '#required' => True
  );
  $form['date_of_birth'] = array(
    '#type' => 'date',
    '#title' => 'Date of Birth',
    '#required' => True,
    '#pre_render' => array('_lcbru_pre_render_blank_date'),
  );
  $form['date_recruited'] = array(
    '#type' => 'date',
    '#title' => 'Date Recruited',
    '#required' => True
  );

  return confirm_form($form,"Add New Participant",'content/genvasc_portal/new_recruits');
}

function _genvasc_labels_add_new_recruits_form_validate($form, &$form_state) {
    civicrm_initialize();

	$nhs_number = $form_state['values']['nhs_number'];
	$practice_name = $form_state['values']['practice'];
	$date_of_birth = $form_state['values']['date_of_birth'];
	$date_recruited = $form_state['values']['date_recruited'];

    if (isInvalidNhsNumber($nhs_number)) {
	    form_set_error('nhs_number', 'Invalid NHS Number');
    }
	$ch = new ContactHelper();
	$matching_practices = $ch->searchPractices($practice_name);
    if (count($matching_practices) == 0) {
	    form_set_error('practice', 'Practice not found.');
    }
    if (count($matching_practices) > 1) {
	    form_set_error('practice', 'More than one practice match found.');
    }
    if (_lcbru_drupal_form_date_to_YMD($date_of_birth) > date('Ymd')) {
	    form_set_error('date_of_birth', 'Date of birth cannot be in the future.');
    }
    if (_lcbru_drupal_form_date_to_YMD($date_recruited) > date('Ymd')) {
	    form_set_error('date_recruited', 'Date recruited cannot be in the future.');
    }
}

function _genvasc_labels_add_new_recruits_form_submit($form, &$form_state) {
    civicrm_initialize();

	$nhs_number = $form_state['values']['nhs_number'];
	$practice_name = $form_state['values']['practice'];
	$date_of_birth = $form_state['values']['date_of_birth'];
	$date_recruited = $form_state['values']['date_recruited'];

	$ch = new ContactHelper();
	$practices = $ch->searchPractices($practice_name);
	$practice = array_shift($practices);

	GenvascPortal::createNewRecruit(
		$nhs_number,
		$practice['id'],
		_lcbru_drupal_form_date_to_YMD($date_of_birth),
		_lcbru_drupal_form_date_to_YMD($date_recruited)
	);
    drupal_goto('content/genvasc_portal/new_recruits');
}

function _genvasc_labels_practices_callback($search_string) {
    civicrm_initialize();

	$ch = new ContactHelper();

	$matches = $ch->searchPractices($search_string);

	$result = array();

	foreach ($matches as $practice) {
		$result[$practice['display_name']] = $practice['display_name'];
	}

	drupal_json_output($result);
}

function _genvasc_labels_new_recruits_list() {
  civicrm_initialize();

  $ch = new ContactHelper();
  $practices = $ch->getPractices();

  $rows = array();

  foreach (GenvascPortal::getOutstandingRecruits() as $record) {
    $links = array();
    $links[] = l('Delete',"content/genvasc_portal/new_recruits/delete/{$record->genvasc_port_recruits_id}");

    if ($record->status == 'Demographics Returned') {
      $links[] = l('Process',"content/genvasc_portal/new_recruits/process/{$record->genvasc_port_recruits_id}");
    }

    $rows[] = array(
      $record->date_recruited,
      $practices[$record->practice_id]['display_name'],
      $record->nhs_number,
      $record->dob,
      $record->status,
      join(' ', $links)
      );
  }

  $header = array(
    array(
      'data' => t('Date Recruited'),
      'datatable_options' => unserialize(DATATABLES_OTHER_COLUMN_OPTION)
    ),
    array(
      'data' => t('Practice'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('NHS Number'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('Date of Birth'),
      'datatable_options' => unserialize(DATATABLES_OTHER_COLUMN_OPTION)
    ),
    array(
      'data' => t('status'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => '',
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
  );

  return theme('datatable', array('header' => $header, 'attributes' => array('datatable_options' => array()), 'rows' => $rows));
}

function _genvasc_labels_new_recruit_process($id) {
  Guard::AssertString_NotEmpty('$id', $id);

  civicrm_initialize();

  $new_recruit = GenvascPortal::get_new_recruit_daps($id);
  if (!empty($new_recruit['date_processed'])) {
    drupal_set_message('Recruit has already been processed.', 'error');
    drupal_goto('content/genvasc_portal/new_recruits');
  }

  return drupal_get_form('_genvasc_labels_portal_participant_import_form', $new_recruit);
}

function _genvasc_labels_new_recruit_process_complete($id) {
  Guard::AssertString_NotEmpty('$id', $id);

  civicrm_initialize();

  $new_recruit = GenvascPortal::get_new_recruit_daps($id);

  return drupal_get_form('_genvasc_labels_portal_participant_complete_form', $new_recruit);
}

function _genvasc_labels_portal_participant_import_form($form, &$form_state, $details) {
  $errors = new ErrorHelper();

  //This is the way to load a css file for a form, we can also load js in the similar manner.
  $form['#attached']['css'][drupal_get_path('module', 'ice_messaging') . '/ice_messaging.css'] = array();

  $form['id'] = array(
    '#type' => 'hidden',
    '#default_value' => $details['id']
  );

  // We have to return false from the onclick in order to make it not submit
  // the form.  Putting 'return false;' after the reload, didn't work.
  // Returning the result of the reload() or !reload() didn't work either.
  // But this did.  I'm not sure if the Star Trek reference is important.
  // Change it if you dare!
  $form['refresh'] = array(
    '#type' => 'button',
    '#value' => 'Refresh',
    '#attributes' => array(
      'onclick' => "return (location.reload() == 'Space: the final frontier');",
    ),
  );

  $form['s_number'] = array(
    '#type' => 'item',
    '#title' => 'UHL System Number',
    '#markup' => $details[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME]
    );

  $form['nhs_number'] = array(
    '#type' => 'item',
    '#title' => 'NHS Number',
    '#markup' => $details[ContactHelper::NHS_NUMBER_FIELD_NAME]
    );

  $addressOptions = array();

  $form['addresses'] = array(
    '#type' => 'container',
    '#attributes' => array(
        'class' => array('ice_columns'),
        ),
    );

  $cleaned_address = array_values(array_filter(ArrayHelper::get($details, 'address', array())));

  $form['addresses']['imported address'] = array(
    '#type' => 'item',
    '#title' => 'Imported Address',
    '#markup' => implode(",<br />", $cleaned_address)
    );

  $addressOptions['imported'] = t('Imported');

  $ch = new ContactHelper();
  $existingContact = $ch->getSubjectFromIds(
    ArrayHelper::get($details, ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME, ''),
    ArrayHelper::get($details, ContactHelper::NHS_NUMBER_FIELD_NAME, '')
  );

  if (!empty($existingContact) && !empty($existingContact['address_id'])) {
    $form['addresses']['civicrm_address'] = array(
      '#type' => 'item',
      '#title' => 'CiviCRM Address',
      '#markup' => implode(",<br />", array_values($ch->getAddressFields($existingContact)))
      );

    $addressOptions['civicrm'] = t('CiviCRM');
  }

  if (!empty($addressOptions)) {
    $form['import_address'] = array(
      '#type' => 'radios',
      '#title' => t('Import address from'),
      '#default_value' => empty($addressOptions['civicrm']) ? 'imported' : 'civicrm',
      '#options' => $addressOptions,
      '#description' => t('Choose the address you want to use for the import.  If you choose the PMI address this will overwrite the existing address in CiviCRM.'),
    );
  }

  return confirm_form($form,"Confirm New Recruit ${details['first_name']} ${details['last_name']}",'content/genvasc_portal/new_recruits');
}

function _genvasc_labels_portal_participant_import_form_validate($form, &$form_state) {
  $new_recruit = GenvascPortal::get_new_recruit_daps($form_state['values']['id']);

  if (empty($new_recruit)) {
    form_set_error('id', 'Recruit not found:');
  }
  if (!empty($new_recruit['date_processed'])) {
    form_set_error('id', 'Recruit has already been processed:');
  }
}

function _genvasc_labels_portal_participant_import_form_submit($form, &$form_state) {
  $importAddress = $form_state['values']['import_address'];

  $new_recruit = GenvascPortal::get_new_recruit_daps($form_state['values']['id']);

  $ids = _genvasc_labels_add_enrolment($new_recruit, ArrayHelper::get($new_recruit, 'address', array()));

  GenvascPortal::markRecruitProcessed($form_state['values']['id'], $ids['contact_id'], $ids['case_id']);

  drupal_goto("content/genvasc_portal/new_recruits/process_complete/{$form_state['values']['id']}");
}

function _genvasc_labels_add_enrolment(array $details, array $address) {

  $participant_details = array(
    ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME => $details[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME],
    ContactHelper::NHS_NUMBER_FIELD_NAME => $details[ContactHelper::NHS_NUMBER_FIELD_NAME],
    'gender' => $details['gender'],
    'birth_date' => $details['birth_date'],
    'title' => $details['title'],
    'first_name' => $details['first_name'],
    'last_name' => $details['last_name'],
    'supplemental_address_1' => ArrayHelper::get($address, 'supplemental_address_1'),
    'street_address' => ArrayHelper::get($address, 'street_address'),
    'supplemental_address_2' => ArrayHelper::get($address, 'supplemental_address_2'),
    'city' => ArrayHelper::get($address, 'city'),
    'state_province_id' => ArrayHelper::get($address, 'state_province_id'),
    'postal_code' => ArrayHelper::get($address, 'postal_code'),
    'deceased_date' => ArrayHelper::get($details, 'deceased_date'),
    'is_deceased' => ArrayHelper::get($details, 'is_deceased'),
    CIVI_FIELD_GENVASC_POST_CODE_NAME => ArrayHelper::get($address, 'postal_code')
    );

  $caseH = new CaseHelper();
  $genvascCaseType = $caseH->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);

  $contactH = new ContactHelper();
  $practice = $contactH->getSurgeryByID($details['practice_id']);

  $participant_details[CIVI_FIELD_GENVASC_SITE_ID_NAME] = $practice[ContactHelper::CIVI_FIELD_ICE_LOCATION_FIELD_NAME];

  $pi = new ParticipantImporter(
    $genvascCaseType['id'],
    True,
    True
    );
  
  $ids = $pi->importSingle($participant_details);

  $relationshipH = new RelationshipHelper();
  $relationshipH->createRelationship($ids['contact_id'], $practice['id'], CIVI_REL_RECRUITING_SITE, $ids['case_id']);

  $existingRelationship = $relationshipH->getRelationshipOfTypeForContact($ids['contact_id'], CIVI_REL_SURGERY_PATIENT);

  if ($existingRelationship) {
    if ($existingRelationship['contact_id_b'] != $practice['id']) {
      drupal_set_message("That patient already has a GP surgery registered. Please check the record manually, and supply an 'end date' for one of the relationships.", "warning");
    }
  } else {
    $relationshipH->createRelationship($ids['contact_id'], $practice['id'], CIVI_REL_SURGERY_PATIENT, $ids['case_id']);
  }

  return $ids;
}

function _genvasc_labels_portal_participant_complete_form($form, &$form_state, $details) {

  $viewContactLink = l('Click here to view the contact in a new window.',"civicrm/contact/view?reset=1&cid={$details['contact_id']}", array('attributes' => array('target'=>'_blank')));
  $viewEnrollmentLink = l('Click here to view the enrolment in a new window.',"civicrm/contact/view/case?reset=1&action=view&id={$details['case_id']}&cid={$details['contact_id']}", array('attributes' => array('target'=>'_blank')));

  drupal_set_title("GENVASC enrolment created for ${details['first_name']} ${details['last_name']}", PASS_THROUGH);

  $form['contact'] = array(
    '#markup' => "<p>$viewContactLink</p>"
    );
  $form['enrollment'] = array(
    '#markup' => "<p>$viewEnrollmentLink</p>"
    );
  $form['buttons']['add'] = array(
        '#type' => 'submit',
        '#value' => t('OK'),
    );

  return $form;
}

function _genvasc_labels_portal_participant_complete_form_submit($form, &$form_states) {
  drupal_goto('content/genvasc_portal/new_recruits');
}

function _genvasc_labels_new_recruit_delete($id) {
  civicrm_initialize();

  $new_recruit = GenvascPortal::get_new_recruit_details($id);

  return drupal_get_form('_genvasc_labels_portal_participant_delete_form', $new_recruit);
}

function _genvasc_labels_portal_participant_delete_form($form, &$form_state, $details) {
  $ch = new ContactHelper();
  $practice = $ch->getPracticeFromId($details['practice_id']);  

  $form['id'] = array(
    '#type' => 'hidden',
    '#default_value' => $details['id']
  );
  $form['practice'] = array(
    '#type' => 'item',
    '#title' => 'Practice',
    '#markup' => $practice['display_name'],
  );
  $form['nhs_number'] = array(
    '#type' => 'item',
    '#title' => 'NHS Number',
    '#markup' => $details['nhs_number'],
  );
  $form['dob'] = array(
    '#type' => 'item',
    '#title' => 'Date of Birth',
    '#markup' => $details['dob'],
  );
  $form['date_recruited'] = array(
    '#type' => 'item',
    '#title' => 'Date Recruited',
    '#markup' => $details['date_recruited'],
  );
  $form['reason'] = array(
    '#type' => 'textfield',
    '#title' => t('Reason'),
    '#required' => True,
    '#maxlength' => 400,
    '#description' => t('Please provide a reson for deleting this recruit.'),
  );

  return confirm_form($form,"Delete Recruit",'content/genvasc_portal/new_recruits');
}

function _genvasc_labels_portal_participant_delete_form_submit($form, &$form_states) {
  GenvascPortal::markRecruitDeleted($form_states['values']['id'], $form_states['values']['reason']);
  drupal_set_message('Recruit deleted');
  drupal_goto('content/genvasc_portal/new_recruits');
}
