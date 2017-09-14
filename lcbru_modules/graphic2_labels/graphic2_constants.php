<?php

/**
 * @file
 * Define some constants for use in the lookup for the GRAPHIC 2 module, which means we can easily move it from one database to another with only one set of changes.
 */

// Table names in ICE messaging database
define('CIVI_TABLE_CONTACT', 'civicrm_contact');
define('CIVI_TABLE_ADDRESS', 'civicrm_address');
define('CIVI_TABLE_CASE_CONTACT', 'civicrm_case_contact');
define('CIVI_TABLE_REL', 'civicrm_relationship');
define('CIVI_TABLE_REL_TYPE', 'civicrm_relationship_type');

//
define('CIVI_TABLE_CONTACT_IDS_NAME', 'Contact_IDs');
define('CIVI_TABLE_GP_DATA_NAME', 'GP_Surgery_data');
define('CIVI_TABLE_GRAPHIC2_DATA_NAME', 'Graphic2');

// Field names in CiviCRM database
define('PAT_DOB', 'birth_date');
define('PAT_NAME', 'display_name');
define('PAT_ADDRESS', 'street_address');
define('PAT_CITY', 'city');
define('PAT_POSTCODE', 'postal_code');
define('GP_NAME', 'display_name');
define('GP_ADDRESS', 'street_address');
define('GP_CITY', 'city');
define('GP_POSTCODE', 'postal_code');

define('GP_C_CODE_NAME', 'Practice_code');
define('PAT_S_NUMBER_NAME', 'UHL_S_number');

define('CIVI_CASE_TYPE_GRAPHIC2', 'GRAPHIC2');
define('CIVI_FIELD_SET_GRAPHIC2', 'GRAPHIC2');

define('LAB_ID_NAME', 'GRAPHIC_LAB_ID');
define('LAB_ID_LABEL', 'GRAPHIC LAB ID');
define('GRAPHIC_ID_NAME', 'GRAPHIC_PARTICIPANT_ID');
define('GRAPHIC_ID_LABEL', 'GRAPHIC PARTICIPANT ID');
define('FAMILY_ID_NAME', 'GRAPHIC_FAMILY_ID');
define('FAMILY_ID_LABEL', 'GRAPHIC FAMILY ID');
define('Consent_for_further_studies_NAME', 'Consent_for_further_studies');
define('Consent_for_further_studies_LABEL', 'Consent for further studies');
define('G1_Blood_Consent_NAME', 'G1_Blood_Consent');
define('G1_Blood_Consent_LABEL', 'G1 Blood Consent');
define('Pre_consent_to_GRAPHIC_2_NAME', 'Pre_consent_to_GRAPHIC_2');
define('Pre_consent_to_GRAPHIC_2_LABEL', 'Pre-consent to GRAPHIC 2');

// Values in CiviCRM database
define('RELATIONSHIP_DESCRIPTION', 'Registered GP surgery is');

