<?php

/**
 * This class provides utility functions for working with Cases.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $ch = new ContactHelper();
 *   $contact = ch.getFromIds($uhlSystemNumber, $nhsNumber);
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class ContactHelper
{
    const NHS_NUMBER_FIELD_NAME = 'NHS_number';
    const UHL_SYSTEM_NUMBER_FIELD_NAME = 'UHL_S_number';
    const CIVI_FIELD_PRACTICE_CODE_FIELD_NAME = 'Practice_code';
    const CIVI_FIELD_ICE_LOCATION_FIELD_NAME = 'Practice_ICE_code';
    const DISPLAY_DATE_OF_BIRTH = 'display_date_of_birth';
    const LAST_NAME_UPPER = 'last_name_upper';

    /**
     * Constructor.
     *
     */
     public function __construct() {
        $cfh = new CustomFieldHelper();
        $this->uhlSystemNumberFieldIdName = $cfh->getFieldIdName(ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME);
        $this->nhsNumberFieldIdName = $cfh->getFieldIdName(ContactHelper::NHS_NUMBER_FIELD_NAME);
        $this->practiceCodeFieldIdName = $cfh->getFieldIdName(ContactHelper::CIVI_FIELD_PRACTICE_CODE_FIELD_NAME);
        $this->iceCodeFieldIdName = $cfh->getFieldIdName(ContactHelper::CIVI_FIELD_ICE_LOCATION_FIELD_NAME);

        $this->caseHelper = new CaseHelper();
    }

    /**
     * @param string $uhlSystemNumber the participant's UHL system number
     * @param boolean $nhsNumber the participant's NHS system number
     * @param boolean $participantStudyIds optional array of study IDs with fields called:
     *                  'isParticipantStudyIdFieldName' and 'participantStudyId'
     */
    public function getSubjectFromIds($uhlSystemNumber, $nhsNumber, array $participantStudyIds = array()) {
      Guard::AssertString('$uhlSystemNumber', $uhlSystemNumber);
      Guard::AssertString('$nhsNumber', $nhsNumber);
      Guard::AssertArray('$participantStudyIds', $participantStudyIds);

      $potentialContacts = array();

      $uhlSystemNumber = getFormattedUhlSystemNumber($uhlSystemNumber);
      $nhsNumber = getFormattedNhsNumber($nhsNumber);

      if (!empty($uhlSystemNumber)) {
        $cons = $this->getSubjects(array($this->uhlSystemNumberFieldIdName => $uhlSystemNumber));

        $potentialContacts = $potentialContacts + $cons;
        pp(potentialContacts, "UHL");
      }

      if (!empty($nhsNumber)) {
        $con = $this->getSubjects(array($this->nhsNumberFieldIdName => $nhsNumber));

        $potentialContacts = $potentialContacts + $con;
        pp(potentialContacts, "NHS");
      }

      $potentialContacts = $potentialContacts + $this->getSubjectFromParticipantStudyIds($participantStudyIds);

      pp(potentialContacts, "Last");

      if (count($potentialContacts) == 0) {
        return null;
      }

      if (count($potentialContacts) > 1) {
          throw new Exception("More than one contact found for participant with UHL System Number = '$uhlSystemNumber', NHS Number = '$nhsNumber' and participant study IDs of '" . print_r($participantStudyIds, true) . "'".PHP_EOL.'Specifically:'.PHP_EOL.print_r($potentialContacts, true));
      }

      $result = array_shift($potentialContacts);

      if (!empty($uhlSystemNumber) && !empty($result[CIVI_FIELD_S_NUMBER])) {
        if ($uhlSystemNumber != $result[CIVI_FIELD_S_NUMBER]) {
          throw new Exception("UHL System Numbers do not match.  Existing is '{$result[CIVI_FIELD_S_NUMBER]}', but provided is '$uhlSystemNumber' for record with NHS Number = '{$result[CIVI_FIELD_NHS_NUMBER]}'");
        }
      }

      if (!empty($nhsNumber) && !empty($result[CIVI_FIELD_NHS_NUMBER])) {
        if ($nhsNumber != $result[CIVI_FIELD_NHS_NUMBER]) {
          throw new Exception("NHS Numbers do not match.  Existing is '{$result[CIVI_FIELD_NHS_NUMBER]}', but provided is '$nhsNumber' for record with S Number = '{$result[CIVI_FIELD_S_NUMBER]}'");
        }
      }

      return $result;
    }

    /**
     * @param string $uhlSystemNumber the participant's UHL system number
     * @param boolean $nhsNumber the participant's NHS system number
     * @param boolean $participantStudyIds optional array of study IDs with fields called:
     *                  'isParticipantStudyIdFieldName' and 'participantStudyId'
     */
    public function getSubjectFromId($contactId) {
        Guard::AssertInteger('$contactId', $contactId);

        $results = $this->getSubjects(array('id' => $contactId));

        if (empty($results)) {
          throw new Exception("Subject does not exist for contact ID = $contactId");
        }

        return reset($results);
    }

    public function getSubjectFromParticipantStudyIds($participantStudyIds) {
      $result = array();

      foreach ($this->caseHelper->getCaseIdsForParticipantStudyIds($participantStudyIds) as $caseId) {
        $case = CiviCrmApiHelper::getObject('case', array('case_id' => $caseId));

        if (empty($case['client_id'])) {
          throw new Exception("Case does not have any associated clients: " . print_r($case, true));
        }
        if (count($case['client_id']) > 1) {
          throw new Exception("Case has too many clients: " . print_r($case, true));
        }

        $cons = $this->getSubjects(array('id' => array_shift($case['client_id'])));

        $result = $result + $cons;
      }

      return $result;
    }

    /**
     * @param string $search_string a string that may contain:
     *          - NHS Number
     *          - UHL System Number
     *          - Subject name
     *          - Study Identifier
     *          - Email Address
     * @return array of matching contacts
     */
    public function searchSubjects($search_string, $limit=10, $search_name=False) {
      Guard::AssertString('$search_string', $search_string);

      $result = array();

      $result = $this->getSubjects(array(
        $this->uhlSystemNumberFieldIdName => getFormattedUhlSystemNumber($search_string)
      ));

      if (!empty($result)) {
        return $result;
      }

      $result = $this->getSubjects(array(
        $this->nhsNumberFieldIdName => getFormattedNhsNumber($search_string)
      ));

      if (!empty($result)) {
        return $result;
      }

      $studyIdFields = $this->caseHelper->getParticipantStudyIdFieldName($search_string);
      $studyIds = $this->caseHelper->extractParticipantStudyIds($studyIdFields);
      $result = $this->getSubjectFromParticipantStudyIds($studyIds);

      IF (count($result) > 0) {
        return $result;
      }

      if ($search_name) {
        $result = $this->getSubjectsFromContactIds(
          $this->getSubjectIdsForNameSearch($search_string)
        );
        return $result;
      }

      return $result;
    }

    public function getSubjects(array $parameters) {
      $parameters['return'] = $this->uhlSystemNumberFieldIdName . ',' . $this->nhsNumberFieldIdName . ',first_name,last_name,birth_date,gender_id,gender,street_address,supplemental_address_1,supplemental_address_2,city,postal_code,state_province,country,display_name';

      $cons = CiviCrmApiHelper::getObjectsAll('contact', $parameters);
      $result = array();

      foreach($cons as $c) {
        // Copy the custom fields to reasonable names
        if (array_key_exists($this->uhlSystemNumberFieldIdName, $c)) {
          $c[CIVI_FIELD_S_NUMBER] = getFormattedUhlSystemNumber($c[$this->uhlSystemNumberFieldIdName]);
        } else {
          $c[CIVI_FIELD_S_NUMBER] = '';
        }
        if (array_key_exists($this->nhsNumberFieldIdName, $c)) {
          $c[CIVI_FIELD_NHS_NUMBER] = getFormattedNhsNumber($c[$this->nhsNumberFieldIdName]);
        } else {
          $c[CIVI_FIELD_NHS_NUMBER] = '';
        }
        if (array_key_exists('last_name', $c)) {
          $c[ContactHelper::LAST_NAME_UPPER] = strtoupper($c['last_name']);
        } else {
          $c[ContactHelper::LAST_NAME_UPPER] = '';
        }
        $c[ContactHelper::DISPLAY_DATE_OF_BIRTH] = '';
        if (array_key_exists('birth_date', $c)) {
          $dob = DateTime::createFromFormat('Y-m-d', $c['birth_date']);
          if (!empty($dob)) {
              $c[ContactHelper::DISPLAY_DATE_OF_BIRTH] = $dob->format('d M Y');
          }
        }

        $result[$c['id']] = $c;
      }

    return $result;

    }

    public function getSubjectsFromContactIds(array $contact_ids) {
      Guard::AssertArrayOfIntegers('$contact_ids', $contact_ids);

      $result = array();

      foreach ($contact_ids as $contactId) {
        $result = array_merge($result, $this->getSubjects(array('id' => $contactId)));
      }

      return $result;
    }

    public function getPracticesFromContactIds(array $contact_ids) {
      Guard::AssertArrayOfIntegers('$contact_ids', $contact_ids);

      $result = array();

      foreach ($contact_ids as $contactId) {
        $result[$contactId] = $this->getPracticeFromId($contactId);
      }

      return $result;
    }

    public function getPracticeFromId($id) {
      Guard::AssertInteger('$is', $id);

      return $this->getPractice(array('id' => $id));
    }

    public function getPractice(array $params) {
      Guard::AssertArray('$params', $params);

      $parameters = array_merge($params, array('contact_sub_type' => 'GP_Surgery'));
      return CiviCrmApiHelper::getObjectOrNull('contact', $parameters);
    }

    public function getContactsFromContactIds(array $contact_ids) {
      Guard::AssertArrayOfIntegers('$contact_ids', $contact_ids);

      $result = array();

      foreach ($contact_ids as $contactId) {
        $result[$contactId] = CiviCrmApiHelper::getObjectOrNull('contact', array('id' => $contactId));
      }

      return $result;
    }

    public function getAddressFields(array $contact_details) {
      return array_filter(array(
        $contact_details["street_address"],
        $contact_details["supplemental_address_1"],
        $contact_details["supplemental_address_2"],
        $contact_details["city"],
        $contact_details["state_province_name"],
        $contact_details["postal_code"],
        $contact_details["country"]
      ));
    }

    public function getSurgeryByCode($code) {
      Guard::AssertString_NotEmpty('$code', $code);

      $surgery = CiviCrmApiHelper::getObject('Contact', array(
        $this->practiceCodeFieldIdName => $code,
        'return' => "{$this->iceCodeFieldIdName},{$this->practiceCodeFieldIdName},contact_id"
        ));
      
      return ArrayHelper::translateKeys($surgery, array(
        $this->iceCodeFieldIdName => ContactHelper::CIVI_FIELD_ICE_LOCATION_FIELD_NAME,
        $this->practiceCodeFieldIdName => ContactHelper::CIVI_FIELD_PRACTICE_CODE_FIELD_NAME,
      ));
    }

    public function getSurgeryByID($id) {
      Guard::AssertInteger('$id', $id);

      $surgery = CiviCrmApiHelper::getObject('Contact', array(
        'id' => $id,
        'return' => "{$this->iceCodeFieldIdName},{$this->practiceCodeFieldIdName},contact_id"
        ));
      
      return ArrayHelper::translateKeys($surgery, array(
        $this->iceCodeFieldIdName => ContactHelper::CIVI_FIELD_ICE_LOCATION_FIELD_NAME,
        $this->practiceCodeFieldIdName => ContactHelper::CIVI_FIELD_PRACTICE_CODE_FIELD_NAME,
      ));
    }

    private function getSubjectIdsForNameSearch($search_string, $limit=0) {
      Guard::AssertString_NotEmpty('$search_string', $search_string);
      Guard::AssertInteger('$limit', $limit);

      $query = "
          SELECT id
          FROM civicrm_contact
          WHERE display_name LIKE %1
            AND contact_sub_type = 'Subject'
          ";

      if ($limit > 0) {
        $query .= "LIMIT $limit";
      }

      $dao = CRM_Core_DAO::executeQuery($query,
        array(1 => array('%' . $search_string . '%', 'String'))
      );

      $result = array();

      while ($dao->fetch()) {
        $result[] = $dao->id;
      }

      return $result;
    }

    public function searchPractices($search_string) {
      $parameters = array(
        'contact_sub_type' => 'GP_Surgery',
        'display_name' => $search_string,
      );
      return CiviCrmApiHelper::getObjectsAll('contact', $parameters);
    }

    public function getPractices() {
      $practices = CiviCrmApiHelper::getObjectsAll('contact', array(
        'contact_sub_type' => 'GP_Surgery',
      ));

      $result = array();
      foreach ($practices as $p) {
        $result[$p['id']] = $p;
      }
      return $result;
    }

    public function getContactIdForDrupalUserId($userId) {
        return CRM_Core_BAO_UFMatch::getContactId($userId);
    }
}
