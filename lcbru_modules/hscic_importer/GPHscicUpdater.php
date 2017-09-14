<?php
class GPHscicUpdater implements IHscicUpdater
{
    // values array indices
    const OrganisationCode = 0;
    const Name = 1;
    // Format of name is "surname initials" 
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
    // P = Proposed
    const OrganisationSubTypeCode = 13;
    // P = Principal/Senior GP at practice
    // O = Other GP in practice (not Principal/Senior GP)
    const ParentOrganisationCode = 14;
    const JoinParentDate = 15;
    const LeftParentDate = 16;
    const ContactTelephoneNumber = 17;
    const AmendedRecordIndicator = 21;
    const CurrentCareOrganisation = 23;

    const First_name = 'First_name';
    const Surname = 'surname';
    const prefix_id = 'prefix_id';

    private $practiceIds;
    private $seniorPartnerRelationshipType;
    private $doctorPrefix;
    private $logger;
    private $processedCount = 0;
    private $ignoredCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;
    private $deletedCount = 0;
    private $validPrefixes;

    public function __construct(IHscicLogger $logger, array $practiceIds) {
        $this->practiceIds = $practiceIds;
        $this->seniorPartnerRelationshipType = find_civi_relationship_type(CIVI_REL_SENIOR_PARTNER);
        $this->doctorPrefix = getPrefixOptionValueFromTitle('Dr');
        $this->logger = $logger;
        $this->validPrefixes = getPrefixOptionValues();
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

        if (isInvalidGpCode($values[self::OrganisationCode])) {
            $this->ignoredCount++;
            return;
        }
        
        $practice = $this->getPractice($values[self::ParentOrganisationCode]);

        if (empty($practice)) {
            $this->ignoredCount++;
            return;
        }

        if (!$this->employedByRelevantPractice($values)) {
            $this->deleteGp($values);
            return;
        }

        $this->process($values, $practice);

    }

    private function process(array $values, array $practice) {
        $existing = lcbru_get_contact_by_custom_field(CIVI_FIELD_PRACTITIONER, $values[self::OrganisationCode]);

        $values[self::First_name] = $this->getBestFirstName($values, $existing);
        $values[self::Surname] = $this->getBestSurame($values);
        $values[self::prefix_id] = $this->getBestPrefix($values, $existing);

        if (is_null($existing)) {
            $this->logger->log("GP '{$values[self::OrganisationCode]}' does not exist in CiviCRM. Creating.");
            $this->save($values, $practice);
            $this->createdCount++;
        } elseif ($this->detailsHaveChanged($values, $practice, $existing)) {
            $this->logger->log("GP '{$values[self::OrganisationCode]}' details do not match. Updating.");
            $this->save($values, $practice, $existing['id']);
            $this->updatedCount++;
        }
    }

    private function getBestFirstName(array $values, $existing) {
        // Since we only have initials for first name use the
        // existing first name if they have been entered
        $nameParts = explode(' ', $values[self::Name]);
        $initials = $nameParts[count($nameParts) - 1];

        if (is_null($existing)) {
            return $initials;
        }

        if (!array_key_exists('first_name', $existing)) {
            return $initials;
        }

        if (strlen($initials) > strlen($existing['first_name'])) {
            return $initials;
        }

        return $existing['first_name'];
    }

    private function getBestSurame(array $values) {
        // Extract the surname from the full name and make
        // look nice
        $nameParts = explode(' ', $values[self::Name]);
        $surname = implode(' ', array_slice($nameParts, 0, count($nameParts) - 1));
        return ucwords(strtolower($surname));
    }

    private function getBestPrefix(array $values, $existing) {
        // Since there is no prefix, use the existin one if it
        // has been entered, or use 'Dr' if not.
        if (is_null($existing)) {
            return $this->doctorPrefix;
        }

        if (empty($existing['prefix_id'])) {
            return $this->doctorPrefix;
        }

        if (!array_key_exists($existing['prefix_id'], $this->validPrefixes)) {
            return $this->doctorPrefix;
        }

        return $existing['prefix_id'];
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

    private function employedByRelevantPractice(array $values) {
        if (!array_key_exists($values[self::ParentOrganisationCode], $this->practiceIds)) {
            return false;
        }

        $leftParentDate = date_create($values[self::LeftParentDate]);

        if ($leftParentDate && $leftParentDate < date_create("now")) {
            return false;
        }

        return true;
    }

    private function detailsHaveChanged(array $newValues, array $practice, array $existing) {

        if (($newValues[self::First_name] ?: '') != ($existing['first_name'] ?: '')) {
            watchdog(__METHOD__, "GP '{$newValues[self::OrganisationCode]}' First_name changed.");
            return true;
        }

        if (($newValues[self::Surname] ?: '') != ($existing['last_name'] ?: '')) {
            watchdog(__METHOD__, "GP '{$newValues[self::OrganisationCode]}' surname changed.");
            return true;
        }

        if (($practice['organization_name'] ?: '') != ($existing['current_employer'] ?: '')) {
            watchdog(__METHOD__, "GP '{$newValues[self::OrganisationCode]}' current_employer changed.");
            return true;
        }

        if ($existing['phone'] != $practice['phone']) {
            watchdog(__METHOD__, "Practice '{$newValues[self::OrganisationCode]}' telephone number changed.");
            return true;
        }

        $seniorPartnerRelationship = civicrm_api(
            'Relationship',
            'get',
            array(
                'version' => '3',
                'contact_id_a' => $existing['id'],
                'contact_id_b' => $practice['id'],
                'relationship_type_id' => $this->seniorPartnerRelationshipType,
                'is_active' => '1'));

        if ($seniorPartnerRelationship['count'] > 0 && $newValues[self::OrganisationSubTypeCode] != 'P') {
            return true;
        }

        if ($seniorPartnerRelationship['count'] == 0 && $newValues[self::OrganisationSubTypeCode] == 'P') {
            return true;
        }

        if (empty($existing['prefix_id'])) {
            return true;
        }

        return false;
    }

    private function save(array $values, array $practice, $contactId = NULL) {

        $gp = array (
            "id" => $contactId,
            "contact_type" => "Individual", 
            "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_HW)), 
            "first_name" => $values[self::First_name],
            "last_name" => $values[self::Surname],
            'prefix_id' => $values[self::prefix_id],
            "current_employer" => $practice['organization_name'],
            "email_greeting_id" => CIVI_EMAIL_GREETING_PREFIX_SURNAME_ID,
            "postal_greeting_id" => CIVI_POSTAL_GREETING_PREFIX_SURNAME_ID
            );

        $address = $this->getAddress($practice);

        if (!is_null($address)) {
            $gp["api.address.create"] = $address;
        }

        $telephone = $this->getTelephone($practice);

        if (!is_null($telephone)) {
            $gp["api.phone.create"] = $telephone;
        }

        $gp = create_civi_contact_with_custom_data(
            $gp,
            array(
                CIVI_FIELD_PRACTITIONER => $values[self::OrganisationCode]
                ),
            FALSE);

        if ($values[self::OrganisationSubTypeCode] == 'P') {
            create_civi_relationship(
                $this->seniorPartnerRelationshipType,
                $gp['id'],
                $practice['id'],
                True);
        } else {
            delete_civi_relationship(array(
                'contact_id_a' => $gp['id'],
                'contact_id_b' => $practice['id'],
                'relationship_type_id' => $this->seniorPartnerRelationshipType));
        }
    }

    private function getAddress(array $practice) {
        $result = civicrm_api('address', 'getsingle', array('version' => '3', 'id' => $practice['address_id']));

        // Address was not found
        if (empty($result['id'])) {
            return NULL;
        }

        $result['master_id'] = $result['id'];
        unset($result['contact_id']);
        unset($result['id']);

        $result["options"] = array(
          "match" => array(
              "location_type_id",
              "contact_id"
            )
        );

        return $result;
    }

    private function getTelephone(array $practice) {
        $result = civicrm_api('phone', 'getsingle', array('version' => '3', 'id' => $practice['phone_id']));

        // Telephone not found
        if (empty($result['id'])) {
            return NULL;
        }
        $result['master_id'] = $result['id'];
        unset($result['contact_id']);
        unset($result['id']);
        $result["options"] = array(
          "match" => array(
              "location_type_id",
              "contact_id"
            )
        );

        return $result;
    }

    private function deleteGp(array $values) {
        $existing = lcbru_get_contact_by_custom_field(CIVI_FIELD_PRACTITIONER, $values[self::OrganisationCode]);

        if (is_null($existing)) {
            return;
        }

        $this->logger->log("GP '{$values[self::OrganisationCode]}' no longer required.  Deleting.");
        delete_civi_contact(array('id' => $existing['id']));
        $this->deletedCount++;
   }

}