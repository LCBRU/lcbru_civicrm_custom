<?php
class PracticeHscicUpdater implements IHscicUpdater
{
    // values array indices
    const OrganisationCode = 0;      
    const Name = 1;
    const NationalGrouping = 2;
    const HighLevelHealthAuthority = 3;
    const AddressLine1 = 4;
    const AddressLine2 = 5;
    const AddressLine3 = 6;
    const AddressLine4 = 7;
    const AddressLine5 = 8;
    const Postcode = 9;
    const OpenDate = 10;
    const CloseDate = 11;
    const StatusCode = 12;
    // A = Active
    // C = Closed
    // D = Dormant
    // P = Proposed
    const Organisation = 13;
    // B = Allocated to a Parent Organisation
    // Z = Not allocated to a Parent Organisation
    const ParentOrganisationCode = 14;
    // Code for the Primary Care Organisation the GP Practice is linked to
    const JoinParentDate = 15;
    const LeftParentDate = 16;
    const ContactTelephoneNumber = 17;
    const AmendedRecordIndicator = 21;
    const PracticeType = 25;
    // 0 = Other
    // 1 = WIC Practice
    // 2 = OOH Practice
    // 3 = WIC + OOH Practice
    // 4 = GP Practice
    // 5 = Prison Prescribing Cost Centre

    private $practiceIds;
    private $activeCcgCodes;
    private $ccgRelationshipType = 0;
    private $ccgIDs = array();
    private $logger;
    private $processedCount = 0;
    private $ignoredCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;
    private $deletedCount = 0;

    public function __construct(IHscicLogger $logger, array $activeCcgCodes, array $practiceIds) {
        $this->practiceIds = $practiceIds;
        $this->activeCcgCodes = $activeCcgCodes;
        $this->ccgRelationshipType = find_civi_relationship_type(CIVI_REL_CCG);
        $this->logger = $logger;
        pp($this->activeCcgCodes, 'CCGs to Process');
    }

    public function complete() {
        $message = "Processed = {$this->processedCount}".PHP_EOL;
        $message .= "Ignored = {$this->ignoredCount}".PHP_EOL;
        $message .= "Created = {$this->createdCount}".PHP_EOL;
        $message .= "Updated = {$this->updatedCount}".PHP_EOL;
        $message .= "Deleted = {$this->deletedCount}".PHP_EOL;

        $this->logger->log($message);        
    }
    
    public function update(array $values) {
        $this->processedCount++;
        
        if (isInvalidGpPracticeCode($values[self::OrganisationCode])) {
            $this->ignoredCount++;
            return;
        }

        $practice = $this->getPractice($values[self::OrganisationCode]);

        if (empty($practice) && !in_array($values[self::ParentOrganisationCode], $this->activeCcgCodes, TRUE)) {
            $this->ignoredCount++;
            return;
        }
        
        $this->processPractice($values, $practice);
    }

    private function processPractice(array $values, $existingPractice) {
        // Make the name look pretty
        $values[self::Name] = ucwords(strtolower($values[self::Name])) . " (" . $values[self::OrganisationCode] . ")" ;

        $newAddress = addressSplit(array(
                $values[self::AddressLine1],
                $values[self::AddressLine2],
                $values[self::AddressLine3],
                $values[self::AddressLine4],
                $values[self::AddressLine5],
                $values[self::Postcode],
            ));

        unset($newAddress["county"]); // Not needed.  In fact, it gets in the way.

        if (is_null($existingPractice)) {
            $this->logger->log("Practice '{$values[self::OrganisationCode]}' does not exist in CiviCRM. Creating.");
            $this->save($values, $newAddress);
            $this->createdCount++;
        } elseif ($this->detailsHaveChanged($values, $newAddress, $existingPractice)) {
            $this->logger->log("Practice '{$values[self::OrganisationCode]}' details do not match. Updating.");
            $this->save($values, $newAddress, $existingPractice['contact_id']);
            $this->updatedCount++;
        }
    }

    private function detailsHaveChanged(array $newValues, array $newAddress, array $existingPractice) {
        if ($newValues[self::Name] != $existingPractice['organization_name']) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' name changed.");
            return true;
        }

        if ($newValues[self::ContactTelephoneNumber] != $existingPractice['phone']) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' telephone number changed.");
            return true;
        }

        if ($newAddress != lcbru_getAddressFromContact($existingPractice)) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' address changed.");
            return true;
        }

        $existingStatus = get_civi_custom_latest_value_by_name($existingPractice['contact_id'], CIVI_FIELD_PRACTICE_STATUS);

        if ($existingStatus != $newValues[self::StatusCode]) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' status changed.");
            return true;
        }

        $existingCcgRelationship = $this->getExistingCcgRelationship($existingPractice['id']);
        
        if (is_null($existingCcgRelationship)) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' has no existing CCG.");
            return true;
        }

        if ($existingCcgRelationship['contact_id_b'] != $this->getIdForCcgCode($newValues[self::ParentOrganisationCode])) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' CCG changed.");
            return true;
        }

        return false;
    }

    private function getExistingCcgRelationship($contactId) {

      $existingRelationship = civicrm_api('Relationship','getsingle',array('version' => '3', 'contact_id_a' => $contactId, 'relationship_type_id' => $this->ccgRelationshipType, 'is_active' => '1'));

      if (!empty($existingRelationship['is_error'])) {
        return null;
      }

      return $existingRelationship;
    }

    private function getIdForCcgCode($code) {
        if (!array_key_exists($code, $this->ccgIDs)) {
            $ccg = lcbru_get_contact_by_custom_field(CIVI_FIELD_CCG_CODE, $code);
            $this->ccgIDs[$code] = $ccg['id'];
        }

        return $this->ccgIDs[$code];
    }

    private function save($values, $address, $practiceId = NULL) {
        $address["is_primary"] = "1";
        $address["location_type_id"] = "3";
        $address["options"] = array(
            "match" => array(
                "location_type_id",
                "contact_id"
              )
          );

        $phone = array(
                "phone" => $values[self::ContactTelephoneNumber], 
                "location_type_id" => "3", 
                "is_primary" => "1", 
                "phone_type_id" => "1",
                "options" => array(
                    "match" => array(
                        "location_type_id",
                        "phone_type_id",
                        "contact_id"
                      ))
            );

        $practice = array (
            "id" => $practiceId,
            "contact_type" => "Organization", 
            "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_SURGERY)), 
            "organization_name" => $values[self::Name],
            "api.address.create.0" => $address,
            "api.phone.create.0" => $phone, 
            );

        $practice = create_civi_contact_with_custom_data(
            $practice,
            array(
                CIVI_FIELD_PRACTICE_CODE => $values[self::OrganisationCode],
                CIVI_FIELD_PRACTICE_STATUS => $values[self::StatusCode]
                ),
            FALSE);

        delete_civi_relationship(array(
            'contact_id_a' => $practice['id'],
            'relationship_type_id' => $this->ccgRelationshipType,
            'is_active' => '1'));

        if (!is_null($this->getIdForCcgCode($values[self::ParentOrganisationCode]))) {
            create_civi_relationship(
                $this->ccgRelationshipType,
                $practice['id'],
                $this->getIdForCcgCode($values[self::ParentOrganisationCode]),
                true);
        }
    }

    private function getPractice($practiceCode) {
        if (empty($this->practiceIds[$practiceCode])) {
            return NULL;
        }
        
        $practice = get_civi_contact($this->practiceIds[$practiceCode]);

        if (empty($practice)) {
            return NULL;
        }

        return $practice;
    }

}