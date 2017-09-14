<?php
class GPMembershipHscicUpdater implements IHscicUpdater
{
    // values array indices
    const PractitionerCode = 0;
    const ParentOrganisationCode = 1;
    const ParentOrganisationType = 2;
    const JoinParentDate = 3;
    const LeftParentDate = 4;
    // Format: YYYYMMDD
    const AmendedRecordIndicator = 5;

    private $gpActivePracticeXref = array();
    private $practices = array();
    private $logger;
    private $processedCount = 0;
    private $ignoredCount = 0;
    private $updatedCount = 0;
    private $createdCount = 0;
    private $deletedCount = 0;
    private $employeeOfRelationshipTypeId;

    public function __construct(IHscicLogger $logger, array $practices) {
        $this->practices = $practices;
        $this->logger = $logger;
        $this->employeeOfRelationshipTypeId = find_civi_relationship_type('Employee of');
    }

    public function complete() {
        $this->updateGPsMembership();
        
        $message = "Processed = {$this->processedCount}".PHP_EOL;
        $message .= "Ignored = {$this->ignoredCount}".PHP_EOL;
        $message .= "Created = {$this->createdCount}".PHP_EOL;
        $message .= "Updated = {$this->updatedCount}".PHP_EOL;
        $message .= "Deleted = {$this->deletedCount}".PHP_EOL;

        $this->logger->log($message);        
    }
    
    public function update(array $values) {
        $this->processedCount++;

        if (isInvalidGpCode($values[self::PractitionerCode])) {
            $this->ignoredCount++;
            return;
        }
        
        $leftParentDate = date_create($values[self::LeftParentDate]);

        if ($leftParentDate && $leftParentDate < date_create("now")) {
            $this->ignoredCount++;
            return;
        }

        $practiceId = $this->getPracticeId($values[self::ParentOrganisationCode]);

        if (is_null($practiceId)) {
            $this->ignoredCount++;
            return;
        }

        $this->process($values, $practiceId);
    }

    private function process(array $values, $practiceId) {
        if (!array_key_exists($values[self::PractitionerCode], $this->gpActivePracticeXref)) {
            $this->gpActivePracticeXref[$values[self::PractitionerCode]] = array();
        }

        $this->gpActivePracticeXref[$values[self::PractitionerCode]][] = $practiceId;
    }

    private function getPracticeId($code) {
        if (array_key_exists($code, $this->practices)) {
            return $this->practices[$code];
        }
    }

    public function getActivePracticesForGp($gpCode) {
        if (array_key_exists($gpCode, $this->gpActivePracticeXref)) {
            $result = $this->gpActivePracticeXref[$gpCode];
            sort($result);
            return array_values(array_unique($result));
        }
    }

    private function updateGPsMembership() {
        foreach (array_keys($this->gpActivePracticeXref) as $gpCode) {
            $gp = lcbru_get_contact_by_custom_field(CIVI_FIELD_PRACTITIONER, $gpCode);

            if (empty($gp)) {
                $this->logger->log("GP '$gpCode' is associated with practices, but does not exist.");
                $this->ignoredCount++;
            } else {
                $this->updatePracticeMembership($gp['id'], $gpCode);
            }
        }
    }
    
    private function updatePracticeMembership($gpId, $gpCode) {
        $existingPractices = $this->getExistingPracticesForGp($gpId);
        $requiredPractices = $this->getActivePracticesForGp($gpCode);

        $practicesToDelete = array_diff($existingPractices, $requiredPractices);

        foreach ($practicesToDelete as $practiceId) {

            $this->deletedCount++;

            delete_civi_relationship(array(
                'contact_id_a' => $gpId,
                'contact_id_b' => $practiceId,
                'relationship_type_id' => $this->employeeOfRelationshipTypeId));
        }

        $practicesToCreate = array_diff($requiredPractices, $existingPractices);

        foreach ($practicesToCreate as $practiceId) {

            $this->createdCount++;

            create_civi_relationship(
                $this->employeeOfRelationshipTypeId,
                $gpId,
                $practiceId,
                True);
        }

    }

    private function getExistingPracticesForGp($gpId) {
        $relationships = civicrm_api(
            'Relationship',
            'get',
            array(
                'version' => '3',
                'contact_id_a' => $gpId,
                'relationship_type_id' => $this->employeeOfRelationshipTypeId));

        $result = array();

        foreach ($relationships['values'] as $r) {
            $result[] = $r['contact_id_b'];
        }

        sort($result);
        return array_values(array_unique($result));
    }

}