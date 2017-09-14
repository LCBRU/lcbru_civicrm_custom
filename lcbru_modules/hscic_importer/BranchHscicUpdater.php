<?php
class BranchHscicUpdater implements IHscicUpdater
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
    const ParentOrganisationCode = 14; // Code for the GP Practice that runs the Branch Surgery
    const JoinParentDate = 15;
    const LeftParentDate = 16;
    const ContactTelephoneNumber = 17;
    const AmendedRecordIndicator = 21;
    const GORCode = 23;

    private $practiceIds;
    private $branchCodeCustomFieldName = NULL;
    private $logger;
    private $processedCount = 0;
    private $ignoredCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;
    private $deletedCount = 0;

    public function __construct(IHscicLogger $logger, array $practiceIds) {
        $this->practiceIds = $practiceIds;
        $this->branchCodeCustomFieldName = lcbru_get_custom_field_id_name(CIVI_FIELD_BRANCH_CODE);
        $this->logger = $logger;
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
        
        if (isInvalidGpBranchCode($values[self::OrganisationCode])) {
            $this->ignoredCount++;
            return;
        }
                
        $practice = $this->getPractice($values[self::ParentOrganisationCode]);

        if (is_null($practice)) {
            $this->ignoredCount++;
            return;
        }

        $this->process($values, $practice);
    }

    private function process(array $values, array $practice) {
        // Make the name look pretty
        $values[self::Name] = ucwords(strtolower($values[self::Name]));

        $existing = $this->getAddressForCode($values[self::OrganisationCode], $practice);

        $newAddress = addressSplit(array(
                $values[self::Name],
                $values[self::AddressLine1],
                $values[self::AddressLine2],
                $values[self::AddressLine3],
                $values[self::AddressLine4],
                $values[self::AddressLine5],
                $values[self::Postcode],
            ));

        unset($newAddress["county"]); // Not needed.  In fact, it gets in the way.

        if (is_null($existing) && empty($values[self::LeftParentDate])) {
            $this->logger->log("Branch '{$values[self::OrganisationCode]}' does not exist in CiviCRM. Creating.");
            $this->save($values, $newAddress, $practice['id']);
            $this->createdCount++;
        } elseif (!is_null($existing) && !empty($values[self::LeftParentDate]) && date_create($values[self::LeftParentDate]) < date_create("now")) {
            $this->logger->log("Branch '{$values[self::OrganisationCode]}' has left parent. Deleting.");
            $this->delete($existing['id']);
            $this->deletedCount++;
        } elseif (!is_null($existing) && $this->detailsHaveChanged($values, $newAddress, $existing)) {
            $this->logger->log("Branch '{$values[self::OrganisationCode]}' details do not match. Updating.");
            $this->save($values, $newAddress, $practice['id'], $existing['id']);
            $this->updatedCount++;
        }
    }

    private function getAddressForCode($code, array $practice) {
        $result = null;

        $addresses = $this->getPracticeAddresses($practice);

        if (!is_null($addresses)) {
            foreach($addresses as $a) {
                if ($a[CIVI_FIELD_BRANCH_CODE] === $code) {
                    $result = $a;
                    break;
                }
            }
        }

        return $result;
    }

    private function getPracticeAddresses(array $practice) {
        $addresses = lcbru_civicrm_api_getall(
            'Address',
            array(
                'contact_id' => $practice['id']
                ));

        foreach ($addresses as &$a) {
            $a[CIVI_FIELD_BRANCH_CODE] = get_civi_custom_latest_value_by_name($a['id'], CIVI_FIELD_BRANCH_CODE);
        }

        return $addresses;
    }

    private function detailsHaveChanged(array $newValues, array $newAddress, array $existing) {

        if (($newAddress['supplemental_address_1'] ?: '') != ($existing['supplemental_address_1'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' supplemental_address_1 changed.");
            return true;
        }

        if (($newAddress['street_address'] ?: '') != ($existing['street_address'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' street_address changed.");
            return true;
        }

        if (($newAddress['supplemental_address_2'] ?: '') != ($existing['supplemental_address_2'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' supplemental_address_2 changed.");
            return true;
        }

        if (($newAddress['city'] ?: '') != ($existing['city'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' city changed.");
            return true;
        }

        if (($newAddress['postal_code'] ?: '') != ($existing['postal_code'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' postal_code changed.");
            return true;
        }

        if (($newAddress['state_province_id'] ?: '') != ($existing['state_province_id'] ?: '')) {
            watchdog(__METHOD__, "Branch '{$newValues[self::OrganisationCode]}' state_province_id changed.");
            return true;
        }

        return false;
    }

    private function save($values, $address, $practiceId, $addressId = NULL) {
        $address['is_primary'] = '0';
        $address['location_type_id'] = '4';
        $address['contact_id'] = $practiceId;
        $address['id'] = $addressId;
        $address['version'] = '3';
        $address[$this->branchCodeCustomFieldName] = $values[self::OrganisationCode];

        $created = civicrm_api('Address', 'create', $address);
    }

    private function delete($addressId) {
        $address['id'] = $addressId;
        $address['version'] = '3';

        civicrm_api('Address', 'delete', $address);
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