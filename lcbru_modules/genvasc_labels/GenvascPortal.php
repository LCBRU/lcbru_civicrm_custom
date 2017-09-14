<?php

/**
 * This class provides utility functions for processing portal recruits
 * 
 * Example usage:
 *
 * <code>
 *
 * // submit outstanding participants to daps
 *
 *   $portal = new GenvascPortal();
 *   $portal->doDapsSubmission();
 *
 * </code>
 * 
*/

class GenvascPortal
{

  public function doDapsSubmission() {
    $recruits = $this->getRecruitsnotSubmittedToDaps();

    $daps = new Daps();
    $daps->submitAndTrack($recruits, 'recruit_id');

    foreach ($recruits as $r) {
    
      db_query('
        UPDATE genvasc_portal_recruits
        SET daps_submission_participant_id = :daps_submission_participant_id
        WHERE id = :recruit_id
        ', array(
            ':daps_submission_participant_id' => $r['daps_submission_participant_id'],
            ':recruit_id' => $r['recruit_id']
            ));

    }
   }

  private function getRecruitsnotSubmittedToDaps() {
    return ArrayHelper::objectToArray(db_query('
      SELECT
          ID AS recruit_id
        , nhs_number AS \''.CIVI_FIELD_NHS_NUMBER.'\'
        , dob AS birth_date
      FROM genvasc_portal_recruits
      WHERE daps_submission_participant_id IS NULL
        AND date_processed IS NULL
      ')->fetchAll());
  }

  public static function getOutstandingRecruits() {
    return db_query('
      SELECT
           gr.id AS genvasc_port_recruits_id
         , gr.practice_id
         , gr.nhs_number
         , gr.dob
         , gr.date_recruited
         , CASE
          WHEN ds.date_returned IS NOT NULL AND LENGTH(TRIM(CONCAT(COALESCE(dp.response_forenames, \'\'), COALESCE(dp.response_forenames, \'\')))) = 0 THEN \'Demographics failed\'
          WHEN ds.date_returned IS NOT NULL THEN \'Demographics Returned\'
          WHEN ds.date_abandoned IS NOT NULL THEN \'Demographics Abandoned\'
          WHEN ds.date_sent IS NOT NULL THEN \'Awaiting Demographics\'
          WHEN ds.date_submitted IS NULL THEN \'\'
          ELSE
            \'Submitted\'
          END AS status
      FROM genvasc_portal_recruits gr
      LEFT JOIN daps_submission_participant dp ON dp.id = gr.daps_submission_participant_id
      LEFT JOIN daps_submission ds ON ds.id = dp.daps_submission_id
      WHERE date_processed IS NULL
      ')->fetchAll();
  }

  public static function markRecruitProcessed($recruit_id, $contact_id, $case_id) {
    Guard::AssertString_NotEmpty('$recruit_id', $recruit_id);
    Guard::AssertInteger('$contact_id', $contact_id);
    Guard::AssertInteger('$case_id', $case_id);

    db_query('
      UPDATE genvasc_portal_recruits
      SET contact_id = :contact_id,
          case_id = :case_id,
          date_processed = CURDATE()
      WHERE id = :recruit_id
      ', array(
          ':contact_id' => $contact_id,
          ':case_id' => $case_id,
          ':recruit_id' => $recruit_id
    ));
   }

  public static function markRecruitDeleted($recruit_id, $reason) {
    Guard::AssertString_NotEmpty('$reason', $reason);

    global $user;

    db_query('
      UPDATE genvasc_portal_recruits
      SET delete_reason = :reason,
          date_processed = CURDATE(),
          delete_date = NOW(),
          delete_user = :user
      WHERE id = :recruit_id
      ', array(
          ':reason' => $reason,
          ':recruit_id' => $recruit_id,
          ':user' => $user->name
    ));
   }

  public static function createNewRecruit($nhs_number, $practice_id, $date_of_birth, $date_recruited) {
    Guard::AssertString_NotEmpty('$nhs_number', $nhs_number);
    Guard::AssertInteger('$practice_id', $practice_id);
    Guard::AssertString_NotEmpty('$date_of_birth', $date_of_birth);
    Guard::AssertString_NotEmpty('$date_recruited', $date_recruited);

    global $user;

    db_query('
      INSERT INTO genvasc_portal_recruits (
        id,
        practice_id,
        nhs_number,
        dob,
        date_recruited,
        create_date,
        create_user
      )
      VALUES (
        UUID(),
        :practice_id,
        :nhs_number,
        :dob,
        :date_recruited,
        NOW(),
        :user
      );
      ', array(
          ':practice_id' => $practice_id,
          ':nhs_number' => $nhs_number,
          ':dob' => $date_of_birth,
          ':date_recruited' => $date_recruited,
          ':user' => $user->name
    ));
   }

  public static function get_new_recruit_details($id) {
    return ArrayHelper::objectToArray(db_query('
      SELECT
           id
         , practice_id
         , nhs_number
         , dob
         , date_recruited
      FROM genvasc_portal_recruits gr
      WHERE id = :id
      ', array(
        ':id' => $id
        ))->fetchObject());
  }

  public static function get_new_recruit_daps($id) {
    $new_recruit = ArrayHelper::objectToArray(db_query('
      SELECT
           id
         , practice_id
         , daps_submission_participant_id
         , date_processed
         , contact_id
         , case_id
         , nhs_number
      FROM genvasc_portal_recruits gr
      WHERE id = :id
      ', array(
        ':id' => $id
        ))->fetchObject());

    $daps = new Daps();
    $daps_details = $daps->getReturnedParticipant($new_recruit['daps_submission_participant_id']);
    $combined_details = array_merge($new_recruit, $daps_details);
    $translated_details = ArrayHelper::translateKeys($combined_details, array(
        'response_gender' => 'gender',
        'response_date_of_birth' => 'birth_date',
        'response_uhl_s_number' => ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME,
        'nhs_number' => ContactHelper::NHS_NUMBER_FIELD_NAME,
        'response_title' => 'title',
        'response_forenames' => 'first_name',
        'response_surname' => 'last_name',
        'response_date_of_death' => 'deceased_date',
        'response_is_deceased' => 'is_deceased',
      ));
    $translated_details['title'] = ucwords(strtolower($translated_details['title']));
    $translated_details['first_name'] = ucwords(strtolower($translated_details['first_name']));
    $translated_details['last_name'] = ucwords(strtolower($translated_details['last_name']));
    $translated_details['address'] = addressSplit(array(
        $daps_details['response_address_1'],
        $daps_details['response_address_2'],
        $daps_details['response_address_3'],
        $daps_details['response_address_4'],
        $daps_details['response_address_5'],
        $daps_details['response_postcode'],
      ));

    return $translated_details;
  }

}

