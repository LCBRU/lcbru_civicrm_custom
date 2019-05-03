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
 *   $selectOptions = CaseHelper::getCaseTypeSelectOptions()
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class CaseHelper
{

    const HOOKNAME_GET_STUDY_ID_FIELD_NAME = 'lcbru_getStudyIdFieldName';

    private $_cached_case_types = null;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->customFieldHelper = new CustomFieldHelper();
    }

    /**
     * Get study participant ID field names
     *
     * @return array of study ID field names
     */
    public function getStudyIdFieldNames() {
        $result = array();

        foreach (module_implements('lcbru_populateStudyIds') as $module) {
          $function = $module . '_lcbru_populateStudyIds';
          $function($result);
        }
        asort($result);

        return $result;
    }

    /**
     * Returns whether the field name is a study participant ID
     *
     * @param string $fieldName the potential name of a study ID
     *
     * @return array of study ID field names
     */
    public function isParticipantStudyIdFieldName($fieldName) {
        Guard::AssertString_NotEmpty('$fieldName', $fieldName);

        return in_array($fieldName, $this->getStudyIdFieldNames());
    }

    /**
     * Get select options for CaseTypes.
     *
     * @return array of case type names keyed by ID
     */
    public function getCaseTypeSelectOptions() {
        $result = array();

        foreach (CiviCrmApiHelper::getObjectsAll('CaseType') as $ct) {
          $result[$ct['id']] = $ct['name'];
        }
        asort($result);

        return $result;
    }

    /**
     * Extract case study IDs from an array.
     *
     * @param $participantDetails an array of participants details
     *                with keys as the field names and values of the values
     *
     * @return array of participant study IDs extracted from the input
     */
    public function extractParticipantStudyIds(array $participantDetails, $caseTypeId = NULL) {
        Guard::AssertArray('$participantDetails', $participantDetails);
        if (!is_null($caseTypeId)) {
            Guard::AssertInteger('$caseTypeId', $caseTypeId);
        }

        $result = array();

        foreach ($participantDetails as $key => $value) {
            if ($this->isParticipantStudyIdFieldName($key)) {
                if (is_null($caseTypeId) || $this->customFieldHelper->isCaseCustomField($caseTypeId, $key)) {

                    $result[] = array(
                            'participantStudyIdFieldName' => $key,
                            'participantStudyId' => $value
                        );
                }
            }
        }

        return $result;
    }

    /**
     * @return array of study ID field names
     */
    public function getParticipantStudyIdFieldName($participantStudyId) {
        Guard::AssertString('$participantStudyId', $participantStudyId);

        $result = array();

        foreach (module_implements(CaseHelper::HOOKNAME_GET_STUDY_ID_FIELD_NAME) as $module) {
            $field_name = $this->getModuleParticipantStudyIdFieldName($module, $participantStudyId);
            if ($field_name) {
                $result[$field_name] = $participantStudyId;
            }
        }

        return $result;
    }


    /**
     * @param $module the name of the module for which the study belongs
     * @param $participantStudyId the value of the participant study ID
     *
     * @return string studyIdFieldName
     */
    public function getModuleParticipantStudyIdFieldName($module, $participantStudyId) {
      Guard::AssertString_NotEmpty('$module', $module);
      Guard::AssertString('$participantStudyId', $participantStudyId);
      Guard::AssertModuleImplementsHook($module, CaseHelper::HOOKNAME_GET_STUDY_ID_FIELD_NAME);
      
      return module_invoke($module, CaseHelper::HOOKNAME_GET_STUDY_ID_FIELD_NAME, $participantStudyId);
    }

    /**
     * Gets a case if it already exists
     *
     * @param integer $contactId the contact ID
     * @param integer $caseTypeId the case type ID
     * @param array potentialStudyIds array of potential study IDs
     *
     * @return id of the existing case
     * @throws Exception if $contactId is empty.
     * @throws Exception if $caseTypeId is empty.
     * @throws Exception if $potentialStudyIds is empty.
     * @throws Exception if more than 1 case is found.
     */
    public function getContactSingleCaseOfTypeFromPotentialStudyIds($contactId, $caseTypeId, array $potentialStudyIds, array $excludeCaseStatuses) {
        Guard::AssertInteger('$contactId', $contactId);
        Guard::AssertInteger('$caseTypeId', $caseTypeId);
        Guard::AssertArray('$potentialStudyIds', $potentialStudyIds);
        Guard::AssertArrayOfIntegers('$excludeCaseStatuses', $excludeCaseStatuses);

        $participantStudyIds = $this->extractParticipantStudyIds($potentialStudyIds, $caseTypeId);
        $caseIds = $this->getCaseIdsForParticipantStudyIds($participantStudyIds);
        $cases = $this->getCasesFromCaseIds($caseIds);

        $cases = $this->filterCasesExcludeStatuses($cases, $excludeCaseStatuses);

        foreach ($cases as $c) {
            if (!in_array($contactId, $c['client_id'])) {
                throw new Exception(__FUNCTION__." study for incorrect contact: Study ID Field: $studyParticipantIdField = $studyParticipantId".PHP_EOL.'Contact Id: '.$contactId.PHP_EOL);
            }
        }

        if (count($cases) == 0) {
            return null;
        }

        if (count($cases) > 1) {
            throw new Exception(
                __FUNCTION__.
                " more than one study found: potentialStudyIds = ".
                print_r($potentialStudyIds, true).
                PHP_EOL.
                "Cases are:".
                PHP_EOL.
                print_r($cases, true)
            );
        }

        return array_shift($cases);
    }


    /**
     * Get cases from participant study IDs.
     *
     * @param $participantStudyIds an array of participants study IDs
     *
     * @return array of cases
     */
    public function getCaseIdsForParticipantStudyIds(array $participantStudyIds) {
        Guard::AssertArray_HasColumns('$participantStudyIds', $participantStudyIds, array('participantStudyIdFieldName', 'participantStudyId'));

        $result = array();

        foreach ($participantStudyIds as $psid) {
          if (!empty($psid['participantStudyId'])) {
            $result = array_merge($result, $this->getCaseIdsForParticipantStudyId($psid['participantStudyIdFieldName'], $psid['participantStudyId']));
          }
        }

        return $result;
    }

    /**
     * @param $participantStudyIdFieldName the name of the participant study ID field
     * @param $participantStudyId the value of the participant study ID
     *
     * @return array of case type names keyed by ID
     */
    public function getCaseIdsForParticipantStudyId($participantStudyIdFieldName, $participantStudyId) {
      Guard::AssertString_NotEmpty('$participantStudyIdFieldName', $participantStudyIdFieldName);
      Guard::AssertString('$participantStudyId', $participantStudyId);

      $participantStudyIdField = $this->customFieldHelper->getFieldbyName($participantStudyIdFieldName);

      $query = "
          SELECT cus.entity_id
          FROM {$participantStudyIdField['custom_group_table_name']} cus
          JOIN civicrm_case c ON c.id = cus.entity_id
                            AND c.is_deleted = 0
          WHERE cus.{$participantStudyIdField['column_name']} = %1
          ";

      $dao = CRM_Core_DAO::executeQuery($query,
        array(1 => array($participantStudyId, 'String'))
      );

      $result = array();

      while ($dao->fetch()) {
        $result[] = $dao->entity_id;
      }

      return $result;
    }

    public function getCasesFromCaseIds(array $caseIds, array $params = NULL) {
        Guard::AssertArrayOfIntegers('$caseIds', $caseIds);

        $p = is_null($params) ? array() : $params;

        $result = array();
        
        foreach ($caseIds as $id) {
            $parameters = array_merge($p, array('case_id' => $id));
            $result[] = CiviCrmApiHelper::getObject('case', $parameters);
        }

        return $result;
    }


    public function getCasesForContactIds(array $contactIds, array $params = NULL) {
        Guard::AssertArrayOfIntegers('$contactIds', $contactIds);

        $result = array();

        foreach ($contactIds as $id) {
            $result += $this->getCasesForContactId($id, $params);
        }        

        return $result;
    }


    public function getCasesForContactId($contactId, array $params = NULL) {
        Guard::AssertInteger('$contactId', $contactId);

        $p = is_null($params) ? array() : $params;

        $parameters = array_merge($p, array('contact_id' => $contactId, 'is_deleted' => 0));

        $cases = CiviCrmApiHelper::getObjectsAll('case', $parameters);

        $result = array();

        # Fix because searching for cases by contact ID
        # ignores all other filters!
        foreach ($cases as $c) {
            if (empty($parameters['case_type_id']) || $c['case_type_id'] == $parameters['case_type_id']) {
                $result[] = $c;
            }
        }

        return $this->addMeaningToCases($result);
    }


    public function addMeaningToCases(array $cases) {
        $result = array();

        $caseStatusHelper = new OptionValueHelper(OptionValueHelper::CASE_STATUS);
        $customFieldHelper = new CustomFieldHelper();

        foreach ($cases as $c) {

            $customfields = $customFieldHelper->getEntityCustomData('Case', $c['id']);

            $activities = CiviCrmApiHelper::getObjectsAll('case', array('id' => $c['id'], 'return' => 'activities'));

            $c = array_merge($c, $customfields, array_pop($activities));

            $c['study_ids'] = array();

            foreach ($customfields as $name => $value) {
                if ($this->isParticipantStudyIdFieldName($name)) {
                    $c['study_ids'][$name] = $value;
                }
            }

            $c['case_type_title'] = $this->getCaseTypes()[$c['case_type_id']]['title'];
            $c['case_status_title'] = $caseStatusHelper->getFromValue($c['status_id'])['label'];
            $contact_id = current($c['contact_id']);
            $c['url'] = CaseHelper::getCaseUrl($contact_id, $c['id']);
            $result[$c['id']] = $c;
        }

        return $result;
    }

    public static function getCaseUrl($contact_id, $case_id) {
        Guard::AssertInteger('$contact_id', $contact_id);
        Guard::AssertInteger('$case_id', $case_id);

        return CRM_Utils_System::url(
                "civicrm/contact/view/case",
                "reset=1&action=view&cid={$contact_id}&id={$case_id}"
            );
    }

    public function filterCasesExcludeStatuses(array $cases, array $excludeStatues) {
        $result = array();

        foreach ($cases as $c) {
            if (!in_array($c['status_id'], $excludeStatues)) {
                $result[] = $c;
            }
        }

        return $result;
    }

    public function getCaseFromId($id) {
        Guard::AssertInteger('$id', $id);

        return CiviCrmApiHelper::getObject('case', array(
            'case_id' => $id
            ));
    }

    public function getCaseTypeFromName($name) {
        Guard::AssertString_NotEmpty('$name', $name);

        return CiviCrmApiHelper::getObject('CaseType', array(
            'name' => $name
            ));
    }

    public function getCaseTypes() {
        if (empty($this->_cached_case_types)) {
            $this->_cached_case_types = array();

            foreach (CiviCrmApiHelper::getObjectsAll("CaseType") as $c) {
                $this->_cached_case_types[$c['id']] = $c;
            } ;

        }

        return $this->_cached_case_types;
    }

    public function getCaseFieldsDescription(array $case) {
        $casefields = array();

        if (!empty($case['case_status_title'])) {
            $casefields[] = "<strong>Status</strong>: {$case['case_status_title']}";
        }
        if (!empty($case['start_date'])) {
            $casefields[] = '<strong>Enrolment Date</strong>: ' . date('d-M-Y', strtotime($case['start_date']));
        }

        return join('; &nbsp; ', $casefields);
    }

    public function getCaseTitle(array $case) {
        $casefields = array();

        $study_id_string = join(array_filter($case['study_ids']), ' / ');
        if ($study_id_string) {
            $study_id_string = "($study_id_string)";
        }

        return "{$case['case_type_title']} $study_id_string";
    }

}
