<?php

/**
 * This class is the central repository for all the custom data and
 * types for all LCBRU modules.
 *
 * This class is not exactly SOLID because it has to be changed when
 * each new type or piece of custom data is created, but it's better than
 * it was.
 *
 * TODO:
 *
  * + Move all of the type creation stuff from all of the modules
  *   into here.
  * + Move the type creation from the lcbru/lib/init.php into here.
  * + Once all of the type creation is in here, make sure that the
  *   methods used by this class from lcbru/lib/utils.php are not
  *   being used by anywhere else and then move them in here and
  *   give them a good refactoring.
*/
class LcbruTypeCreator
{
    /**
     * Constructor.
     *
     * Populate some constants that we're going to use for type creation
     */
    public function __construct() {
        $this->activityTypeOptionGroup = get_civi_option_group(array("name" => "activity_type"));
        $this->caseStatusOptionGroup = get_civi_option_group(array("name" => "case_status"));
        $this->activityStatusOptionGroup = get_civi_option_group(array("name" => "activity_status"));
    }

    /**
     * Call this method to recreate or create for the first time all
     * the types that this class knows about.
     *
     * @return void
     */
    public function recreate() {
        watchdog(__METHOD__, 'started');

        $this->recreateActivityTypes();
        $this->recreateCaseStatuses();
        $this->recreateActivityStatuses();
        $this->recreateContactTypes();
        $this->recreateRelationshipTypes();
        $this->recreateAddressCustomData();
        $this->recreateStudies();

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Call this method to recreate or create for the first time all
     * the types for specific studies.
     *
     * @return void
     */
    public function recreateStudies() {
        watchdog(__METHOD__, 'started');

        if (module_exists('amaze')) {
            $this->recreateCaseAmaze();
        }
        if (module_exists('brave')) {
            $this->recreateCaseBrave();
        }
        if (module_exists('briccs')) {
            $this->recreateCaseBriccs();
        }
        if (module_exists('dream_id_generator')) {
            $this->recreateCaseDream();
        }
        if (module_exists('emmace_4')) {
            $this->recreateCaseEmmace4();
        }
        if (module_exists('fast')) {
            $this->recreateCaseFast();
        }
        if (module_exists('foami')) {
            $this->recreateCaseFoami();
        }
        if (module_exists('genvasc_labels')) {
            $this->recreateCaseGenvascLabels();
        }
        if (module_exists('genvasc_site_management')) {
            $this->recreateCaseGenvascSiteManagement();
        }
        if (module_exists('global_leaders')) {
            $this->recreateCaseGlobalLeadersLabels();
        }
        if (module_exists('graphic2_labels')) {
            $this->recreateCaseGraphic2();
        }
        if (module_exists('hscic_importer')) {
            $this->recreateCaseHscicImporter();
        }
        if (module_exists('indapamide')) {
            $this->recreateCaseIndapamide();
        }
        if (module_exists('interval')) {
            $this->recreateCaseInterval();
        }
        if (module_exists('lenten')) {
            $this->recreateCaseLenten();
        }
        if (module_exists('mermaid')) {
            $this->recreateCaseMermaid();
        }
        if (module_exists('nihr_br_labels')) {
            $this->recreateCaseNihrBrLabels();
        }
        if (module_exists('nihr_br_sub')) {
            $this->recreateCaseNihrBrSub();
        }
        if (module_exists('omics_register')) {
            $this->recreateCaseOmicsRegister();
        }
        if (module_exists('predict')) {
            $this->recreateCasePredict();
        }
        if (module_exists('scad')) {
            $this->recreateCaseScad();
        }
        if (module_exists('tmao')) {
            $this->recreateCaseTmao();
        }

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the LCBRU bespoke case statuses
     *
     * @return void
     */
    private function recreateCaseStatuses() {
        watchdog(__METHOD__, 'started');

        $this->recreateCaseStatus(CIVI_CASE_PENDING, 1);
        $this->recreateCaseStatus(CIVI_CASE_RECRUITED);
        $this->recreateCaseStatus(CIVI_CASE_AVAILABLE);
        $this->recreateCaseStatus(CIVI_CASE_DECLINED);
        $this->recreateCaseStatus(CIVI_CASE_WITHDRAWN);
        $this->recreateCaseStatus(CIVI_CASE_EXCLUDED);
        $this->recreateCaseStatus(CIVI_CASE_FAILED_TO_RESPOND);
        $this->recreateCaseStatus(CIVI_CASE_DUPLICATE);
        /*
        TODO: This seems to create new ones not update - needs fixing
        
        $this->recreateCaseStatus("Resolved", 0, 0);
        $this->recreateCaseStatus("Ongoing", 0, 0);
        $this->recreateCaseStatus("Urgent", 0, 0);
        */

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the LCBRU bespoke activity statuses
     *
     * @return void
     */
    private function recreateActivityStatuses() {
        watchdog(__METHOD__, 'started');

        $this->recreateActivityStatus(CIVI_STATUS_AUTO);
        $this->recreateActivityStatus(CIVI_ACTIVITY_STATUS_BOOKED);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the LCBRU bespoke contact types and it's ascociated custom data
     *
     * @return void
     */
    private function recreateContactTypes() {
        watchdog(__METHOD__, 'started');

        $this->recreateContactTypeCcg();
        $this->recreateContactTypeSurgery();
        $this->recreateContactTypeLocality();

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the CCG custom contact type and it's ascociated custom data
     *
     * @return void
     */
    private function recreateContactTypeCcg() {
        watchdog(__METHOD__, 'started');

        // TODO: Refactor into this class
        create_civi_contact_subtype(
            array(
                "name" => CIVI_SUBTYPE_CCG,
                "parent_id" => "3",
                "description" => "Clinical Commissioning Group"
            ),
            true);

        $customDataGroupId = $this->recreateCustomGroup(CIVI_FIELD_SET_CCG_DATA, 'Organization', str_replace(" ","_",CIVI_SUBTYPE_CCG));
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_CCG_CODE), CIVI_FIELD_CCG_CODE);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_CCG_GENVASC_PI), CIVI_FIELD_CCG_GENVASC_PI);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_CCG_CLRN_SITE_ID), CIVI_FIELD_CCG_CLRN_SITE_ID);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_CCG_CLRN_SITE_ID), CIVI_FIELD_CCG_CLRN_SITE_ID);

        create_civi_relation_type(
              array(
                  "name_a_b" => CIVI_REL_CCG
                , "name_b_a" => CIVI_REL_CCG_FOR
                , "description" => "Linking a GP practice to a CCG."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "contact_type_b" => "Organization"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_CCG)
                , "is_active" => "1"
                )
            , TRUE
        );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the Surgery custom contact type and it's ascociated custom data
     *
     * @return void
     */
    private function recreateContactTypeSurgery() {
        watchdog(__METHOD__, 'started');

        $customDataGroupId = $this->recreateCustomGroup(CIVI_FIELD_SET_PRACTICE_DATA, 'Organisation', str_replace(" ","_",CIVI_SUBTYPE_SURGERY));
        $this->recreateCustomSelectField(
              $customDataGroupId
            , str_replace(" ","_",CIVI_FIELD_PRACTICE_STATUS)
            , CIVI_FIELD_PRACTICE_STATUS
            , array(
                  'A' => 'Active'
                , 'C' => 'Closed'
                , 'P' => 'Proposed'
                , 'D' => 'Dormant'
                )
            );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the Locality custom contact type and it's ascociated custom data
     *
     * @return void
     */
    private function recreateContactTypeLocality() {
        watchdog(__METHOD__, 'started');

        // TODO: Refactor into this class
        create_civi_contact_subtype(
            array(
                "name" => CIVI_SUBTYPE_LOCALITY,
                "parent_id" => "3",
                "description" => "Locality"
            ),
            true);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the custom data associated with addresses
     *
     * @return void
     */
    private function recreateAddressCustomData() {
        watchdog(__METHOD__, 'started');

        $customDataGroupId = $this->recreateCustomGroup(CIVI_FIELD_SET_BRANCH_DATA, 'Address', null);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_ICE_LOCATION), CIVI_FIELD_ICE_LOCATION);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRANCH_CODE), CIVI_FIELD_BRANCH_CODE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the LCBRU bespoke activity types
     *
     * @return void
     */
    private function recreateActivityTypes() {
        watchdog(__METHOD__, 'started');

        $this->recreateCaseActivityType(CIVI_ACTIVITY_RECRUIT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_TAKE_SAMPLES);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_REGISTER);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_FETCH_SAMPLES);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_CHECK_CONSENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_PROCESS_SAMPLES);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_MAKE_AVAILABLE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_WITHDRAW);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_GP_LETTER);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_THANK_YOU_TO_PARTICIPANT_LETTER);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_REM_LETTER);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_CHECK_QUEST);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_INPUT_DATA);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_POST_QUESTIONNAIRE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_POST_CONSENT_FORM);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_RECRUIT_AND_INTERVIEW);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_LOADED_INTO_I2B2);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_RECEIVE_QUESTIONNAIRE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_DISCHARGE_PATIENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_SEND_DATA_TO_LEAD_SITE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_SEND_DATA_TO_SATALLITE_SITE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ATTENDED);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_INVESTIGATION);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ACKNOWLEDGEMENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_QUESTIONNAIRE_STARTED);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_QUESTIONNAIRE_COMPLETED);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_POST_STUDY_INFORMATION_SUPPLIED);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_SUBMITTED_FOR_REIMBURSEMENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_INFORM_GP_PRACTICE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_MRI);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_INTERVENTION);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_RANDOMISE);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ARRANGE_TRANSPORT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ISSUE_PARKING_PERMIT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ARRANGE_APPOINTMENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_SCREENING);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_FAMILY_ENQUIRY);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_APPOINTMENT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ARRANGE_HOSPITALITY);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_CHECK_EXCLUSION_CRITERIA);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_ADD_TO_CALENDAR);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_TRAINING_AND_INITIATION);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_SURVEY_RESPONSE_RECEIVED);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_PARTICIPANT_LETTER);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_EMAIL_PARTICIPANT);
        $this->recreateCaseActivityType(CIVI_ACTIVITY_EXPENSE_REIMBURSEMENT_SUBMITTED);
        
        $archivingActivityTypeId = $this->recreateCaseActivityType(CIVI_ACTIVITY_ARCHIVING);
        $archivingDataGroupId = $this->recreateActivityCustomGroup($archivingActivityTypeId, CIVI_FIELD_SET_ARCHIVING_ACTIVITY);
        $this->recreateCustomStringField($archivingDataGroupId, 'CIVI_FIELD_ARCHIVING_BOX_BARCODE', CIVI_FIELD_ARCHIVING_BOX_BARCODE, True);

        $this->recreateContactActivityType(CIVI_ACTIVITY_INV_LETTER);
        $this->recreateContactActivityType(CIVI_ACTIVITY_NOTES_REQUESTED);
        $this->recreateContactActivityType(CIVI_ACTIVITY_TELEPHONE_PARTICIPANT);
        $this->recreateContactActivityType(CIVI_ACTIVITY_TELEPHONE_SITE);
        $this->recreateContactActivityType(CIVI_ACTIVITY_INTERNAL_MONITORING_VISIT);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the LCBRU bespoke relationship types
     *
     * @return void
     */
    private function recreateRelationshipTypes() {
        watchdog(__METHOD__, 'started');

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => CIVI_REL_RECRUITING_SITE
                , "name_b_a" => CIVI_REL_RECRUITING_SITE_FOR
                , "description" => "Linking a contact to the site that recruited them into a study."
                , "contact_type_a" => "Individual"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT)
                , "contact_type_b" => "Organization"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => CIVI_REL_SURGEON
                , "name_b_a" => CIVI_REL_SURGEON_FOR
                , "description" => "Linking a patient to a surgeon who operated on them."
                , "contact_type_a" => "Individual"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_CONTACT)
                , "contact_type_b" => "Individual"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => 'CIVI_REL_GENVASC_CONTACT'
                , "name_b_a" => 'CIVI_REL_GENVASC_CONTACT'
                , "label_a_b" => CIVI_REL_GENVASC_CONTACT
                , "label_b_a" => CIVI_REL_GENVASC_CONTACT_FOR
                , "description" => "Linking a practice to their GENVASC contact."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "contact_type_b" => "Individual"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => CIVI_REL_PRACTICE_MANAGER
                , "name_b_a" => CIVI_REL_PRACTICE_MANAGER_FOR
                , "description" => "Linking a practice to their Practice Manager."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "contact_type_b" => "Individual"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => CIVI_REL_LOCALITY_MANAGER
                , "name_b_a" => CIVI_REL_LOCALITY_MANAGER_FOR
                , "description" => "Linking a locality to their Locality Manager."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_LOCALITY)
                , "contact_type_b" => "Individual"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => CIVI_REL_LOCALITY
                , "name_b_a" => CIVI_REL_LOCALITY_FOR
                , "description" => "Linking a GP Practice to their Locality."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "contact_type_b" => "Organization"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_LOCALITY)
                , "is_active" => "1"
            )
            , TRUE
        );

        // Refactor this into this class
        create_civi_relation_type(
            array(
                  "name_a_b" => 'CIVI_REL_CRN_STUDY_CONTACT'
                , "name_b_a" => 'CIVI_REL_CRN_STUDY_CONTACT_FOR'
                , "label_a_b" => CIVI_REL_CRN_STUDY_CONTACT
                , "label_b_a" => CIVI_REL_CRN_STUDY_CONTACT_FOR
                , "description" => "Linking a practice to their CRN contact."
                , "contact_type_a" => "Organization"
                , "contact_sub_type_a" => str_replace(" ","_",CIVI_SUBTYPE_SURGERY)
                , "contact_type_b" => "Individual"
                , "contact_sub_type_b" => str_replace(" ","_",CIVI_SUBTYPE_HW)
                , "is_active" => "1"
            )
            , TRUE
        );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the AMAZE study
     *
     * @return void
     */
    private function recreateCaseAmaze() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_AMAZE);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_AMAZE);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_AMAZE_ID), CIVI_FIELD_AMAZE_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the BRAVE study
     *
     * @return void
     */
    private function recreateCaseBrave() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_BRAVE);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_BRAVE);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_BRAVE_ID', CIVI_FIELD_BRAVE_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_BRAVE_BRICCS_ID', CIVI_FIELD_BRAVE_BRICCS_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_BRAVE_FAMILY_ID', CIVI_FIELD_BRAVE_FAMILY_ID);

        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_BRAVE_SOURCE_STUDY'
            , CIVI_FIELD_BRAVE_SOURCE_STUDY
            , array(
                  'ADULT_CARDIOLOGY' => 'Adult Cardiology'
                , 'GUCH' => 'Grow Up Congenital Heart Disease'
                , 'Radiology' => 'Radiology'
              )
            );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the BRICCS study
     *
     * @return void
     */
    private function recreateCaseBriccs() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_BRICCS);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_BRICCS_DATA);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_ID), CIVI_FIELD_BRICCS_ID);
        $this->recreateCustomDateField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_INTERVIEW_DATETIME), CIVI_FIELD_BRICCS_INTERVIEW_DATETIME);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_INTERVIEWER), CIVI_FIELD_BRICCS_INTERVIEWER);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , str_replace(" ","_",CIVI_FIELD_BRICCS_INTEVIEW_STATUS)
            , CIVI_FIELD_BRICCS_INTEVIEW_STATUS
            , array(
                  'IN_PROGRESS' => 'In Progress'
                , 'CLOSED' => 'Closed'
                , 'CANCELLED' => 'Cancelled'
                , 'COMPLETED' => 'Completed'
                )
            );
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_CONSENT), CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_CONSENT);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_BLOOD_AND_URINE), CIVI_FIELD_BRICCS_CONSENT_BLOOD_AND_URINE);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_BRICCS_DATABASE), CIVI_FIELD_BRICCS_CONSENT_BRICCS_DATABASE);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_FURTHER_CONTACT), CIVI_FIELD_BRICCS_CONSENT_FURTHER_CONTACT);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_WITHDRAWAL), CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_WITHDRAWAL);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , str_replace(" ","_",CIVI_FIELD_BRICCS_RECRUITMENT_TYPE)
            , CIVI_FIELD_BRICCS_RECRUITMENT_TYPE
            , array(
                  'Inpatient' => 'Inpatient'
                , 'Outpatient' => 'Outpatient'
                , "Healthy_control" => "Healthy Control"
                , 'DK' => 'Don\'t Know'
                , 'GeneFast AS study' => 'GeneFast AS study'
                , 'BRAVE' => 'BRAVE'
                , 'BRICCS CT' => 'BRICCS CT'
                )
            );
        $this->recreateCustomCheckBoxesField(
              $customDataGroupId
            , 'CIVI_FIELD_BRICCS_INVITATION_FOR'
            , CIVI_FIELD_BRICCS_INVITATION_FOR
            , array(
                  'BRICCS CT' => 'BRICCS CT',
                )
            );

        $cfh = new CustomFieldHelper();

        $cfh->setFieldInactive(str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_CONSENT));
        $cfh->setFieldInactive(str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_BLOOD_AND_URINE));
        $cfh->setFieldInactive(str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_BRICCS_DATABASE));
        $cfh->setFieldInactive(str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_FURTHER_CONTACT));
        $cfh->setFieldInactive(str_replace(" ","_",CIVI_FIELD_BRICCS_CONSENT_UNDERSTANDS_WITHDRAWAL));

        watchdog(__METHOD__, 'completed');
    }


    /**
     * Create the case and custom data types for the DREAM study
     *
     * @return void
     */
    private function recreateCaseDream() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_DREAM);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the GENVASC study
     *
     * @return void
     */
    private function recreateCaseGenvascLabels() {
        watchdog(__METHOD__, 'started');
        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_GENVASC);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_GENVASC_DATA);
        $this->recreateCustomStringField($customDataGroupId, CIVI_FIELD_GENVASC_POST_CODE_NAME, CIVI_FIELD_GENVASC_POST_CODE);

        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_SITE_ID), CIVI_FIELD_GENVASC_SITE_ID);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_ID), CIVI_FIELD_GENVASC_ID);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_1), CIVI_FIELD_GENVASC_CONSENT_1);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_2), CIVI_FIELD_GENVASC_CONSENT_2);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_3), CIVI_FIELD_GENVASC_CONSENT_3);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_4), CIVI_FIELD_GENVASC_CONSENT_4);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_5), CIVI_FIELD_GENVASC_CONSENT_5);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_6), CIVI_FIELD_GENVASC_CONSENT_6);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GENVASC_CONSENT_7), CIVI_FIELD_GENVASC_CONSENT_7);

        $invoiceCustomDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_GENVASC_INVOICE_DATA);
        $this->recreateCustomSelectField(
              $invoiceCustomDataGroupId
            , 'CIVI_FIELD_GENVASC_INVOICE_YEAR'
            , CIVI_FIELD_GENVASC_INVOICE_YEAR
            , array(
                  '2012-13' => '2012-13'
                , '2013-14' => '2013-14'
                , '2014-15' => '2014-15'
                , '2015-16' => '2015-16'
                , '2016-17' => '2016-17'
                , '2017-18' => '2017-18'
                , '2018-19' => '2018-19'
                , '2019-20' => '2019-20'
                , '2020-21' => '2020-21'
                , '2021-22' => '2021-22'
              ), False, False
            );
        $this->recreateCustomSelectField(
              $invoiceCustomDataGroupId
            , 'CIVI_FIELD_GENVASC_INVOICE_QUARTER'
            , CIVI_FIELD_GENVASC_INVOICE_QUARTER
            , array(
                  'Q1' => 'Q1'
                , 'Q2' => 'Q2'
                , 'Q3' => 'Q3'
                , 'Q4' => 'Q4'
              ), False, False
            );
        $this->recreateCustomSelectField(
              $invoiceCustomDataGroupId
            , 'CIVI_FIELD_GENVASC_INVOICE_REIMBURSED_STATUS'
            , CIVI_FIELD_GENVASC_INVOICE_REIMBURSED_STATUS
            , array(
                  'Yes' => 'Yes'
                , 'No (Paticipant Excluded)' => 'No (Paticipant Excluded)'
              ), False, False
            );

        $this->recreateCustomStringField($invoiceCustomDataGroupId, 'CIVI_FIELD_GENVASC_INVOICE_PROCESSED_BY', CIVI_FIELD_GENVASC_INVOICE_PROCESSED_BY, False, False);
        $this->recreateCustomNotesField($invoiceCustomDataGroupId, 'CIVI_FIELD_GENVASC_INVOICE_NOTES', CIVI_FIELD_GENVASC_INVOICE_NOTES, False, False);
        $this->recreateCustomDateField($invoiceCustomDataGroupId, 'CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE', CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE, False, False);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the GENVASC study
     *
     * @return void
     */
    private function recreateCaseGenvascSiteManagement() {
        watchdog(__METHOD__, 'started');
        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_NAME_GENVASC_SITE_MANAGEMENT, CIVI_CASE_TYPE_TITLE_GENVASC_SITE_MANAGEMENT);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_GENVASC_SITE_MANAGEMENT);

        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_GENVASC_SITE_RSI', CIVI_FIELD_GENVASC_SITE_RSI);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_GENVASC_SITE_IT_SYSTEM'
            , CIVI_FIELD_GENVASC_SITE_IT_SYSTEM
            , array(
                  'EMIS' => 'EMIS'
                , 'SystemOne' => 'SystemOne'
              )
            );


        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the Global Leaders study
     *
     * @return void
     */
    private function recreateCaseGlobalLeadersLabels() {
        watchdog(__METHOD__, 'started');
        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_GLOBAL_LEADERS);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_GLOBAL_LEADERS);

        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_GLOBAL_LEADERS_ID), CIVI_FIELD_GLOBAL_LEADERS_ID);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , str_replace(" ","_",CIVI_FIELD_GLOBAL_LEADERS_TREATMENT)
            , CIVI_FIELD_GLOBAL_LEADERS_TREATMENT
            , array(
                  'CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_EXPERIMENTAL' => CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_EXPERIMENTAL
                , 'CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_UNSTABLE' => CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_UNSTABLE
                , 'CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_STABLE' => CIVI_FIELD_GLOBAL_LEADERS_TREATMENT_STANDARD_STABLE
                )
            );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the EMMACE 4 study
     *
     * @return void
     */
    private function recreateCaseEmmace4() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_EMMACE_4);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_EMMACE_4_DATA);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_EMMACE_4_ID), CIVI_FIELD_EMMACE_4_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the FAST study
     *
     * @return void
     */
    private function recreateCaseFast() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_FAST);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_FAST);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_FAST_ID', CIVI_FIELD_FAST_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the FOAMI study
     *
     * @return void
     */
    private function recreateCaseFoami() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_FOAMI);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_FOAMI);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_FOAMI_ID', CIVI_FIELD_FOAMI_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the GRAPHIC2 study
     *
     * @return void
     */
    private function recreateCaseGraphic2() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_GRAPHIC2);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_GRAPHIC2);
        $this->recreateCustomStringField($customDataGroupId, LAB_ID_NAME, LAB_ID_LABEL, False, True);
        $this->recreateCustomStringField($customDataGroupId, GRAPHIC_ID_NAME, GRAPHIC_ID_LABEL, False, True);
        $this->recreateCustomStringField($customDataGroupId, FAMILY_ID_NAME, FAMILY_ID_LABEL, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, Consent_for_further_studies_NAME, Consent_for_further_studies_LABEL, True, False);
        $this->recreateCustomBooleanField($customDataGroupId, G1_Blood_Consent_NAME, G1_Blood_Consent_LABEL, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, Pre_consent_to_GRAPHIC_2_NAME, Pre_consent_to_GRAPHIC_2_LABEL, False, True);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the HscicImporter module
     *
     * @return void
     */
    private function recreateCaseHscicImporter() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_EMMACE_4);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_EMMACE_4_DATA);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_EMMACE_4_ID), CIVI_FIELD_EMMACE_4_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the Indapamide module
     *
     * @return void
     */
    private function recreateCaseIndapamide() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_INDAPAMIDE);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_INDAPAMIDE);
        $this->recreateCustomStringField($customDataGroupId,'CIVI_FIELD_INDAPAMIDE_ID', CIVI_FIELD_INDAPAMIDE_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the Interval module
     *
     * @return void
     */
    private function recreateCaseInterval() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_INTERVAL);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_INTERVAL_DATA);
        $this->recreateCustomStringField($customDataGroupId,'CIVI_FIELD_INTERVAL_ID', CIVI_FIELD_INTERVAL_ID);
        $this->recreateCustomDateField($customDataGroupId, 'CIVI_FIELD_INTERVAL_CONSENT_DATE', CIVI_FIELD_INTERVAL_CONSENT_DATE);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_INTERVAL_CONSENT_VERSION'
            , CIVI_FIELD_INTERVAL_CONSENT_VERSION
            , array(
                  '' => ''
                , 'Interval - V1 15/05/2015' => 'Interval - V1 15/05/2015'
                )
            );
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_INTERVAL_CONSENT_LEAFLET'
            , CIVI_FIELD_INTERVAL_CONSENT_LEAFLET
            , array(
                  '' => ''
                , 'Interval - V1 15/05/2015' => 'Interval - V1 15/05/2015'
                )
            );

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the LENTEN module
     *
     * @return void
     */
    private function recreateCaseLenten() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASETYPE_LENTEN);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_CUSTOMGROUP_LENTEN);
        $this->recreateCustomStringField($customDataGroupId,'CIVI_FIELD_LENTEN_ID', CIVI_FIELD_LENTEN_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the Mermaid study
     *
     * @return void
     */
    private function recreateCaseMermaid() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_MERMAID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the NIHR Bioresource study
     *
     * @return void
     */
    private function recreateCaseNihrBrLabels() {
        watchdog(__METHOD__, 'started');

        // Refactor this into the class
        create_civi_group(array("title" => CIVI_GROUP_BIORESOURCE), TRUE);

        $caseTypeId = recreateCaseType(CIVI_CASETYPE_BIORESOURCE);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_CUSTOMGROUP_BIORESOURCE);
        $this->recreateCustomStringField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORESOURCE_ID), CIVI_FIELD_BIORESOURCE_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_BIORESOURCE_LEGACY_ID', CIVI_FIELD_BIORESOURCE_LEGACY_ID);
        $this->recreateCustomDateField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_DATE), CIVI_FIELD_BIORES_CONSENT_DATE, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_1), CIVI_FIELD_BIORES_CONSENT_1, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_2), CIVI_FIELD_BIORES_CONSENT_2, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_3), CIVI_FIELD_BIORES_CONSENT_3, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_4), CIVI_FIELD_BIORES_CONSENT_4, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_5), CIVI_FIELD_BIORES_CONSENT_5, False, True);
        $this->recreateCustomBooleanField($customDataGroupId, str_replace(" ","_",CIVI_FIELD_BIORES_CONSENT_6), CIVI_FIELD_BIORES_CONSENT_6, False, True);
        $withdrawalCustomDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_CUSTOMGROUP_BIORES_WITHDRAW);
        $this->recreateCustomSelectField(
              $withdrawalCustomDataGroupId
            , str_replace(" ","_",CIVI_FIELD_BIORES_WITHDRAW)
            , CIVI_FIELD_BIORES_WITHDRAW
            , array(
                  '0' => 'Not Withdrawn'
                , 'A' => 'Withdrawn - Can use samples & data'
                , "B" => "Withdrawn - Destroy samples & data"
                )
            );

        $cfh = new CustomFieldHelper();

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the NIHR Bioresource study
     *
     * @return void
     */
    private function recreateCaseNihrBrSub() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASETYPE_BIORESOURCE_SUB_STUDY);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_CUSTOMGROUP_BIORESOURCE_SUB_STUDY);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_BIORESOURCE_SUB_STUDY_NAME'
            , CIVI_FIELD_BIORESOURCE_SUB_STUDY_NAME
            , array(
                  'NBR005' => 'NBR005: Oxford - Neural and Genetic Associations in Cognitive Decline',
                  'National Bioresource' => 'National Bioresource',
                )
            , true
            );

        $cfh = new CustomFieldHelper();

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the Omics Register study
     *
     * @return void
     */
    private function recreateCaseOmicsRegister() {
        watchdog(__METHOD__, 'started');

        // Refactor this into the class
        create_civi_group(array("title" => CIVI_GROUP_OMICS_REGISTER), TRUE);

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_OMICS_REGISTER);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_OMICS_REGISTER);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_OMICS_REGISTER_ID', CIVI_FIELD_OMICS_REGISTER_ID);
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_OMICS_REGISTER_OMICS_TYPE'
            , CIVI_FIELD_OMICS_REGISTER_OMICS_TYPE
            , array(
                  'BRICCS Exome (Sanger; 2013)' => 'BRICCS Exome (Sanger; 2013)'
                , 'Affymetrix Array (2016)' => 'Affymetrix Array (2016)'
                , 'Metabolon (2016)' => 'Metabolon (2016)'
                , 'National Phenome Centre (2014)' => 'National Phenome Centre (2014)'
                )
            );
        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_OMICS_REGISTER_SAMPLE_SOURCE_STUDY'
            , CIVI_FIELD_OMICS_REGISTER_SAMPLE_SOURCE_STUDY
            , array(
                  'BRAVE' => 'BRAVE'
                , 'BRICCS' => 'BRICCS'
                , 'GENVASC' => 'GENVASC'
                , 'GRAPHIC2' => 'GRAPHIC2'
                , 'SCAD' => 'SCAD'
                )
            );
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_OMICS_REGISTER_FAILED_QC', CIVI_FIELD_OMICS_REGISTER_FAILED_QC);
        $this->recreateCustomDateField($customDataGroupId, 'CIVI_FIELD_OMICS_REGISTER_DATE_DATA_RECEIVED', CIVI_FIELD_OMICS_REGISTER_DATE_DATA_RECEIVED);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the PREDICT module
     *
     * @return void
     */
    private function recreateCasePredict() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASETYPE_PREDICT);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_CUSTOMGROUP_PREDICT);
        $this->recreateCustomStringField($customDataGroupId,'CIVI_FIELD_PREDICT_ID', CIVI_FIELD_PREDICT_ID);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the SCAD study
     *
     * @return void
     */
    private function recreateCaseScad() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_SCAD);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_GROUP_SCAD);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_ID', CIVI_FIELD_SCAD_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_BRICCS_ID', CIVI_FIELD_SCAD_BRICCS_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_REG_ID', CIVI_FIELD_SCAD_REG_ID);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_SURVEY_REFERENCE', CIVI_FIELD_SCAD_SURVEY_REFERENCE);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_SURVEY_REFERENCE_2', CIVI_FIELD_SCAD_SURVEY_REFERENCE_2);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_SCAD_VISIT_ID', CIVI_FIELD_SCAD_VISIT_ID);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_READ_INFORMATION', CIVI_FIELD_SCAD_CONSENT_READ_INFORMATION);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_WITHDRAWAL', CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_WITHDRAWAL);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_PROVIDE_INFORMATION', CIVI_FIELD_SCAD_CONSENT_PROVIDE_INFORMATION);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_CONTACT_BY_RESEARCH_TEAM', CIVI_FIELD_SCAD_CONSENT_CONTACT_BY_RESEARCH_TEAM);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_SAMPLE_STORAGE', CIVI_FIELD_SCAD_CONSENT_SAMPLE_STORAGE);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_NO_FINANCIAL_BENEFIT', CIVI_FIELD_SCAD_CONSENT_NO_FINANCIAL_BENEFIT);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_CONTACT_GP', CIVI_FIELD_SCAD_CONSENT_CONTACT_GP);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_DNA_SEQUENCING', CIVI_FIELD_SCAD_CONSENT_DNA_SEQUENCING);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_SKIN_BIOPSY', CIVI_FIELD_SCAD_CONSENT_SKIN_BIOPSY);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_HOW_TO_CONTACT_RESEARCH_TEAM', CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_HOW_TO_CONTACT_RESEARCH_TEAM);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_SHARE_WITH_MAYO_CLINIC', CIVI_FIELD_SCAD_CONSENT_SHARE_WITH_MAYO_CLINIC);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_ACCESS_TO_MEDICAL_RECORD', CIVI_FIELD_SCAD_CONSENT_ACCESS_TO_MEDICAL_RECORD);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_CONTACT_FOR_RELATED_STUDIES', CIVI_FIELD_SCAD_CONSENT_CONTACT_FOR_RELATED_STUDIES);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_RECEIVE_RESEARCH_SUMMARY', CIVI_FIELD_SCAD_CONSENT_RECEIVE_RESEARCH_SUMMARY);
        $this->recreateCustomDateField($customDataGroupId, 'CIVI_FIELD_SCAD_CONSENT_DATE', CIVI_FIELD_SCAD_CONSENT_DATE);

        $this->recreateCustomSelectField(
              $customDataGroupId
            , 'CIVI_FIELD_SCAD_RECRUITMENT_TYPE'
            , CIVI_FIELD_SCAD_RECRUITMENT_TYPE
            , array(
                  'SCAD' => 'SCAD'
                , 'Healthy Volunteer' => 'Healthy Volunteer'
              )
            );

        $cfh = new CustomFieldHelper();

        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_READ_INFORMATION');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_WITHDRAWAL');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_PROVIDE_INFORMATION');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_CONTACT_BY_RESEARCH_TEAM');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_SAMPLE_STORAGE');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_NO_FINANCIAL_BENEFIT');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_CONTACT_GP');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_DNA_SEQUENCING');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_SKIN_BIOPSY');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_UNDERSTANDS_HOW_TO_CONTACT_RESEARCH_TEAM');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_SHARE_WITH_MAYO_CLINIC');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_ACCESS_TO_MEDICAL_RECORD');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_CONTACT_FOR_RELATED_STUDIES');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_RECEIVE_RESEARCH_SUMMARY');
        $cfh->setFieldInactive('CIVI_FIELD_SCAD_CONSENT_DATE');
        
        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create the case and custom data types for the TMAO study
     *
     * @return void
     */
    private function recreateCaseTmao() {
        watchdog(__METHOD__, 'started');

        $caseTypeId = recreateCaseType(CIVI_CASE_TYPE_TMAO);
        $customDataGroupId = $this->recreateCaseCustomGroup($caseTypeId, CIVI_FIELD_SET_TMAO);
        $this->recreateCustomStringField($customDataGroupId, 'CIVI_FIELD_TMAO_ID', CIVI_FIELD_TMAO_ID);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_READ_INFORMATION_SHEET', CIVI_FIELD_TMAO_CONSENT_READ_INFORMATION_SHEET);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_UNDERSTANDS_WITHDRAWAL', CIVI_FIELD_TMAO_CONSENT_UNDERSTANDS_WITHDRAWAL);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_ACCESS_NOTES', CIVI_FIELD_TMAO_CONSENT_ACCESS_NOTES);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_GP_INFORMED', CIVI_FIELD_TMAO_CONSENT_GP_INFORMED);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_ENROLMENT', CIVI_FIELD_TMAO_CONSENT_ENROLMENT);
        $this->recreateCustomBooleanField($customDataGroupId, 'CIVI_FIELD_TMAO_CONSENT_STORE_BLOOD', CIVI_FIELD_TMAO_CONSENT_STORE_BLOOD);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a case activities.
     *
     * @param string $activityName the name of the case
     * @throws InvalidArgumentException if the activityName is null or empty.
     */
    private function recreateCaseActivityType($activityName) {
        watchdog(__METHOD__, 'started');

        if (!isset($activityName) || trim($activityName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without an $activityName.');
        }

        // TODO: Refactor this method into this class
        $result = create_civi_option_value($this->activityTypeOptionGroup, array("name" => $activityName, "component_id" => "7"), TRUE);

        watchdog(__METHOD__, 'completed');

        return $result['id'];
    }

    /**
     * Create / recreate a contact activities.
     *
     * @param string $activityName the name of the case
     * @throws InvalidArgumentException if the activityName is null or empty.
     */
    private function recreateContactActivityType($activityName) {
        watchdog(__METHOD__, 'started');

        if (!isset($activityName) || trim($activityName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without an $activityName.');
        }

        // TODO: Refactor this method into this class
        create_civi_option_value($this->activityTypeOptionGroup, array("name" => $activityName), TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a Case Status.
     *
     * @param string $caseStatusName the name of the case
     * @throws InvalidArgumentException if the caseStatusName is null or empty.
     */
    private function recreateCaseStatus($caseStatusName, $isDefault = 0, $isActive=1) {
        watchdog(__METHOD__, 'started');

        if (!isset($caseStatusName) || trim($caseStatusName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without an $caseStatusName.');
        }

        // TODO: Refactor this method into this class
        create_civi_option_value($this->caseStatusOptionGroup, array(
          "name" => $caseStatusName,
          "is_default" => $isDefault,
          "is_active" => $isActive,
          ), TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a Activity Status.
     *
     * @param string $activityStatusName the name of the activity
     * @throws InvalidArgumentException if the activityStatusName is null or empty.
     */
    private function recreateActivityStatus($activityStatusName, $isDefault = 0, $isActive=1) {
        watchdog(__METHOD__, 'started');

        if (!isset($activityStatusName) || trim($activityStatusName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without an $activityStatusName.');
        }

        // TODO: Refactor this method into this class
        create_civi_option_value($this->activityStatusOptionGroup, array(
          "name" => $activityStatusName,
          "is_default" => $isDefault,
          "is_active" => $isActive,
          ), TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom group for a activity Type.
     *
     * @param integer $activityTypeId the Id for the Case Type
     * @param string $customDataGroupName the name of the custom data group
     * @return integer custom data group id
     * @throws InvalidArgumentException if the caseTypeId is null or not a number.
     */
    private function recreateActivityCustomGroup($activityTypeId, $customDataGroupName) {
        watchdog(__METHOD__, 'started');

        if (!isset($activityTypeId) || !( !is_int($activityTypeId) ? (ctype_digit($activityTypeId)) : true )) {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called with a none numeric value for $activityTypeId.');
        }

        $helper = new OptionValueHelper(OptionValueHelper::ACTIVITY_TYPE);
        $activityType = $helper->getFromId($activityTypeId);

        return $this->recreateCustomGroup($customDataGroupName, 'Activity', $activityType['value']);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom group for a case type.
     *
     * @param integer $caseTypeId the Id for the Case Type
     * @param string $customDataGroupName the name of the custom data group
     * @return integer custom data group id
     * @throws InvalidArgumentException if the caseTypeId is null or not a number.
     */
    private function recreateCaseCustomGroup($caseTypeId, $customDataGroupName) {
        watchdog(__METHOD__, 'started');

        if (!isset($caseTypeId) || !( !is_int($caseTypeId) ? (ctype_digit($caseTypeId)) : true )) {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called with a none numeric value for $caseTypeId.');
        }

        return $this->recreateCustomGroup($customDataGroupName, 'Case', $caseTypeId);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom group for a case type.
     *
     * @param string $customDataGroupName the name of the custom data group
     * @param string $entityTypeName the name entity type that the custom data
     *               is attached to.  For example, 'case' or a contact sub-type.
     * @param integer $entitySubType the sub-type of entity or caseTypeID or null if not applicable
     * @return integer custom data group id
     * @throws InvalidArgumentException if the entityTypeName is null or empty.
     * @throws InvalidArgumentException if the customDataGroupName is null or empty.
     */
    private function recreateCustomGroup($customDataGroupName, $entityTypeName, $entitySubType) {
        watchdog(__METHOD__, 'started');

        if (!isset($entityTypeName) || trim($entityTypeName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $entityTypeName.');
        }
        if (!isset($customDataGroupName) || trim($customDataGroupName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $customDataGroupName.');
        }

        $extends = array($entityTypeName);

        if ($entitySubType) {
            $extends[] = array($entitySubType);
        }

        // TODO: Refactor this method into this class
        return create_civi_custom_group(
            array(
                  "title" => $customDataGroupName
                , "extends" => $extends
                , "is_active" => 1
            ), TRUE
        )['id'];

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom Date field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @throws InvalidArgumentException if the customGroupId is null or not a number.
     */
    private function recreateCustomDateField($customGroupId, $customDataName, $customDataLabel, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "Date", "Select Date");
        $params["is_search_range"] = 1;
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom String field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @throws InvalidArgumentException if the customGroupId is null or not a number.
     */
    private function recreateCustomStringField($customGroupId, $customDataName, $customDataLabel, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "String", "Text");
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        // TODO: Refactor this method into this class
        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom Notes field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @throws InvalidArgumentException if the customGroupId is null or not a number.
     */
    private function recreateCustomNotesField($customGroupId, $customDataName, $customDataLabel, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "Note", "TextArea");
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        // TODO: Refactor this method into this class
        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom Boolean field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @throws InvalidArgumentException if the customGroupId is null or not a number.
     */
    private function recreateCustomBooleanField($customGroupId, $customDataName, $customDataLabel, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "Boolean", "Radio");
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        // TODO: Refactor this method into this class
        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom Select field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @param array $items key value pairs for the select items
     * @throws InvalidArgumentException if customGroupId is null or not a number.
     * @throws InvalidArgumentException if items is null.
     */
    private function recreateCustomSelectField($customGroupId, $customDataName, $customDataLabel, array $items, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertArray_NotEmpty('$items', $items);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "String", "Select");
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        $params['option_type'] = 1;
        $params['option_label'] = array();
        $params['option_value'] = array();
        $params['option_weight'] = array();
        $params['option_status'] = array();

        $itemOrder = 1;
        foreach ($items as $key => $value) {
            $params['option_label'][$key] = $value;
            $params['option_value'][$key] = $key;
            $params['option_weight'][$key] = $itemOrder++;
            $params['option_status'][$key] = TRUE;
        }

        // TODO: Refactor this method into this class
        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Create / recreate a custom CheckBoxes field.
     *
     * @param array $customGroupId the ID of the custom group to which to add the field
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @param array $items key value pairs for the select items
     * @throws InvalidArgumentException if customGroupId is null or not a number.
     * @throws InvalidArgumentException if items is null.
     */
    private function recreateCustomCheckBoxesField($customGroupId, $customDataName, $customDataLabel, array $items, $isRequired = False, $isReadonly = False) {
        watchdog(__METHOD__, 'started');

        Guard::AssertInteger('$customGroupId', $customGroupId);
        Guard::AssertString_NotEmpty('$customDataName', $customDataName);
        Guard::AssertString_NotEmpty('$customDataLabel', $customDataLabel);
        Guard::AssertArray_NotEmpty('$items', $items);
        Guard::AssertBoolean('$isRequired', $isRequired);
        Guard::AssertBoolean('$isReadonly', $isReadonly);

        // This is a bit naughty because I'm using knowledge of what the
        // Utils method expects, but it makes it easier to do my guard clauses
        // and also maintain the same function prototype when I refactor the
        // utils method into this class.
        $customGroupParams = array('id' => $customGroupId);

        $params = $this->getStandardCustomFieldParams($customDataName, $customDataLabel, "String", "CheckBox");
        $params["is_required"] = $isRequired ? 1 : 0;
        $params["is_view"] = $isReadonly ? 1 : 0;

        $params['option_type'] = 1;
        $params['option_label'] = array();
        $params['option_value'] = array();
        $params['option_weight'] = array();
        $params['option_status'] = array();

        $itemOrder = 1;
        foreach ($items as $key => $value) {
            $params['option_label'][$key] = $value;
            $params['option_value'][$key] = $key;
            $params['option_weight'][$key] = $itemOrder++;
            $params['option_status'][$key] = TRUE;
        }

        // TODO: Refactor this method into this class
        create_civi_custom_field($customGroupParams, $params, TRUE);

        watchdog(__METHOD__, 'completed');
    }

    /**
     * Get standard custom field parameters.
     *
     * @param string $customDataName the name of the custom data field
     * @param string $customDataLabel the label of the custom data field
     * @param string $dataType the custom data's type
     * @param string $htmlType the type of control to use for the custom data
     * @throws InvalidArgumentException if the customDataName is null or empty.
     * @throws InvalidArgumentException if the customDataLabel is null or empty.
     * @throws InvalidArgumentException if the dataType is null or empty.
     * @throws InvalidArgumentException if the htmlType is null or empty.
     */
    private function getStandardCustomFieldParams($customDataName, $customDataLabel, $dataType, $htmlType) {
        watchdog(__METHOD__, 'started');

        if (!isset($customDataName) || trim($customDataName)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $customDataName.');
        }
        if (!isset($customDataLabel) || trim($customDataLabel)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $customDataLabel.');
        }
        if (!isset($dataType) || trim($dataType)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $dataType.');
        }
        if (!isset($htmlType) || trim($htmlType)==='') {
            throw new InvalidArgumentException(__METHOD____ . ' cannot be called without a value for $htmlType.');
        }

        return array(
              "weight"=> 1
            , "name" => $customDataName
            , "label" => $customDataLabel
            , "data_type" => $dataType
            , "html_type" => $htmlType
            , "is_active"=> 1
            , "is_searchable"=> 1
        );

        watchdog(__METHOD__, 'completed');
    }
}
