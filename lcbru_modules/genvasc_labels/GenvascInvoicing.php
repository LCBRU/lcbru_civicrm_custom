<?php

/**
 * This class provides utility functions for processing GENVASC invoices
 * 
 * Example usage:
 *
 * <code>
 *
 * // get a number of outstanding participants
 *
 *   $c = GenvascInvoicing::getOutstandingParticipantCount();
 *
 * </code>
 * 
*/

class GenvascInvoicing
{

  public static function getOutstandingParticipants() {
    civicrm_initialize();

    $cfh = new CustomFieldHelper();
    $caseh = new CaseHelper();

    $genvascCaseType = $caseh->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);
    $invYearField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_YEAR');
    $invProcessedField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE');

    $rh = new RelationshipHelper();
    $recruitSiteRelationShip = $rh->getRelationshipTypeFromName(CIVI_REL_RECRUITING_SITE);

    $query = "
        SELECT DISTINCT cas.id
        FROM civicrm_case cas
        LEFT JOIN {$invYearField['custom_group_table_name']} cus
            ON cus.entity_id = cas.id
        JOIN civicrm_relationship practiceRel ON practiceRel.case_id = cas.id
                                AND practiceRel.relationship_type_id = %2
                                AND COALESCE(practiceRel.end_date, CURDATE()) >= CURDATE() 
                                AND COALESCE(practiceRel.start_date, CURDATE()) <= CURDATE() 
        JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
        JOIN civicrm_case_contact cc ON cc.case_id = cas.id
        JOIN civicrm_contact con ON con.id = cc.contact_id
                AND con.is_deleted = 0
        WHERE cas.case_type_id = %1
            AND COALESCE(cus.{$invProcessedField['column_name']}, '') = ''
            AND cas.is_deleted = 0
        ";

    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array($genvascCaseType['id'], 'Integer'),
      2 => array($recruitSiteRelationShip['id'], 'Integer')
    ));

    $caseIds = array();
    while ($dao->fetch()) {
      $caseIds[] = $dao->id;
    }
    $caseHelper = new CaseHelper();
    return $caseHelper->getCasesFromCaseIds($caseIds);
  }

  public static function getOutstandingParticipantCount() {
    civicrm_initialize();

    $cfh = new CustomFieldHelper();
    $caseh = new CaseHelper();

    $genvascCaseType = $caseh->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);
    $invYearField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_YEAR');
    $invProcessedField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE');

    $rh = new RelationshipHelper();
    $recruitSiteRelationShip = $rh->getRelationshipTypeFromName(CIVI_REL_RECRUITING_SITE);

    $query = "
        SELECT COUNT(DISTINCT cas.id) as count
        FROM civicrm_case cas
        LEFT JOIN {$invYearField['custom_group_table_name']} cus
            ON cus.entity_id = cas.id
        JOIN civicrm_relationship practiceRel ON practiceRel.case_id = cas.id
                                AND practiceRel.relationship_type_id = %2
                                AND COALESCE(practiceRel.end_date, CURDATE()) >= CURDATE() 
                                AND COALESCE(practiceRel.start_date, CURDATE()) <= CURDATE() 
        JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
        JOIN civicrm_case_contact cc ON cc.case_id = cas.id
        JOIN civicrm_contact con ON con.id = cc.contact_id
                AND con.is_deleted = 0
        WHERE cas.case_type_id = %1
            AND COALESCE(cus.{$invProcessedField['column_name']}, '') = ''
            AND cas.is_deleted = 0
        ";

    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array($genvascCaseType['id'], 'Integer'),
      2 => array($recruitSiteRelationShip['id'], 'Integer')
    ));

    $result = array();
    $dao->fetch();
    return $dao->count;
  }

  public static function getInvoiceSummary() {
    civicrm_initialize();

    $cfh = new CustomFieldHelper();
    $caseh = new CaseHelper();

    $genvascCaseType = $caseh->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);
    $invYearField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_YEAR');
    $invQuarterField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_QUARTER');

    $rh = new RelationshipHelper();
    $recruitSiteRelationShip = $rh->getRelationshipTypeFromName(CIVI_REL_RECRUITING_SITE);

    $query = "
        SELECT
            cus.{$invYearField['column_name']} AS invoice_year
          , cus.{$invQuarterField['column_name']} AS invoice_quarter
          , COUNT(*) AS participant_count
        FROM civicrm_case cas
        JOIN {$invYearField['custom_group_table_name']} cus
            ON cus.entity_id = cas.id
        JOIN civicrm_relationship practiceRel ON practiceRel.case_id = cas.id
                                AND practiceRel.relationship_type_id = %2
                                AND COALESCE(practiceRel.end_date, CURDATE()) >= CURDATE() 
                                AND COALESCE(practiceRel.start_date, CURDATE()) <= CURDATE() 
        JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
        WHERE cas.case_type_id = %1
            AND TRIM(COALESCE(cus.{$invYearField['column_name']}, '')) != ''
        GROUP BY cus.{$invYearField['column_name']}, cus.{$invQuarterField['column_name']}
        ";

    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array($genvascCaseType['id'], 'Integer'),
      2 => array($recruitSiteRelationShip['id'], 'Integer')
    ));

      $result = array();

      while ($dao->fetch()) {
        $result[] = ArrayHelper::objectToArray($dao);
      }

      return $result;
   }

  public static function getInvoicePracticeSummary($invoice_year, $invoice_quarter) {
    Guard::AssertString_NotEmpty('$invoice_year', $invoice_year);
    Guard::AssertString_NotEmpty('$invoice_quarter', $invoice_quarter);

    civicrm_initialize();

    $cfh = new CustomFieldHelper();
    $caseh = new CaseHelper();

    $genvascCaseType = $caseh->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);
    $invYearField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_YEAR');
    $invQuarterField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_QUARTER');

    $rh = new RelationshipHelper();
    $recruitSiteRelationShip = $rh->getRelationshipTypeFromName(CIVI_REL_RECRUITING_SITE);

    $query = "
        SELECT
            practice.id AS practice_id
          , practice.organization_name practice
          , COUNT(*) AS participant_count
        FROM civicrm_case cas
        JOIN {$invYearField['custom_group_table_name']} cus
            ON cus.entity_id = cas.id
        JOIN civicrm_relationship practiceRel ON practiceRel.case_id = cas.id
                                AND practiceRel.relationship_type_id = %2
                                AND COALESCE(practiceRel.end_date, CURDATE()) >= CURDATE() 
                                AND COALESCE(practiceRel.start_date, CURDATE()) <= CURDATE() 
        JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
        WHERE cas.case_type_id = %1
            AND cus.{$invYearField['column_name']} = %3
            AND cus.{$invQuarterField['column_name']} = %4
        GROUP BY
            cus.{$invYearField['column_name']},
            cus.{$invQuarterField['column_name']},
            practice.organization_name,
            practice.id
        ";

    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array($genvascCaseType['id'], 'Integer'),
      2 => array($recruitSiteRelationShip['id'], 'Integer'),
      3 => array($invoice_year, 'String'),
      4 => array($invoice_quarter, 'String'),
      )
    );

    $result = array();

    while ($dao->fetch()) {
      $result[] = ArrayHelper::objectToArray($dao);
    }

    return $result;
   }

  public static function getInvoicePracticeParticipants($invoice_year, $invoice_quarter, $practice_id) {
    Guard::AssertString_NotEmpty('$invoice_year', $invoice_year);
    Guard::AssertString_NotEmpty('$invoice_quarter', $invoice_quarter);
    Guard::AssertInteger('$practice_id', $practice_id);

    civicrm_initialize();

    $cfh = new CustomFieldHelper();
    $caseh = new CaseHelper();

    $genvascCaseType = $caseh->getCaseTypeFromName(CIVI_CASE_TYPE_GENVASC);
    $invYearField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_YEAR');
    $invQuarterField = $cfh->getFieldbyName('CIVI_FIELD_GENVASC_INVOICE_QUARTER');
    $genvascIdField = $cfh->getFieldbyName(str_replace(" ","_",CIVI_FIELD_GENVASC_ID));

    $rh = new RelationshipHelper();
    $recruitSiteRelationShip = $rh->getRelationshipTypeFromName(CIVI_REL_RECRUITING_SITE);

    $query = "
        SELECT
            participant.display_name
          , gen.{$genvascIdField['column_name']} AS gpt_number
          , participant.id AS contact_id
          , cas.id AS case_id
        FROM civicrm_case cas
        JOIN {$invYearField['custom_group_table_name']} cus
            ON cus.entity_id = cas.id
        JOIN civicrm_case_contact cas_con ON cas_con.case_id = cas.id
        JOIN civicrm_contact participant ON participant.id = cas_con.contact_id
        JOIN {$genvascIdField['custom_group_table_name']} gen ON gen.entity_id = cas.id
        JOIN civicrm_relationship practiceRel ON practiceRel.case_id = cas.id
                                AND practiceRel.relationship_type_id = %2
                                AND COALESCE(practiceRel.end_date, CURDATE()) >= CURDATE() 
                                AND COALESCE(practiceRel.start_date, CURDATE()) <= CURDATE() 
        JOIN civicrm_contact practice ON practice.id = practiceRel.contact_id_b
        WHERE cas.case_type_id = %1
            AND cus.{$invYearField['column_name']} = %3
            AND cus.{$invQuarterField['column_name']} = %4
            AND practice.id = %5
        ";

    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array($genvascCaseType['id'], 'Integer'),
      2 => array($recruitSiteRelationShip['id'], 'Integer'),
      3 => array($invoice_year, 'String'),
      4 => array($invoice_quarter, 'String'),
      5 => array($practice_id, 'Integer'),
      )
    );

    $result = array();

    while ($dao->fetch()) {
      $result[] = ArrayHelper::objectToArray($dao);
    }

    return $result;
   }

  public static function invoiceOutstandingParticipants($invoice_year, $invoice_quarter) {
    Guard::AssertString_NotEmpty('$invoice_year', $invoice_year);
    Guard::AssertString_NotEmpty('$invoice_quarter', $invoice_quarter);

    civicrm_initialize();
    global $user;

    $actH = new ActivityHelper();
    $cfh = new CustomFieldHelper();
    $caseStatusH = new OptionValueHelper(OptionValueHelper::CASE_STATUS);
    $activityStatusH = new OptionValueHelper(OptionValueHelper::ACTIVITY_STATUS);
    $excludedCaseStatusId = $caseStatusH->getValueFromLabel(CIVI_CASE_EXCLUDED);
    $actTypeH = new OptionValueHelper(OptionValueHelper::ACTIVITY_TYPE);
    $submittedReimbActivityType = $actTypeH->getValueFromLabel(CIVI_ACTIVITY_SUBMITTED_FOR_REIMBURSEMENT);
    $excludedCaseStatusId = $caseStatusH->getValueFromLabel(CIVI_CASE_EXCLUDED);
    $completedStatusId = $activityStatusH->getValueFromLabel(CIVI_ACTIVITY_STATUS_COMPLETED);
    $notRequiredStatusId = $activityStatusH->getValueFromLabel(CIVI_ACTIVITY_STATUS_NOT_REQUIRED);

    $cases = GenvascInvoicing::getOutstandingParticipants();

    foreach ($cases as $c) {
      $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_YEAR', $invoice_year);
      $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_QUARTER', $invoice_quarter);

      if ($c['status_id'] == $excludedCaseStatusId) {
        $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_REIMBURSED_STATUS', 'No (Paticipant Excluded)');
      } else {
        $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_REIMBURSED_STATUS', 'Yes');
      }

      $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_PROCESSED_BY', $user->name);
      $cfh->saveValue($c['id'], 'CIVI_FIELD_GENVASC_INVOICE_PROCESSED_DATE', date("Ymd"));

      // I have to do this in 2 lines because PHP is so
      // useless that it errors if you do it in one!
      $relaventActivities = $actH->getActivitiesFromIds($c['activities'], array('activity_type_id' => $submittedReimbActivityType));
      $submittedForReimbursement = array_pop($relaventActivities);

      if ($c['status_id'] == $excludedCaseStatusId) {
        $submittedForReimbursement['status_id'] = $notRequiredStatusId;
      } else {
        $submittedForReimbursement['status_id'] = $completedStatusId;
      }
      $submittedForReimbursement['activity_date_time'] = date("Ymd");

      $actH->saveActivity($submittedForReimbursement);
    }

  }
}

