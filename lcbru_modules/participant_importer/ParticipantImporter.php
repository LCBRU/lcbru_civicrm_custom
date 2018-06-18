<?php

/**
 * This class provides utility functions for importing participants into CiviCRM.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $pimp = new ParticipantImporter();
 *
 *   $pimp->importFromCsv($csvFile)
 *
 * }
 * </code>
 *
 * Things to note are:
 *
*/

class ParticipantImporter
{
    /**
     * Constructor.
     * @param integer $caseTypeId the case type to be created
     *
     * @throws Exception if $caseTypeId is empty.
     */
     public function __construct(
        $caseTypeId,
        $createNewParticipants,
        $createNewCases,
        $ignoredCaseStatuses=array(),
        $ignoreIfParticipantMissing=False
    ) {
        #Guard::AssertInteger('$caseTypeId', $caseTypeId);
        Guard::AssertBoolean('$createNewParticipants', $createNewParticipants);
        Guard::AssertBoolean('$createNewCases', $createNewCases);
        Guard::AssertArrayOfIntegers('$ignoredCaseStatuses', $ignoredCaseStatuses);
        Guard::AssertBoolean('$ignoreIfParticipantMissing', $ignoreIfParticipantMissing);
        Guard::AssertFalse('$ignoreIfParticipantMissing && $createNewParticipants', $ignoreIfParticipantMissing && $createNewParticipants);

        $this->caseHelper = new CaseHelper();
        $this->customFieldHelper = new CustomFieldHelper();
        $this->contactHelper = new ContactHelper();

        $this->caseTypeId = $caseTypeId;
        $this->caseTypeName = $this->caseHelper->getCaseTypeSelectOptions()[$this->caseTypeId];

        $this->locationTypeHelper = new LocationTypeHelper();
        $this->stateProvinceHelper = new StateProvinceHelper();
        $this->phoneTypeHelper = new OptionValueHelper(OptionValueHelper::PHONE_TYPE);
        $this->preferredCommunicationMethodHelper = new OptionValueHelper(OptionValueHelper::PREFERRED_COMMUNICATION_METHOD);
        $this->individualPrefixHelper = new OptionValueHelper(OptionValueHelper::INDIVIDUAL_PREFIX);
        $this->genderHelper = new OptionValueHelper(OptionValueHelper::GENDER);
        $this->caseStatusHelper = new OptionValueHelper(OptionValueHelper::CASE_STATUS);
        $this->nhsNumberFieldName = lcbru_get_custom_field_id_name(CIVI_FIELD_NHS_NUMBER);
        $this->uhlSystemNumberFieldName = lcbru_get_custom_field_id_name(CIVI_FIELD_S_NUMBER);

        $this->recruitedCaseStatusValue = $this->caseStatusHelper->getValueFromLabel(CIVI_CASE_RECRUITED);
        $this->createNewParticipants = $createNewParticipants;
        $this->createNewCases = $createNewCases;
        $this->ignoredCaseStatuses = $ignoredCaseStatuses;
        $this->ignoreIfParticipantMissing = $ignoreIfParticipantMissing;

    }

    /**
     * Sets the creator_id to use for creating the cases and contacts
     *
     * @param integer creator_id
     *
     */
    public function setCreatorId($creatorId) {
        Guard::AssertInteger('$creatorId', $creatorId);

        $this->creatorId = $creatorId;
    }

    /**
     * Returns the validation errors of the CSV.
     *
     * @param string $filepath the path to the CSV file
     *
     * @return array of validation errors
     * @throws Exception if $filepath is empty.
     */
    public function getCsvValidationErrors($csv) {

        $result = array();

        foreach($csv->getValidationErrors() as $e) {
            $result[] = $e;
        }

        $csv->foreachRow(function($row) use (&$result) {
            $result = array_merge(
                $result,
                $this->getSingleValidationErrors($row)
            );
        });

        return $result;
    }

    public function getSingleValidationErrors($details) {

        $result = array();

        $nhsNumber = (empty($details[ContactHelper::NHS_NUMBER_FIELD_NAME])) ? '' : $details[ContactHelper::NHS_NUMBER_FIELD_NAME];
        $uhlSystemNumber = (empty($details[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME])) ? '' : $details[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME];

        if (isInvalidNhsNumber($nhsNumber)) {
            $result[] = "Invalid NHS Number: $nhsNumber";
        }
        if (!empty($uhlSystemNumber) && isInvalidUhlSystemNumber($uhlSystemNumber)) {
            $result[] = "Invalid UHL System Number: $uhlSystemNumber";
        }
        if (!empty($details['email']) && isInvalidEmailAddress($details['email'])) {
            $result[] = "Invalid Email Address: {$details['email']}";
        }

        try {
            $existingContact = $this->contactHelper->getSubjectFromIds($uhlSystemNumber, $nhsNumber, $this->caseHelper->extractParticipantStudyIds($details));

            if (is_null($existingContact)) {
                if ($this->ignoreIfParticipantMissing) {
                    watchdog('Contact does not exist, but ignoring: ', print_r($details, True));    
                } else if (!$this->createNewParticipants) {
                    $result[] = "Contact does not exist for: " . print_r($details, true);
                }
            }
        } catch (Exception  $e) {
            $result[] = $e->getMessage();
        }

        try {
            if (!empty($existingContact)) {
                $existingCase = $this->caseHelper->getContactSingleCaseOfTypeFromPotentialStudyIds($existingContact['id'], $this->caseTypeId, $details, $this->ignoredCaseStatuses);

                if (is_null($existingCase) && !$this->createNewCases) {
                    $result[] = "Case does not exist for: " . print_r($details, true);
                }
            }
        } catch (Exception  $e) {
            $result[] = $e->getMessage();
        }

        return $result;
    }

    public function getArrayValidationErrors($participants) {

        $result = array();

        foreach ($participants as $p) {
            $result = array_merge(
                $result,
                $this->getSingleValidationErrors($p)
            );
        }

        return $result;
    }

    /**
     * Imports the participants from the CSV file and creates a
     * case if required.
     *
     * @param string $filepath the path to the CSV file
     *
     * @return null
     * @throws Exception if $filepath is empty.
     */
    public function importFromCsv($csv) {
        $csv->foreachRow(function($row) {
            $this->importSingle($row);
        });
    }

    /**
     * Imports the participants from an array and creates a
     * case if required.
     *
     * @param array $participants
     *
     * @return null
     */
    public function importFromArray(array $particpants) {
        foreach ($particpants as $p) {
            $this->importSingle($p);
        }
    }

    /**
     * Imports the participants from an array and creates a
     * case if required.
     *
     * @param array $participants
     *
     * @return null
     */
    public function importSingle(array $details) {
        try {

            $contactId = $this->createSubject($details);

            if (empty($contactId) && $this->ignoreIfParticipantMissing) {
            return;
            }

            $caseId = $this->createCase($contactId, $details);
            $this->createCaseCustomValues($caseId, $details);

            return array(
            'contact_id' => $contactId,
            'case_id' => $caseId
            );

        } catch (Exception $ex) {
            watchdog($this->jobTitle, $ex->getMessage(), null, 'error');
            MailHelper::send(LCBRU_DEFAULT_EMAIL_RECIPIENT, 'Error: importing ' . $this->caseTypeName, $ex->__toString());
        }
    }

    /**
     * Validates and Imports the participants from an array and creates a
     * case if required.  Returns an array of validation errors.
     *
     * @param array $participants
     *
     * @return array
     */
    public function batchValidateAndImport(array $participants) {
        $errors = array();
        
        foreach ($participants as $p) {

            $e = $this->getSingleValidationErrors($p);

            if (empty($e)) {
                $this->importSingle($p);
            } else {
                $errors = array_merge($errors, $e);
            }
        }

        return $errors;
    }

    /**
     * Creates a case
     *
     * @param string $contactId the contact ID
     * @param array subjectData array of subject data
     *
     * @return id of case created
     * @throws Exception if $contactId is empty.
     * @throws Exception if $subjectData is empty.
     */
    private function createCase($contactId, array $subjectData) {
        Guard::AssertInteger('$contactId', $contactId);
        Guard::AssertArray('$subjectData', $subjectData);

        if ($contactId == 48913) {
            watchdog('FAST Duplicate', print_r($subjectData, True));    
            MailHelper::send(
                'richard.a.bramley@uhl-tr.nhs.uk',
                'FAST Duplicate',
                print_r($subjectData, True)
            );
        }

        $existingCase = $this->getExistingCase($contactId, $subjectData);

        $defaults = array();

        if (!empty($existingCase)) {
            $defaults['id'] = $existingCase['id'];
            $defaults['case_id'] = $existingCase['id'];

        } else {
            $defaults = array(
                'contact_id' => $contactId,
                'case_type_id' => $this->caseTypeId
            );

            if (empty($subjectData['start_date'])) {
              $defaults['start_date'] = date(DATE_RFC2822);
            }

            if (empty($subjectData['subject'])) {
              $defaults['subject'] = $this->caseTypeName;
            }

            if (empty($subjectData['case_status'])) {
              $defaults['case_status_id'] = $this->recruitedCaseStatusValue;
            }

            if (!empty($this->creatorId)) {
                $defaults['creator_id'] = $this->creatorId;
            }
        }

        $request = array_merge(
            $subjectData,
            $defaults
          );

        if (!empty($request['case_status'])) {
          $request['case_status_id'] = $this->caseStatusHelper->getValueFromLabel($request['case_status']);
        }

        return CiviCrmApiHelper::createObject('case', $request)['id'];
    }

    /**
     * Gets an existing case
     *
     * @param string $contactId the contact ID
     * @param array subjectData array of subject data
     *
     * @return details of the existing case
     * @throws Exception if $contactId is empty.
     * @throws Exception if $subjectData is empty.
     */
    private function getExistingCase($contactId, array $subjectData) {
        Guard::AssertInteger('$contactId', $contactId);
        Guard::AssertArray('$subjectData', $subjectData);

        # Get the Case that has the correct participant ID
        $existingCase = $this->caseHelper->getContactSingleCaseOfTypeFromPotentialStudyIds(
            $contactId,
            $this->caseTypeId,
            $subjectData,
            $this->ignoredCaseStatuses
        );

        if (!empty($existingCase)) {
            return $existingCase;
        }

        # No case with the correct participant ID exists, but
        # there might be a case of the correct type with all
        # blank IDs
        $cases = $this->caseHelper->getCasesForContactId(
            $contactId,
            array('case_type_id' => $this->caseTypeId)
        );

        $cases = $this->caseHelper->filterCasesExcludeStatuses(
            $cases,
            $this->ignoredCaseStatuses
        );

        $casesWithBlankIds = array_filter(
            $cases,
            function($case) {
                $pids = $this->caseHelper->extractParticipantStudyIds(
                    $case,
                    $this->caseTypeId
                );

                $non_blank_pids = array_filter(
                    $pids,
                    function($pid) {
                        return !empty($pid['participantStudyId']);
                    }
                );

                return count($non_blank_pids) == 0;
            }
        );

        if (!empty($casesWithBlankIds)) {
            return reset($casesWithBlankIds);
        }

        return null;
    }

    /**
     * Creates custom fields
     *
     * @param string $caseId the case ID
     * @param array subjectData array of subject data
     *
     * @return null
     * @throws Exception if $caseId is empty.
     * @throws Exception if $subjectData is empty.
     */
    private function createCaseCustomValues($caseId, array $subjectData) {
        Guard::AssertInteger('$caseId', $caseId);
        Guard::AssertArray('$subjectData', $subjectData);

        foreach ($subjectData as $name => $value) {
            if ($this->customFieldHelper->isCaseCustomField($this->caseTypeId, $name)) {

                if ($value == '<auto generate>') {
                    $value = $this->customFieldHelper->getAutoCustomFieldValue($name);
                } elseif (is_null($value)) {
                    $value = '';
                }

                $this->customFieldHelper->saveValue($caseId, $name, $value);
            }
        }
    }

    /**
     * Creates a subject
     *
     * @param array $subjectData the subject data
     *
     * @return id of the contact created
     * @throws Exception if $subjectData is empty.
     */
    public function createSubject(array $subjectData) {
        Guard::AssertArray('$subjectData', $subjectData);

        $request = array_merge(
            $subjectData
          , array(
              'contact_type' => 'Individual'
            , 'contact_sub_type' => array(str_replace(" ","_",CIVI_SUBTYPE_CONTACT))
            , "api.address.create.0" => $this->getAddressArray($subjectData)
            )
          );

        $this->addPhoneCreation($request, $subjectData);

        if (!empty($subjectData['gender'])) {
            $request['gender_id'] = $this->genderHelper->getValueFromLabel($subjectData['gender']);
        }

        if (!empty($subjectData['title'])) {
            $request['prefix_id'] = $this->individualPrefixHelper->getValueFromLabel($subjectData['title']);
        }

        if (!empty($subjectData['preferred_communication_method'])) {
            $request['preferred_communication_method'] = array($this->preferredCommunicationMethodHelper->getValueFromLabel($subjectData['preferred_communication_method']));
        }

        $nhsNumber = (empty($subjectData[ContactHelper::NHS_NUMBER_FIELD_NAME])) ? '' : $subjectData[ContactHelper::NHS_NUMBER_FIELD_NAME];
        $uhlSystemNumber = (empty($subjectData[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME])) ? '' : $subjectData[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME];

        if (!empty($nhsNumber)) {
          $request[$this->nhsNumberFieldName] = $nhsNumber;
        }

        if (!empty($uhlSystemNumber)) {
          $request[$this->uhlSystemNumberFieldName] = $uhlSystemNumber;
        }

        $existingContact = $this->contactHelper->getSubjectFromIds($uhlSystemNumber, $nhsNumber, $this->caseHelper->extractParticipantStudyIds($subjectData));

        if (empty($existingContact) && $this->ignoreIfParticipantMissing) {
            return NULL;
        }

        if (!empty($existingContact)) {
            $request['id'] = $existingContact['contact_id'];
        } else {
            if (!empty($this->creatorId)) {
                $request['creator_id'] = $this->creatorId;
            }
        }

        return CiviCrmApiHelper::createObject('contact', $request)['id'];
    }

    /**
     * Creates a phone record for a contact
     *
     * @param array $request the request data
     * @param array $subjectData the subject data
     *
     * @return details of the contact created
     * @throws Exception if $contactId is empty.
     * @throws Exception if $subjectData is empty.
     */
    private function addPhoneCreation(array &$request, array $subjectData) {
        Guard::AssertArray('$request', $request);
        Guard::AssertArray('$subjectData', $subjectData);

        if (empty($subjectData['phone'])) {
            return;
        }

 		$location = @$subjectData['phone_location'] ?: 'home';
 		$phoneType = @$subjectData['phone_type'] ?: 'phone';

        $phoneRequest = array_merge(
            $subjectData
          , array(
              'phone_type_id' => $this->phoneTypeHelper->getValueFromLabel($phoneType)
            , 'location_type_id' => $this->locationTypeHelper->getIdFromDisplayName($location)
            , 'options' => array(
                    'match' => array(
                        'location_type_id',
                        'phone_type_id',
                        'contact_id'
                      ))
                )
          );

        $request['api.phone.create.0'] = $phoneRequest;

    }

    /**
     * Creates an address record for a contact
     *
     * @param integer $contactId
     * @param array $subjectData the subject data
     *
     * @return details of the contact created
     * @throws Exception if $contactId is empty.
     * @throws Exception if $subjectData is empty.
     */
    private function getAddressArray(array $subjectData) {
        Guard::AssertArray('$subjectData', $subjectData);

 		$location = @$subjectData['address_location'] ?: 'home';

        $request = array_merge(
            $subjectData
          , array(
              'location_type_id' => $this->locationTypeHelper->getIdFromDisplayName($location)
            , 'options' => array(
                'match' => array(
                    'location_type_id',
                    'contact_id'
                    )
                )
            )
        );

        if (!empty($subjectData['state_province'])) {
            $request['state_province_id'] = $this->stateProvinceHelper->getIdFromTitle($subjectData['state_province']);
        }

        return $request;
    }
}
