<?php

/**
 * @file
 * Define some constants for use in the LCBRU civicrm extension module
 */

// Contact sub types
define('CIVI_SUBTYPE_CONTACT', 'Subject');
define('CIVI_SUBTYPE_STAFF', 'LCBRU Staff');
define('CIVI_SUBTYPE_HW', 'Health Worker');

define('CIVI_SUBTYPE_SURGERY', 'GP Surgery');
define('CIVI_SUBTYPE_CCG', 'CCG');
define('CIVI_SUBTYPE_LOCALITY', 'Locality');

// Custom fields
define('CIVI_FIELD_SET_IDENTIFIERS', 'Contact IDs');
define('CIVI_FIELD_NHS_NUMBER', 'NHS number');
define('CIVI_FIELD_S_NUMBER', 'UHL S number');

define('CIVI_FIELD_SET_HW_DATA', 'Health Worker data');
define('CIVI_FIELD_GMC', 'GP GMC number');
define('CIVI_FIELD_PRACTITIONER', 'GP Practitioner code');
define('CIVI_FIELD_GP_ICE', 'GP ICE code');
define('CIVI_FIELD_GCP_DATE', 'GCP training date');

define('CIVI_FIELD_SET_PRACTICE_DATA', 'GP Surgery data');
define('CIVI_FIELD_PRACTICE_CODE', 'Practice code');
define('CIVI_FIELD_PRACTICE_STATUS', 'Practice status');

define('CIVI_FIELD_SET_BRANCH_DATA', 'GP site-based data');
define('CIVI_FIELD_ICE_LOCATION', 'Practice ICE code');
define('CIVI_FIELD_BRANCH_CODE', 'Practice Branch code');

// ACL roles and groups
define('CIVI_ACL_STAFF', 'LCBRU staff only');
define('CIVI_GROUP_STAFF', 'LCBRU staff only');

// Relationships
define('CIVI_REL_SURGERY_PATIENT', 'Registered GP surgery is');
define('CIVI_REL_SURGERY', 'GP surgery');
define('CIVI_REL_GP_PATIENT', 'Registered GP is');
define('CIVI_REL_GP', 'GP');
define('CIVI_REL_BLOOD_TAKEN', 'Blood taken by');
define('CIVI_REL_VENEPUNCTURE', 'Venepuncturist');
define('CIVI_REL_STUDY_ADMIN_IS', 'Study Administrator is');
define('CIVI_REL_STUDY_ADMIN', 'Study Administrator');
define('CIVI_REL_STUDY_PI_IS', 'Study P.I. is');
define('CIVI_REL_STUDY_PI', 'Principal Investigator');
define('CIVI_REL_STUDY_MAN_IS', 'Study Manager is');
define('CIVI_REL_STUDY_MANAGER', 'Study Manager');
define('CIVI_REL_STUDY_RECRUITED_BY', 'recruited by');
define('CIVI_REL_STUDY_RECRUITER', 'Recruiter for study');
define('CIVI_REL_SAMPLES_PROC_BY', 'samples processed by');
define('CIVI_REL_SAMPLES_PROCESSOR', 'processed samples in lab');
define('CIVI_REL_SENIOR_PARTNER_IS', 'Senior Partner Is');
define('CIVI_REL_SENIOR_PARTNER', 'Senior Partner Of');
define('CIVI_REL_RECRUITING_SITE', 'Recruiting site');
define('CIVI_REL_RECRUITING_SITE_FOR', 'Recruiting site for study');
define('CIVI_REL_CCG', 'CCG');
define('CIVI_REL_CCG_FOR', 'CCG for');
define('CIVI_REL_SURGEON', 'Surgeon');
define('CIVI_REL_SURGEON_FOR', 'Surgeon for');
define('CIVI_REL_GENVASC_CONTACT', 'Practice GENVASC Contact');
define('CIVI_REL_GENVASC_CONTACT_FOR', 'Practice GENVASC Contact for');
define('CIVI_REL_PRACTICE_MANAGER', 'Practice Manager');
define('CIVI_REL_PRACTICE_MANAGER_FOR', 'Practice Manager for');
define('CIVI_REL_LOCALITY_MANAGER', 'Locality Manager');
define('CIVI_REL_LOCALITY_MANAGER_FOR', 'Locality Manager for');
define('CIVI_REL_LOCALITY', 'Locality');
define('CIVI_REL_LOCALITY_FOR', 'Locality for');
define('CIVI_REL_CRN_STUDY_CONTACT', 'CRN Study Contact');
define('CIVI_REL_CRN_STUDY_CONTACT_FOR', 'CRN Study Contact for');

// Option values
define('CIVI_NOT_SPECIFIED', 'Not Specified');
define('CIVI_NOT_KNOWN', 'Not Known');
define('CIVI_PREFIX_PROF', 'Professor');

// Encounter medium
define('CIVI_ENCOUNTER_GP', 'In GP Surgery');
define('CIVI_ENCOUNTER_FORM', 'Form submitted');

// Case status
define('CIVI_CASE_PENDING', 'Recruitment pending');
define('CIVI_CASE_RECRUITED', 'Recruited');
define('CIVI_CASE_AVAILABLE', 'Available for cohort');
define('CIVI_CASE_DECLINED', 'Declined');
define('CIVI_CASE_WITHDRAWN', 'Withdrawn');
define('CIVI_CASE_EXCLUDED', 'Excluded');
define('CIVI_CASE_FAILED_TO_RESPOND', 'Failed to Respond');
define('CIVI_CASE_DUPLICATE', 'Duplicate');

// Activity status
define('CIVI_STATUS_AUTO', 'Automated');
define('CIVI_ACTIVITY_STATUS_BOOKED', 'Booked');
define('CIVI_ACTIVITY_STATUS_SCHEDULED', 'Scheduled');
define('CIVI_ACTIVITY_STATUS_NOT_REQUIRED', 'Not Required');
define('CIVI_ACTIVITY_STATUS_COMPLETED', 'Completed');

// CCG Custom Fields
define('CIVI_FIELD_SET_CCG_DATA', 'CCG data');
define('CIVI_FIELD_CCG_CODE', 'CCG Code');
define('CIVI_FIELD_CCG_GENVASC_PI', 'Genvasc Principal Investigator');
define('CIVI_FIELD_CCG_CLRN_SITE_ID', 'CLRN Site ID');

// BRICCS Case types, custom group, custom fields
define('CIVI_CASE_TYPE_BRICCS', 'BRICCS');
define('CIVI_FIELD_SET_BRICCS_DATA', 'BRICCS recruitment data');
define('CIVI_FIELD_BRICCS_ID', 'BRICCS ID');
define('CIVI_FIELD_BRICCS_RECRUITMENT_TYPE', 'Recruitment Type');
define('CIVI_FIELD_BRICCS_INTERVIEW_DATETIME', 'Interview Date and Time');
define('CIVI_FIELD_BRICCS_INTERVIEWER', 'Interviewer');
define('CIVI_FIELD_BRICCS_INTEVIEW_STATUS', 'Interview Status');
define('CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_CONSENT', 'Consent Understands Consent');
define('CIVI_FIELD_BRICCS_CONSENT_BLOOD_AND_URINE', 'Consent Blood and Urine');
define('CIVI_FIELD_BRICCS_CONSENT_BRICCS_DATABASE', 'Consent BRICCS Database');
define('CIVI_FIELD_BRICCS_CONSENT_FURTHER_CONTACT', 'Consent Further Contact');
define('CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_WITHDRAWAL', 'Consent Understands Withdrawal');
define('CIVI_FIELD_BRICCS_INVITATION_FOR', 'Invitation For ...');

// TMAO Case type
define('CIVI_CASE_TYPE_TMAO', 'TMAO');

define('CIVI_FIELD_SET_TMAO', 'TMAO');
define('CIVI_FIELD_TMAO_ID', 'TMAO ID');
define('CIVI_FIELD_TMAO_CONSENT_READ_INFORMATION_SHEET', 'TMAO Consent has read information sheet');
define('CIVI_FIELD_TMAO_CONSENT_UNDERSTANDS_WITHDRAWAL', 'TMAO Consent understands withdrawal');
define('CIVI_FIELD_TMAO_CONSENT_ACCESS_NOTES', 'TMAO Consent permission to access notes');
define('CIVI_FIELD_TMAO_CONSENT_GP_INFORMED', 'TMAO Consent GP informed');
define('CIVI_FIELD_TMAO_CONSENT_ENROLMENT', 'TMAO Consent to enrol');
define('CIVI_FIELD_TMAO_CONSENT_STORE_BLOOD', 'TMAO Consent to store blood');

// DREAM Case type
define('CIVI_CASE_TYPE_DREAM', 'DREAM');

// EMMACE 4 Case type
define('CIVI_CASE_TYPE_EMMACE_4', 'EMMACE4');

// EMMACE 4 Group for contacts in study
define('CIVI_GROUP_EMMACE_4', 'EMMACE4');

// EMMACE_4 custome fields
define('CIVI_FIELD_SET_EMMACE_4_DATA', 'EMMACE_4 recruitment data');
define('CIVI_FIELD_EMMACE_4_ID', 'EMMACE_4 ID');

// SCAD Case type
define('CIVI_CASE_TYPE_SCAD', 'SCAD');

// SCAD Group for contacts in study
define('CIVI_GROUP_SCAD', 'SCAD');

// SCAD Custom Fields
define('CIVI_FIELD_SET_SCAD', 'SCAD');
define('CIVI_FIELD_SCAD_BRICCS_ID', 'BRICCS ID');
define('CIVI_FIELD_SCAD_SURVEY_REFERENCE', '1st SCAD Survey ID');
define('CIVI_FIELD_SCAD_SURVEY_REFERENCE_2', '2nd SCAD Survey ID');
define('CIVI_FIELD_SCAD_VISIT_ID', 'SCAD Visit ID');
define('CIVI_FIELD_SCAD_ID', 'SCAD ID');
define('CIVI_FIELD_SCAD_FAMILY_ID', 'Family ID');
define('CIVI_FIELD_SCAD_CONSENT_READ_INFORMATION', 'Consent: Read information');
define('CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_WITHDRAWAL', 'Consent: Understands withdrawal');
define('CIVI_FIELD_SCAD_CONSENT_PROVIDE_INFORMATION', 'Consent: Provide medical information');
define('CIVI_FIELD_SCAD_CONSENT_CONTACT_BY_RESEARCH_TEAM', 'Consent: Contact by research team');
define('CIVI_FIELD_SCAD_CONSENT_INVESTIGATIONS', 'Consent: Investigations');
define('CIVI_FIELD_SCAD_CONSENT_SAMPLE_STORAGE', 'Consent: Sample storage');
define('CIVI_FIELD_SCAD_CONSENT_NO_FINANCIAL_BENEFIT', 'Consent: No financial benefit');
define('CIVI_FIELD_SCAD_CONSENT_CONTACT_GP', 'Consent: Contact GP');
define('CIVI_FIELD_SCAD_CONSENT_DNA_SEQUENCING', 'Consent: DNA sequencing');
define('CIVI_FIELD_SCAD_CONSENT_SKIN_BIOPSY', 'Consent: Skin biopsy');
define('CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_HOW_TO_CONTACT_RESEARCH_TEAM', 'Consent: Understands how to contact research team');
define('CIVI_FIELD_SCAD_CONSENT_SHARE_WITH_MAYO_CLINIC', 'Consent: Share Information with Mayo Clinic');
define('CIVI_FIELD_SCAD_CONSENT_ACCESS_TO_MEDICAL_RECORD', 'Consent: Access to medical records');
define('CIVI_FIELD_SCAD_CONSENT_CONTACT_FOR_RELATED_STUDIES', 'Consent: Contact for related studies');
define('CIVI_FIELD_SCAD_CONSENT_RECEIVE_RESEARCH_SUMMARY', 'Consent: Receive research sumary');
define('CIVI_FIELD_SCAD_CONSENT_DATE', 'Consent date');
define('CIVI_FIELD_SCAD_RECRUITMENT_TYPE', 'Recruitment Type');

// OMICS Register Case type
define('CIVI_CASE_TYPE_OMICS_REGISTER', 'OMICS_REGISTER');

define('CIVI_FIELD_SET_OMICS_REGISTER', 'OMICS_REGISTER');
define('CIVI_FIELD_OMICS_REGISTER_ID', 'OMICS ID');
define('CIVI_FIELD_OMICS_REGISTER_OMICS_TYPE', 'OMICS Type');
define('CIVI_FIELD_OMICS_REGISTER_SAMPLE_SOURCE_STUDY', 'Sample Source Study');
define('CIVI_FIELD_OMICS_REGISTER_FAILED_QC', 'Failed QC');
define('CIVI_FIELD_OMICS_REGISTER_DATE_DATA_RECEIVED', 'Date Data Received');

// Omics Register Group for contacts in study
define('CIVI_GROUP_OMICS_REGISTER', 'OMICS_REGISTER');

// AMAZE Case type
define('CIVI_CASE_TYPE_AMAZE', 'AMAZE');

define('CIVI_FIELD_SET_AMAZE', 'AMAZE');
define('CIVI_FIELD_AMAZE_ID', 'AMAZE ID');

// Global Leaders
define('CIVI_CASE_TYPE_GLOBAL_LEADERS', 'GLOBAL_LEADERS');

define('CIVI_FIELD_SET_GLOBAL_LEADERS', 'GLOBAL_LEADERS');
define('CIVI_FIELD_GLOBAL_LEADERS_ID', 'GLOBAL LEADERS ID');
define('CIVI_FIELD_GLOBAL_LEADERS_TREATMENT', 'Treatment Arm');
define('CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_EXPERIMENTAL', 'Experimental Treatment');
define('CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_UNSTABLE', 'Standard Treatment for Unstable Patients');
define('CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_STABLE', 'Standard Treatment for Stable Patients');


// BRAVE Case type
define('CIVI_CASE_TYPE_BRAVE', 'BRAVE');

define('CIVI_FIELD_SET_BRAVE', 'BRAVE');
define('CIVI_FIELD_BRAVE_ID', 'BRAVE ID');
define('CIVI_FIELD_BRAVE_BRICCS_ID', 'BRICCS ID');
define('CIVI_FIELD_BRAVE_FAMILY_ID', 'BRAVE Family ID');
define('CIVI_FIELD_BRAVE_SOURCE_STUDY', 'BRAVE Source Study');

// BRAVE Case type
define('CIVI_CASE_TYPE_NAME_GENVASC_SITE_MANAGEMENT', 'GENVASC_SITE_MANAGEMENT');
define('CIVI_CASE_TYPE_TITLE_GENVASC_SITE_MANAGEMENT', 'GENVASC Site Management');

define('CIVI_FIELD_SET_GENVASC_SITE_MANAGEMENT', 'GENVASC_SITE_MANAGEMENT');
define('CIVI_FIELD_GENVASC_SITE_IT_SYSTEM', 'IT System');
define('CIVI_FIELD_GENVASC_SITE_RSI', 'Research Site Initiative');

// INTERVAL Case type
define('CIVI_CASE_TYPE_INTERVAL', 'INTERVAL');

define('CIVI_FIELD_SET_INTERVAL_DATA', 'INTERVAL_DATA');
define('CIVI_FIELD_INTERVAL_ID', 'Interval ID');
define('CIVI_FIELD_INTERVAL_CONSENT_DATE', 'Consent Date');
define('CIVI_FIELD_INTERVAL_CONSENT_VERSION', 'Consent Version');
define('CIVI_FIELD_INTERVAL_CONSENT_LEAFLET', 'Consent Leaflet');

// INTERVAL Case type
define('CIVI_CASE_TYPE_MERMAID', 'MERMAID');

// FAST
define('CIVI_CASE_TYPE_FAST', 'FAST');
define('CIVI_FIELD_SET_FAST', 'FAST');
define('CIVI_FIELD_FAST_ID', 'FAST ID');

// FOAMI
define('CIVI_CASE_TYPE_FOAMI', 'FOAMI');
define('CIVI_FIELD_SET_FOAMI', 'FOAMI');
define('CIVI_FIELD_FOAMI_ID', 'FOAMI ID');

// Indapamide Case type
define('CIVI_CASE_TYPE_INDAPAMIDE', 'Indapamide');

define('CIVI_FIELD_SET_INDAPAMIDE', 'Indapamide');
define('CIVI_FIELD_INDAPAMIDE_ID', 'Indapamide ID');

// NIBR Bioresource Sub-Studies
define('CIVI_CASETYPE_BIORESOURCE_SUB_STUDY', 'BIORESOURCE_SUB_STUDY');

define('CIVI_CUSTOMGROUP_BIORESOURCE_SUB_STUDY', 'BIORESOURCE_SUB_STUDY');
define('CIVI_FIELD_BIORESOURCE_SUB_STUDY_NAME', 'Sub-study');

// LENTEN
define('CIVI_CASETYPE_LENTEN', 'LENTEN');

define('CIVI_CUSTOMGROUP_LENTEN', 'LENTEN');
define('CIVI_FIELD_LENTEN_ID', 'LENTEN ID');

// Activities
define('CIVI_ACTIVITY_CHANGE_STATUS', 'Change Enrolment Status');
define('CIVI_ACTIVITY_RECRUIT', 'Recruit to study');
define('CIVI_ACTIVITY_TAKE_SAMPLES', 'Take study samples');
define('CIVI_ACTIVITY_REGISTER', 'Register on study');
define('CIVI_ACTIVITY_FETCH_SAMPLES', 'Fetch samples from pathology');
define('CIVI_ACTIVITY_CHECK_CONSENT', 'Check study consent');
define('CIVI_ACTIVITY_PROCESS_SAMPLES', 'Process study samples');
define('CIVI_ACTIVITY_MAKE_AVAILABLE', 'Mark as available for cohorting');
define('CIVI_ACTIVITY_WITHDRAW', 'Withdraw from study');
define('CIVI_ACTIVITY_GP_LETTER', 'Letter to GP');
define('CIVI_ACTIVITY_THANK_YOU_TO_PARTICIPANT_LETTER', 'Thank you letter to participant');
define('CIVI_ACTIVITY_INV_LETTER', 'Invitation letter');
define('CIVI_ACTIVITY_REM_LETTER', 'Reminder letter');
define('CIVI_ACTIVITY_CHECK_QUEST', 'Check study questionnaire');
define('CIVI_ACTIVITY_INPUT_DATA', 'Input study data');
define('CIVI_ACTIVITY_POST_QUESTIONNAIRE', 'Post out questionnaire');
define('CIVI_ACTIVITY_POST_CONSENT_FORM', 'Post out consent form');
define('CIVI_ACTIVITY_RECRUIT_AND_INTERVIEW', 'Recruit and interview');
define('CIVI_ACTIVITY_LOADED_INTO_I2B2', 'Participant loaded into i2b2');

define('CIVI_ACTIVITY_RECEIVE_QUESTIONNAIRE', 'Receive questionnaire');
define('CIVI_ACTIVITY_DISCHARGE_PATIENT', 'Discharge patient');
define('CIVI_ACTIVITY_SEND_DATA_TO_LEAD_SITE', 'Send data to lead site');
define('CIVI_ACTIVITY_SEND_DATA_TO_SATALLITE_SITE', 'Send data to satallite site');
define('CIVI_ACTIVITY_ATTENDED', 'Attended');
define('CIVI_ACTIVITY_INVESTIGATION', 'Investigation');
define('CIVI_ACTIVITY_ACKNOWLEDGEMENT', 'Acknowledgement');
define('CIVI_ACTIVITY_QUESTIONNAIRE_STARTED', 'Questionnaire started');
define('CIVI_ACTIVITY_QUESTIONNAIRE_COMPLETED', 'Questionnaire completed');
define('CIVI_ACTIVITY_POST_STUDY_INFORMATION_SUPPLIED', 'Post study information supplied');
define('CIVI_ACTIVITY_SUBMITTED_FOR_REIMBURSEMENT', 'Submitted for Reimbursement');
define('CIVI_ACTIVITY_INFORM_GP_PRACTICE', 'Inform GP practice');
define('CIVI_ACTIVITY_MRI', 'MRI');
define('CIVI_ACTIVITY_INTERVENTION', 'Intervention');
define('CIVI_ACTIVITY_RANDOMISE', 'Randomise');
define('CIVI_ACTIVITY_ARRANGE_TRANSPORT', 'Arrange Transport');
define('CIVI_ACTIVITY_ISSUE_PARKING_PERMIT', 'Issue Parking Permit');
define('CIVI_ACTIVITY_ARRANGE_APPOINTMENT', 'Arrange Appointment');
define('CIVI_ACTIVITY_NOTES_REQUESTED', 'Notes Requested');
define('CIVI_ACTIVITY_TELEPHONE_PARTICIPANT', 'Telephone Participant');
define('CIVI_ACTIVITY_SCREENING', 'Screening');
define('CIVI_ACTIVITY_FAMILY_ENQUIRY', 'Family Enquiry');
define('CIVI_ACTIVITY_APPOINTMENT', 'Appointment');
define('CIVI_ACTIVITY_ARRANGE_HOSPITALITY', 'Arrange Hospitality');
define('CIVI_ACTIVITY_CHECK_EXCLUSION_CRITERIA', 'Check Inclusion and Exclusion Criteria');
define('CIVI_ACTIVITY_ADD_TO_CALENDAR', 'Add to calendar');
define('CIVI_ACTIVITY_TELEPHONE_SITE', 'Telephone Site');
define('CIVI_ACTIVITY_INTERNAL_MONITORING_VISIT', 'Internal Montoring Visit');
define('CIVI_ACTIVITY_TRAINING_AND_INITIATION', 'Training and Initiation');
define('CIVI_ACTIVITY_SURVEY_RESPONSE_RECEIVED', 'Survey Response Received');
define('CIVI_ACTIVITY_PARTICIPANT_LETTER', 'Letter to Participant');
define('CIVI_ACTIVITY_EMAIL_PARTICIPANT', 'Email Participant');
define('CIVI_ACTIVITY_ARCHIVING', 'Archiving');
define('CIVI_ACTIVITY_EXPENSE_REIMBURSEMENT_SUBMITTED', 'Expense Reimbursement Submitted');

define('CIVI_FIELD_SET_ARCHIVING_ACTIVITY', 'Archiving Custom Data');
define('CIVI_FIELD_ARCHIVING_BOX_BARCODE', 'Archiving Box Barcode');

define('LCBRU_DEFAULT_EMAIL_RECIPIENT', 'richard.a.bramley@uhl-tr.nhs.uk');

//Titles
define('CIVI_LCBRU_TITLES', serialize(array('Br', 'Canon', 'Capt', 'Col', 'Dame', 'Dr', 'Fr', 'Hon', 'Lady', 'Lord', 'Lt', 'Major', 'Miss', 'Mr', 'Mrs', 'Ms', 'Msgr', 'Mstr', 'Prof', 'Rabbi', 'Rev', 'Rt Hon', 'Sgt', 'Sir', 'Sr')));
define('CIVI_LCBRU_TITLE_CROSS_REFERENCE', serialize(array('Mr.' => 'Mr', 'Mrs.' => 'Mrs', 'Ms.' => 'Ms', 'Dr.' => 'Dr', 'Professor' => 'Prof')));

//Addresses
define('CIVI_LCBRU_COUNTY_CROSS_REFERENCE', serialize(array('LEICESTERSHIRE' => 'Leicestershire', 'LEICS' => 'Leicestershire', 'RUTLAND' => 'Rutland', 'NORTHAMPTONSHIRE' => 'Northamptonshire', 'NORTHANTS' => 'Northamptonshire', 'NOTTINGHAMSHIRE' => 'Nottinghamshire', 'NOTTS' => 'Nottinghamshire', 'LINCOLNSHIRE' => 'Lincolnshire', 'LINCS' => 'Lincolnshire', 'DERBYSHIRE' => 'Derbyshire', 'DERBYS' => 'Derbyshire')));

//FTP
define('CIVI_DAPS_FTP_CONNECTION_SERVER', 'UHLDWHSQL');
define('CIVI_DAPS_FTP_CONNECTION_USER', 'briccsftp');
define('CIVI_DAPS_FTP_CONNECTION_PASSWORD', 'sk7E9X4G');

// Greetings
define('CIVI_EMAIL_GREETING_PREFIX_SURNAME_ID', '3');
define('CIVI_POSTAL_GREETING_PREFIX_SURNAME_ID', '3');

// Case types, custom group, custom fields
define('CIVI_CASE_TYPE_GENVASC', 'GENVASC');
define('CIVI_FIELD_SET_GENVASC_DATA', 'GENVASC recruitment data');
define('CIVI_FIELD_GENVASC_ID', 'GENVASC ID');
define('CIVI_FIELD_GENVASC_SITE_ID', 'GENVASC recruitment site ICE code');
define('CIVI_FIELD_GENVASC_CONSENT_1', 'GENVASC Consent Q1'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_1_UNDERSTANDS_CONSENT', 'Genvasc Consent Understands Consent');
define('CIVI_FIELD_GENVASC_CONSENT_2', 'GENVASC Consent Q2'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_2_DONATE_SAMPLES', 'Genvasc Consent to Donate Samples');
define('CIVI_FIELD_GENVASC_CONSENT_3', 'GENVASC Consent Q3'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_3_SAMPLES_STORED', 'Genvasc Consent to Sample Storage');
define('CIVI_FIELD_GENVASC_CONSENT_4', 'GENVASC Consent Q4'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_4_INFORMATION_RESEARCH', 'Genvasc Consent to Information used for Research');
define('CIVI_FIELD_GENVASC_CONSENT_5', 'GENVASC Consent Q5'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_5_AUDIT', 'Genvasc Consent Access for Audit');
define('CIVI_FIELD_GENVASC_CONSENT_6', 'GENVASC Consent Q6'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_6_FUTURE_DATA_COLLECTION', 'Genvasc Consent to Future Data Collection');
define('CIVI_FIELD_GENVASC_CONSENT_7', 'GENVASC Consent Q7'); // Deprecated
define('CIVI_FIELD_GENVASC_CONSENT_7_FURTHER_CONTACT', 'Genvasc Consent to Further Contact');
define('CIVI_FIELD_GENVASC_POST_CODE', 'Participant Post Code at time of recruitment to GENVASC');
define('CIVI_FIELD_GENVASC_POST_CODE_NAME', 'GENVASC_Post_Code');
define('CIVI_FIELD_GENVASC_SITE_ID_NAME', 'GENVASC_recruitment_site_ICE_code');

define('CIVI_FIELD_SET_GENVASC_INVOICE_DATA', 'GENVASC invoice data');
define('CIVI_FIELD_GENVASC_INVOICE_YEAR', 'Invoice Year');
define('CIVI_FIELD_GENVASC_INVOICE_QUARTER', 'Invoice Quarter');
define('CIVI_FIELD_GENVASC_INVOICE_REIMBURSED_STATUS', 'Reimbursed Status');
define('CIVI_FIELD_GENVASC_INVOICE_PROCESSED_BY', 'Processed By');
define('CIVI_FIELD_GENVASC_INVOICE_NOTES', 'Notes');
define('CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE', 'Processed Date');

define('DATATABLES_SEARCHABLE_COLUMN_OPTION', serialize(array('bSortable' => TRUE,'bSearchable' => TRUE,)));
define('DATATABLES_LINK_COLUMN_OPTION', serialize(array('bSortable' => FALSE,'bSearchable' => FALSE,)));
define('DATATABLES_OTHER_COLUMN_OPTION', serialize(array('bSortable' => TRUE,'bSearchable' => FALSE,)));
