<?php

/**
 * @file
 * The LCBRU module exists only to pre-populate a civicrm installation with the necessary data to function as the LCBRU intended.
 * This file contains functions to support the lcbru.install process.
 *
 * Note: This install works on a vanilla civicrm 4.2.1 install but it makes assumptions regarding the baseline sample data supplied by civicrm.
 * It therefore may not work with future versions of civicrm.
 *
 */

require_once "core.php";

/**
 * Create all the required data customisations.
 * Each function called added here should have a corresponding 'delete' call in lcbru_delete_init
 */
function lcbru_init_required() {
 // TODO: This needs to be rewritten to be re-runnable

watchdog(__FUNCTION__, 'started');
watchdog(__FUNCTION__, 'A');
create_civi_contact_subtype(array("name" => CIVI_SUBTYPE_CONTACT, "parent_id" => "1", "description" => "Individuals who we might recruit into research studies.")); // Create sub-type: contact
create_civi_contact_subtype(array("name" => CIVI_SUBTYPE_STAFF, "parent_id" => "1", "description" => "Staff working for the LCBRU.")); // Create sub-type: LCBRU staff
create_civi_contact_subtype(array("name" => CIVI_SUBTYPE_HW, "parent_id" => "1", "description" => "All health workers, whether LCBRU staff or otherwise.")); // Create sub-type: health worker
create_civi_contact_subtype(array("name" => CIVI_SUBTYPE_SURGERY, "parent_id" => "3", "description" => "")); // Create sub-type: GP surgery

watchdog(__FUNCTION__, 'B');
$cg = create_civi_custom_group(array("title" => CIVI_FIELD_SET_IDENTIFIERS, "extends" => array("Individual", array(str_replace(" ","_",CIVI_SUBTYPE_CONTACT))), "is_active" => 1)); // Create custom fields set: contact IDs
create_civi_custom_field($cg, array("weight"=>1, "label" => CIVI_FIELD_NHS_NUMBER, "data_type" => "String", "html_type" => "Text","is_active"=>1)); // Create custom field: NHS number
create_civi_custom_field($cg, array("weight"=>2, "label" => CIVI_FIELD_S_NUMBER, "data_type" => "String", "html_type" => "Text","is_active"=>1)); // Create custom field: S number

watchdog(__FUNCTION__, 'C');
$cg = create_civi_custom_group(array("title" => CIVI_FIELD_SET_HW_DATA, "extends" => array("Individual", array(str_replace(" ","_",CIVI_SUBTYPE_HW))), "is_active" => 1)); // Create custom fields set: health worker
create_civi_custom_field($cg, array("weight"=>1, "label" => CIVI_FIELD_GMC, "data_type" => "String", "html_type" => "Text","is_active"=>1)); // Create custom field: GMC number
create_civi_custom_field($cg, array("weight"=>1, "label" => CIVI_FIELD_PRACTITIONER, "data_type" => "String", "html_type" => "Text","is_active"=>1)); // Create custom field: practitioner code
create_civi_custom_field($cg, array("weight"=>2, "label" => CIVI_FIELD_GP_ICE, "data_type" => "String", "html_type" => "Text","is_active"=>1)); // Create custom field: ICE code
create_civi_custom_field($cg, array("weight"=>3, "label" => CIVI_FIELD_GCP_DATE, "data_type" => "Date", "html_type" => "Select Date", "end_date_years" => "0", "start_date_years" => "3", "is_active"=>1));// Create custom field: GCP training date

watchdog(__FUNCTION__, 'D');
$cg = create_civi_custom_group(array("title" => CIVI_FIELD_SET_PRACTICE_DATA, "extends" => array("Organization", array(str_replace(" ","_",CIVI_SUBTYPE_SURGERY))), "is_active" => 1)); // Create custom fields set: gp practice
create_civi_custom_field($cg, array("weight"=>1, "label" => CIVI_FIELD_PRACTICE_CODE, "data_type" => "String", "html_type" => "Text","is_active"=>1));// Create custom field: Practice code

watchdog(__FUNCTION__, 'E');
//$cg = create_civi_custom_group(array("title" => CIVI_FIELD_SET_BRANCH_DATA, "extends" => "Address", "is_active" => 1)); // Create custom fields set: gp branch surgery
//create_civi_custom_field($cg, array("weight"=>2, "label" => CIVI_FIELD_ICE_LOCATION, "data_type" => "String", "html_type" => "Text","is_active"=>1));// Create custom field: ICE code

watchdog(__FUNCTION__, 'F');
create_civi_group(array("title" => CIVI_GROUP_STAFF, "is_reserved" => "1", "group_type" => array("1" => 1))); // Create group: LCBRU
create_civi_acl_role(array("label" => CIVI_ACL_STAFF)); // Create ACL role: LCBRU
// Not yet implemented - assign staff ACL role to staff group
// Not yet implemented - allocate specific permissions to ACL role for LCBRU

watchdog(__FUNCTION__, 'F1');
deleteCaseType('housing_support');
deleteCaseType('adult_day_care_referral');

watchdog(__FUNCTION__, 'G');
create_civi_relation_type(array("name_a_b" => CIVI_REL_SENIOR_PARTNER, "name_b_a" => CIVI_REL_SENIOR_PARTNER_IS, "description" => "Designating a senior partner for a GP practice.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_HW), "contact_type_b" => "Organization", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), "is_active" => "1")); // Create relationship type: GP surgery / senior partner
create_civi_relation_type(array("name_a_b" => CIVI_REL_SURGERY_PATIENT, "name_b_a" => CIVI_REL_SURGERY, "description" => "Linking a contact to their GP practice.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Organization", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), "is_active" => "1")); // Create relationship type: GP surgery / patient
create_civi_relation_type(array("name_a_b" => CIVI_REL_GP_PATIENT, "name_b_a" => CIVI_REL_GP, "description" => "Linking a contact to their GP.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW), "is_active" => "1")); // Create relationship type: GP / patient
create_civi_relation_type(array("name_a_b" => CIVI_REL_BLOOD_TAKEN, "name_b_a" => CIVI_REL_VENEPUNCTURE, "description" => "Linking a contact to the person who drew blood for a study.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW), "is_active" => "1")); // Create relationship type: Blood samples taken by / Venepuncturist
create_civi_relation_type(array("name_a_b" => CIVI_REL_STUDY_ADMIN_IS, "name_b_a" => CIVI_REL_STUDY_ADMIN, "description" => "Linking a contact to the study administrator.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_STAFF), "is_active" => "1")); // Create relationship type: In study administered by / Study Administrator
create_civi_relation_type(array("name_a_b" => CIVI_REL_STUDY_PI_IS, "name_b_a" => CIVI_REL_STUDY_PI, "description" => "Linking a contact to the study Principal Investigator.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_STAFF), "is_active" => "1")); // Create relationship type: In study for P.I. / Principal Investigator
create_civi_relation_type(array("name_a_b" => CIVI_REL_STUDY_MAN_IS, "name_b_a" => CIVI_REL_STUDY_MANAGER, "description" => "Linking a contact to the study manager.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_STAFF), "is_active" => "1")); // Create relationship type: In study managed by / Study Manager
create_civi_relation_type(array("name_a_b" => CIVI_REL_STUDY_RECRUITED_BY, "name_b_a" => CIVI_REL_STUDY_RECRUITER, "description" => "Linking a contact to the person who recruited them for a study.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW), "is_active" => "1")); // Create relationship type: Recruited by / Study Recruiter
create_civi_relation_type(array("name_a_b" => CIVI_REL_SAMPLES_PROC_BY, "name_b_a" => CIVI_REL_SAMPLES_PROCESSOR, "description" => "Linking a contact to the person who processed their study samples.", "contact_type_a" => "Individual", "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT), "contact_type_b" => "Individual", "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_STAFF), "is_active" => "1")); // Create relationship type: Blood samples processed by / Study lab operator
update_civi_relation_type(array("name_a_b" => "Benefits Specialist is"), array("is_active" => "0")); // Disable relationship type: Benefits specialist is / Benefits specialist
update_civi_relation_type(array("name_a_b" => "Case Coordinator is"), array("is_active" => "0")); // Disable relationship type: Case coordinator is / Case Coordinator
update_civi_relation_type(array("name_a_b" => "Health Services Coordinator is"), array("is_active" => "0")); // Disable relationship type: Health Services coordinator is / Health Services Coordinator
update_civi_relation_type(array("name_a_b" => "Homeless Services Coordinator is"), array("is_active" => "0")); // Disable relationship type: Homeless Services Coordinator is / Homeless Services Coordinator
update_civi_relation_type(array("name_a_b" => "Senior Services Coordinator is"), array("is_active" => "0")); // Disable relationship type: Senior Services Coordinator is / Senior Services Coordinator
update_civi_relation_type(array("name_a_b" => "Supervised by"), array("is_active" => "0")); // Disable relationship type: Supervised by / Supervisor
update_civi_relation_type(array("name_a_b" => "Volunteer for"), array("is_active" => "0")); // Disable relationship type: Volunteer for / Volunteer is

watchdog(__FUNCTION__, 'H');
$og = get_civi_option_group(array("name" => "gender")); // Get option group for 'gender'
create_civi_option_value($og, array("name" => CIVI_NOT_SPECIFIED)); // Create option values: gender - 'not specified' 
create_civi_option_value($og, array("name" => CIVI_NOT_KNOWN)); // Create option values: gender - 'not known'

watchdog(__FUNCTION__, 'I');
$og = get_civi_option_group(array("name" => "individual_prefix")); // Get option group for 'individual prefix' (6)
create_civi_option_value($og, array("name" => CIVI_PREFIX_PROF)); // Create option values: prefix - 'professor'

watchdog(__FUNCTION__, 'J');
$og = get_civi_option_group(array("name" => "encounter_medium")); // Get option group for 'encounter medium' (70)
create_civi_option_value($og, array("name" => CIVI_ENCOUNTER_GP)); // Create option values: encounter medium: 'In GP surgery'
create_civi_option_value($og, array("name" => CIVI_ENCOUNTER_FORM)); // Create option values: encounter medium: 'Form submitted'

watchdog(__FUNCTION__, 'K');
$og = get_civi_option_group(array("name" => "case_status")); // Get option group for 'case status' (27)
watchdog(__FUNCTION__, 'K1');
create_civi_option_value($og, array("name" => CIVI_CASE_PENDING, "is_default" => "1")); // Create option values: case status: pending
watchdog(__FUNCTION__, 'K2');
create_civi_option_value($og, array("name" => CIVI_CASE_RECRUITED)); // Create option values: case status: recruited
watchdog(__FUNCTION__, 'K3');
create_civi_option_value($og, array("name" => CIVI_CASE_AVAILABLE)); // Create option values: case status: recruited
watchdog(__FUNCTION__, 'K4');
create_civi_option_value($og, array("name" => CIVI_CASE_DECLINED)); // Create option values: case status: declined
watchdog(__FUNCTION__, 'K5');
create_civi_option_value($og, array("name" => CIVI_CASE_WITHDRAWN), TRUE); // Create option values: case status: withdrawn
watchdog(__FUNCTION__, 'K6');
create_civi_option_value($og, array("name" => CIVI_CASE_EXCLUDED)); // Create option values: case status: excluded
watchdog(__FUNCTION__, 'K7');
update_civi_option_value(array("label" => "Resolved", "option_group_id" => $og['id']), array("is_active" => "0")); // mark as inactive option values: case status: resolved
watchdog(__FUNCTION__, 'K8');
update_civi_option_value(array("label" => "Ongoing", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option values: case status: ongoing
watchdog(__FUNCTION__, 'K9');
update_civi_option_value(array("label" => "Urgent", "option_group_id" => $og['id']), array("is_active" => "0")); // mark as inactive option values: case status: urgent


watchdog(__FUNCTION__, 'L');

if (get_civi_option_value($og, "Adult Day Care Referral", TRUE)) {
	update_civi_option_value(array("label" => "Adult Day Care Referral", "option_group_id" => $og['id']), array("is_active" => "0")); // mark as inactive option values: demo case type
}
if (get_civi_option_value($og, "Housing Support", TRUE)) {
	update_civi_option_value(array("label" => "Housing Support", "option_group_id" => $og['id']), array("is_active" => "0")); // mark as inactive option values: demo case type
}
watchdog(__FUNCTION__, 'M');
watchdog(__FUNCTION__, 'N');
// activity types
$og = get_civi_option_group(array("name" => "activity_type")); // Get option group for 'activity type' (2)
// Update default activity types
update_civi_option_value(array("label" => "Medical evaluation", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "Mental health evaluation", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "Secure temporary housing", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "Income and benefits stabilization", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "Long-term housing plan", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "ADC referral", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: 
update_civi_option_value(array("label" => "Change Case Type", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: change case type makes no sense in research 
update_civi_option_value(array("label" => "Merge Case", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: merge case makes no sense in research 
update_civi_option_value(array("label" => "Link Cases", "option_group_id" => $og['id']), array("is_active" => "0", "is_default" => "0")); // mark as inactive option value: link cases makes no sense in research 
update_civi_option_value(array("label" => "Open Case", "option_group_id" => $og['id']), array("label" => "Enrol into study", "is_default" => "0")); // change label for 'open case' option value: 
update_civi_option_value(array("label" => "Change Case Status", "option_group_id" => $og['id']), array("label" => "Change Enrolment Status", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Change Case Start Date", "option_group_id" => $og['id']), array("label" => "Change Enrolment Start Date", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Assign Case Role", "option_group_id" => $og['id']), array("label" => "Assign Study Role", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Remove Case Role", "option_group_id" => $og['id']), array("label" => "Remove Study Role", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Reassigned Case", "option_group_id" => $og['id']), array("label" => "Reassigned Study Enrolment", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Change Case Tags", "option_group_id" => $og['id']), array("label" => "Change Study Enrolment Tags", "is_default" => "0")); // change label for option value: 
update_civi_option_value(array("label" => "Add Client To Case", "option_group_id" => $og['id']), array("label" => "Add Subject to Enrolment", "is_default" => "0")); // change label for option value: 

watchdog(__FUNCTION__, 'O');
$og = get_civi_option_group(array("name" => "activity_status")); // Get option group for 'activity status' (25)
create_civi_option_value($og, array("name" => CIVI_STATUS_AUTO)); // Create option values: activity status: 'automated'

// tags - disable existing tags


watchdog(__FUNCTION__, 'P');
// populate with initial data
lcbru_populate_data();

watchdog(__FUNCTION__, 'finished');
}

/**
 * Populate contact data for GP surgeries
 */
function lcbru_populate_data() {

// Populate GP surgeries - currently only the pilot sites

$practices = array(
  array(
    "name" => "Dr S Longworth and Partners", 
    "practice_code" => "C82063", 
    "sites" => array(
      array(
        "supplemental_address_1" => "East Leicester Medical Practice", 
        "street_address" => "131 Uppingham Road", 
        "supplemental_address_2" => "", 
        "city" => "Leicester", 
        "postal_code" => "LE5 4BP", 
        "location_type_id" => "3",
        "is_primary" => "1", 
        "ice_code" => "UPP13B", 
      ),
    ), 
    "phones" => array(
      array(
        "phone" => "01162958282", 
        "location_type_id" => "3", 
        "is_primary" => "1", 
      ),
    ), 
    "staff" => array(
      array(
        "prefix" => "Dr",
        "first_name" => "Stephen", 
        "middle_name" => "", 
        "last_name" => "Longworth", 
        "senior_partner" => "1", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "2709480", 
		'gcp_date' => '',
        "practitioner_code" => "G8510464", 
        "ice_code" => "LON1", 
      ),
      array(
        "prefix" => "Prof",
        "first_name" => "Azhar", 
        "middle_name" => "", 
        "last_name" => "Farooqi", 
        "senior_partner" => "0", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "2802170", 
		'gcp_date' => '',
        "practitioner_code" => "G8708777", 
        "ice_code" => "FAR2", 
      ),
      array(
        "prefix" => "Ms",
        "first_name" => "Joanne", 
        "middle_name" => "", 
        "last_name" => "Sexton", 
        "senior_partner" => "0", 
        "job_title" => "Practice Nurse", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "SEX1", 
      ),
      array(
        "prefix" => "Ms", 
        "first_name" => "Emma", 
        "middle_name" => "", 
        "last_name" => "Flint", 
        "senior_partner" => "0", 
        "job_title" => "Practice Nurse", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "FLI1", 
      ),
      array(
        "prefix" => "Mr", 
        "first_name" => "Amit", 
        "middle_name" => "", 
        "last_name" => "Rawal", 
        "senior_partner" => "0", 
        "job_title" => "Practice Manager", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "", 
      ),
    ),
  ), 

  array(
    "name" => "Dr J Astles and Partners",
    "practice_code" => "C82029", 
    "sites" => array(
      array(
       "supplemental_address_1" => "Willowbrook Medical Centre", 
       "street_address" => "195 Thurncourt Road", 
       "supplemental_address_2" => "Thurnby Lodge", 
       "city" => "Leicester", 
       "postal_code" => "LE5 2NL", 
       "location_type_id" => "3", 
       "is_primary" => "1",
       "ice_code" => "THU195", 
      ),
      array(
        "supplemental_address_1" => "Springfield Road Health Centre", 
        "street_address" => "Springfield Road", 
        "supplemental_address_2" => "", 
        "city" => "Leicester", 
        "postal_code" => "LE2 3BB", 
        "location_type_id" => "4", 
        "ice_code" => "SPRA",
        "is_primary" => "",
      ),
    ), 
    "phones" => array(
      array(
        "phone" => "01162432727", 
        "location_type_id" => "3", 
        "is_primary" => "1", 
      ),
      array(
        "phone" => "01162256670", 
        "location_type_id" => "4", 
        "is_primary" => "", 
      ), 
    ), 
    "staff" => array(
      array(
        "prefix" => "Dr", 
        "first_name" => "John", 
        "middle_name" => "", 
        "last_name" => "Astles", 
        "senior_partner" => "1", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "2438168", 
		'gcp_date' => '',
        "practitioner_code" => "G8211284", 
        "ice_code" => "AST1", 
      ),
      array(
        "prefix" => "Dr", 
        "first_name" => "Nitin", 
        "middle_name" => "", 
        "last_name" => "Joshi", 
        "senior_partner" => "0", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "3256938", 
		'gcp_date' => '',
        "practitioner_code" => "G9111275", 
        "ice_code" => "JOS1", 
      ),
      array(
        "prefix" => "Dr",
        "first_name" => "Rishabh", 
        "middle_name" => "", 
        "last_name" => "Prasad", 
        "senior_partner" => "0", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "6077335", 
		'gcp_date' => '',
        "practitioner_code" => "G8842435", 
        "ice_code" => "PRA8", 
      ),
      array(
        "prefix" => "Mrs",
        "first_name" => "Patricia", 
        "middle_name" => "", 
        "last_name" => "Marriott", 
        "senior_partner" => "0", 
        "job_title" => "Practice Nurse", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "356B", 
      ),
      array(
        "prefix" => "Mrs",
        "first_name" => "Vivian", 
        "middle_name" => "", 
        "last_name" => "Botting", 
        "senior_partner" => "0", 
        "job_title" => "Practice Manager", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "", 
      ),
    ),
  ), 

  array(
    "name" => "Dr HDD Nandha and Partners", 
    "practice_code" => "C82088", 
    "sites" => array(
      array(
        "supplemental_address_1" => "Evington Medical Centre", 
        "street_address" => "2 - 6 Halsbury Street", 
        "supplemental_address_2" => "", 
        "city" => "Leicester", 
        "postal_code" => "LE2 1QA", 
        "location_type_id" => "3", 
        "is_primary" => "1", 
        "ice_code" => "HAL2", 
      ), 
      array(
        "supplemental_address_1" => "Loughborough Road Surgery", 
        "street_address" => "32 Loughborough Road", 
        "supplemental_address_2" => "", 
        "city" => "Leicester", 
        "postal_code" => "LE4 5LD", 
        "location_type_id" => "4", 
        "ice_code" => "LOU32", 
        "is_primary" => "", 
      ),       
    ), 
    "phones" => array(
      array(
        "phone" => "08444773587", 
        "location_type_id" => "3", 
        "is_primary" => "1", 
      ), 
      array(
        "phone" => "08444773509", 
        "location_type_id" => "4", 
        "is_primary" => "", 
      ), 
    ),
    "staff" => array(
      array(
        "prefix" => "Dr", 
        "first_name" => "Ranjit", 
        "middle_name" => "", 
        "last_name" => "Thakor", 
        "senior_partner" => "0", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "1727560", 
		'gcp_date' => '',
        "practitioner_code" => "G3358067", 
        "ice_code" => "THA1", 
      ),
      array(
        "prefix" => "Dr", 
        "first_name" => "Hasmukh", 
        "middle_name" => "", 
        "last_name" => "Nandha", 
        "senior_partner" => "1", 
        "job_title" => "General Practitioner", 
        "gmc_number" => "4251446", 
		'gcp_date' => '',
        "practitioner_code" => "G9711280", 
        "ice_code" => "NAN1", 
      ),
      array(
        "prefix" => "Mrs", 
        "first_name" => "Ranjan", 
        "middle_name" => "", 
        "last_name" => "Bhayani", 
        "senior_partner" => "0", 
        "job_title" => "Practice Nurse", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "193A", 
      ),
      array(
        "prefix" => "Mrs", 
        "first_name" => "Danni", 
        "middle_name" => "", 
        "last_name" => "Patel", 
        "senior_partner" => "0", 
        "job_title" => "Practice Manager", 
        "gmc_number" => "", 
		'gcp_date' => '',
        "practitioner_code" => "", 
        "ice_code" => "", 
      ),
    ),
  ), 
);


foreach ($practices as $practice) {

  $siteslist = array();
  foreach ($practice['sites'] as $skey => $site) {
    $siteslist["api.address.create.$skey"] = array(
        "supplemental_address_1" => $site['supplemental_address_1'], 
        "street_address" => $site['street_address'], 
        "supplemental_address_2" => $site['supplemental_address_2'], 
        "city" => $site['city'], 
        "postal_code" => $site['postal_code'], 
        "location_type_id" => $site['location_type_id'], 
        "is_primary" => $site['is_primary'], 
        "state_province_id" => "2709", 
        "country_id" => "1226",
    );
  }

  $phoneslist = array();
  foreach ($practice['phones'] as $pkey => $phone) {
    $phoneslist["api.phone.create.$pkey"] = array(
        "phone" => $phone['phone'], 
        "location_type_id" => $phone['location_type_id'], 
        "is_primary" => $phone['is_primary'], 
        "phone_type_id" => "1",
    );
  }

  $result = create_civi_contact_with_custom_data(array_merge(array(
    "contact_type" => "Organization", 
    "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_SURGERY)), 
    "organization_name" => $practice['name'], 
    ),
    $siteslist, 
    $phoneslist
    ), 
    array(
    CIVI_FIELD_PRACTICE_CODE => $practice['practice_code'],
    )); // Create GP surgery

  foreach ($practice['sites'] as $site) {
    attach_site_ice_code($practice['name'],$site['supplemental_address_1'],$site['ice_code']);
  }


  foreach ($practice['staff'] as $hw) {   // Populate external healthworkers for GP surgeries - give them the primary address and phone number, and the relationship(s) with their employer

    // Look up the prefix to get the prefix_id
    $og = get_civi_option_group(array("name" => "individual_prefix")); // Get option group for 'individual prefix'
    $prefix = get_civi_option_value($og,$hw['prefix']);

    // Remember master address id and master phone id in $result array - it becomes important
    $staff = create_civi_contact_with_custom_data(array(
      "contact_type" => "Individual", 
      "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_HW)), 
      "first_name" => $hw['first_name'], 
      "middle_name" => $hw['middle_name'], 
      "last_name" => $hw['last_name'], 
      "job_title" => $hw['job_title'], 
      "prefix_id" => $prefix['value'], 
      "current_employer" => $practice['name'], 
      "api.address.create" => array("master_id" => $result['api.address.create.0']['id'], "postal_code" => $siteslist['api.address.create.0']['postal_code'], "city" => $siteslist['api.address.create.0']['city'], "supplemental_address_1" => $siteslist['api.address.create.0']['supplemental_address_1'], "street_address" => $siteslist['api.address.create.0']['street_address'], "supplemental_address_2" => $siteslist['api.address.create.0']['supplemental_address_2'], "location_type_id" => "2", "is_primary" => "1", "state_province_id" => "2709", "country_id" => "1226"), 
      "api.phone.create" => array("master_id" => $result['api.address.create.0']['id'], "phone" => $phoneslist['api.phone.create.0']['phone'], "location_type_id" => "2", "is_primary" => "1", "phone_type_id" => "1"), 
      ),
      array(
      CIVI_FIELD_GMC => $hw['gmc_number'],
      CIVI_FIELD_PRACTITIONER => $hw['practitioner_code'],
      CIVI_FIELD_GP_ICE => $hw['ice_code'],
      CIVI_FIELD_GCP_DATE => $hw['gcp_date'],
      )); // Create healthworker

/*  Commented out because the relationship is created automatically when the staff member is added with a 'current_employer' field.
 *      $rel = find_civi_relationship_type("Employee of");
 *      create_civi_relationship($rel, $staff['id'], $result['id']);
 */

      if ($hw['senior_partner'] == "1") {
        $rel = find_civi_relationship_type(CIVI_REL_SENIOR_PARTNER);
        create_civi_relationship($rel, $staff['id'], $result['id']);
      }
    }
  }
  
  // Create a cron user to be allocated to any CiviCRM cron activities (such as the GENVASC 'mark as available' job)
  $cron_user = create_civi_contact(array(
      "contact_type" => "Individual", 
      "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_STAFF)), 
      "first_name" => "Cron", 
      "last_name" => "System", 
      "job_title" => "Ghost in the Machine", 
      "nick_name" => "Mycroft",
      ),FALSE
      ); // Create contact with no associated Drupal user account.
  
}