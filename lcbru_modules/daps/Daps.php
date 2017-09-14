<?php

/**
 * This class provides utility functions for printing labels
 * 
 * Example usage:
 *
 * <code>
 *
 * // submit participants to daps
 *
 *   $daps = new Daps();
 *   $daps->submit($participants);
 *
 * </code>
 * 
*/

class Daps
{

  const DAPS_TABLE_SUBMISSION = 'daps_submission';
  const DAPS_TABLE_SUBMISSION_PARTICIPANT = 'daps_submission_participant';

  public function submitAndTrack(array &$contacts, $identifier_column_name) {
    Guard::AssertString_NotEmpty('$identifier_column_name', $identifier_column_name);

    if (count($contacts) == 0) {
      return;
    }
    
    try {
      $outstream = fopen("php://temp", 'r+');

      $submission_id = $this->createSubmission();

      foreach ($contacts as &$c) {
        $daps_submission_participant_id = $this->createContactSubmission($submission_id, $c, $identifier_column_name);
        $c['daps_submission_participant_id'] = $daps_submission_participant_id;
        $c['local_id'] = $daps_submission_participant_id;
      }

      $this->writeCsv($outstream, $contacts);
      $this->ftpData($outstream, $this->getSubmissionFilename($submission_id));

      $this->markSubmitted($submission_id);

    } finally {
      fclose($outstream);
    }

  }

  private function getSubmissionFilename($submission_id) {
    Guard::AssertString_NotEmpty('$submission_id', $submission_id);
    return "CiviCRM_DAPS_TST1_$submission_id.csv";
  }

  public function submit(array $contacts, $filename) {
      Guard::AssertString_NotEmpty('$filename', $filename);

      try {
        $outstream = fopen("php://temp", 'r+');

        $this->writeCsv($outstream, $contacts);
        $this->ftpData($outstream, $filename);

      } finally {
        fclose($outstream);
      }

  }

  public function download(array $contacts, $filename) {
      Guard::AssertString_NotEmpty('$filename', $filename);
      
      try {
        $outstream = fopen("php://temp", 'r+');

        $this->writeCsv($outstream, $contacts);
        $this->downloadStream($outstream, $filename);

      } finally {
        fclose($outstream);
      }

  }

  private function writeCsv($stream, array $contacts) {

    $headings = array(
          'FORENAMES',
          'SURNAME',
          'DOB',
          'SEX',
          'POSTCODE',
          'NHS_NUMBER',
          'SYSTEM_NUMBER',
          'ADDRESS1',
          'ADDRESS2',
          'ADDRESS3',
          'ADDRESS4',
          'ADDRESS5',
          'LOCAL_ID',
          );

    $csvData = array();

    foreach ($contacts as $c) {
      $csvData[] = $this->createCsvEntry($c);
    }

    $this->mssafe_csv($stream, $csvData, $headings);

    rewind($stream);
    
  }

  private function createSubmission() {
    $id = lcbru_get_GUID();

    db_insert(Daps::DAPS_TABLE_SUBMISSION)
            ->fields(array(
              'id' => $id,
              'date_created' => date('Y-m-d H:i:s'),
            ))
            ->execute();

    return $id;
  }

  private function markSubmitted($submission_id) {
    Guard::AssertString_NotEmpty('$submission_id', $submission_id);

    db_query('
      UPDATE '.Daps::DAPS_TABLE_SUBMISSION.'
      SET date_submitted = CURDATE()
      WHERE id = :submission_id
      ', array(
          ':submission_id' => $submission_id
          ));
  }

  private function createContactSubmission($submission_id, $contact, $identifier_column_name) {
    Guard::AssertString_NotEmpty('$submission_id', $submission_id);
    Guard::AssertString_NotEmpty('$identifier_column_name', $identifier_column_name);

    $id = lcbru_get_GUID();
    $id = str_replace('{', '', $id);
    $id = str_replace('}', '', $id);
    $id = str_replace('-', '', $id);

    db_insert(Daps::DAPS_TABLE_SUBMISSION_PARTICIPANT)
            ->fields(array(
              'id' => $id,
              'daps_submission_id' => $submission_id,
              'identifier' => $contact[$identifier_column_name]
            ))
            ->execute();

    return $id;
  }

 private function createCsvEntry(array $contact) {
    $formatted_address = AddressHelper::getDapsAddress($contact);

    return array(
      'FORENAMES' => ArrayHelper::get($contact, 'first_name', ''),
      'SURNAME' => ArrayHelper::get($contact, 'last_name', ''),
      'DOB' => ArrayHelper::get($contact, 'birth_date', ''),
      'SEX' => ArrayHelper::get($contact, 'gender', ''),
      'POSTCODE' => ArrayHelper::get($contact, 'postal_code', ''),
      'NHS_NUMBER' => ArrayHelper::get($contact, CIVI_FIELD_NHS_NUMBER, ''),
      'SYSTEM_NUMBER' => ArrayHelper::get($contact, CIVI_FIELD_S_NUMBER, ''),
      'ADDRESS1' => ArrayHelper::get($formatted_address, 'ADDRESS1', ''),
      'ADDRESS2' => ArrayHelper::get($formatted_address, 'ADDRESS2', ''),
      'ADDRESS3' => ArrayHelper::get($formatted_address, 'ADDRESS3', ''),
      'ADDRESS4' => ArrayHelper::get($formatted_address, 'ADDRESS4', ''),
      'ADDRESS5' => ArrayHelper::get($formatted_address, 'ADDRESS5', ''),
      'LOCAL_ID' => ArrayHelper::get($contact, 'local_id', ArrayHelper::get($contact, 'contact_id', '')),
      );
  }

  private function mssafe_csv($fp, $data, $header = array())
  {
    $show_header = true;
    if ( empty($header) ) {
        $show_header = false;
        reset($data);
        $line = current($data);
        if ( !empty($line) ) {
            reset($line);
            $first = current($line);
            if ( substr($first, 0, 2) == 'ID' && !preg_match('/["\\s,]/', $first) ) {
                array_shift($data);
                array_shift($line);
                if ( empty($line) ) {
                    fwrite($fp, "\"{$first}\"\r\n");
                } else {
                    fwrite($fp, "\"{$first}\",");
                    fputcsv($fp, $line);
                    fseek($fp, -1, SEEK_CUR);
                    fwrite($fp, "\r\n");
                }
            }
        }
    } else {
        reset($header);
        $first = current($header);
        if ( substr($first, 0, 2) == 'ID' && !preg_match('/["\\s,]/', $first) ) {
            array_shift($header);
            if ( empty($header) ) {
                $show_header = false;
                fwrite($fp, "\"{$first}\"\r\n");
            } else {
                fwrite($fp, "\"{$first}\",");
            }
        }
    }
    if ( $show_header ) {
        fputcsv($fp, $header);
        fseek($fp, -1, SEEK_CUR);
        fwrite($fp, "\r\n");
    }
    foreach ( $data as $line ) {
        fputcsv($fp, $line);
        fseek($fp, -1, SEEK_CUR);
        fwrite($fp, "\r\n");
    }
  } 

  private function ftpData($stream, $filename) {

    $conn_id = ftp_connect(CIVI_DAPS_FTP_CONNECTION_SERVER);

    if (!$conn_id) {
        throw new Exception("Could not connect to the DAPS server.");
    }

    try {
      if (!ftp_login($conn_id, CIVI_DAPS_FTP_CONNECTION_USER, CIVI_DAPS_FTP_CONNECTION_PASSWORD)) {
          throw new Exception("Could not log in to the DAPS server.");
      }

      if (!ftp_chdir($conn_id, "briccs")) {
          throw new Exception("Could not change into the briccs directory.");
      }

      if (!ftp_pasv($conn_id, true)) {
          throw new Exception("Could not switch to passive mode.");
      }

      if (!ftp_fput($conn_id, $filename, $stream, FTP_BINARY)) {
        throw new Exception("Transfer of file to DAPS server failed.");
      }
    } catch (Exception $ex) {
      throw $ex;
    }
     finally {
      ftp_close($conn_id);
    }

  }

  private function downloadStream($stream, $filename) {
    rewind($stream);

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    echo stream_get_contents($stream);

    die();
  }

  public function import() {
    $outstandingDapsRequests = ArrayHelper::objectToArray(db_query('
      SELECT ID AS submission_id
      FROM '.Daps::DAPS_TABLE_SUBMISSION.'
      WHERE date_returned IS NULL
        AND date_abandoned IS NULL
      ')->fetchAll());

    foreach ($outstandingDapsRequests as $r) {
      $daps_request_details = $this->getDapsReturnedDetailsFor($r['submission_id']);

      if (!empty($daps_request_details['sent_date'])) {
        db_query('
          UPDATE '.Daps::DAPS_TABLE_SUBMISSION.'
          SET date_sent = :sent_date
          WHERE id = :submission_id
          ', array(
              ':submission_id' => $r['submission_id'],
              ':sent_date' => $daps_request_details['sent_date']
              ));
      }

      if (!empty($daps_request_details['returned_date'])) {
        foreach ($this->getDapsReturnedPatientsFor($daps_request_details['batch_id']) as $p) {
          db_query('
            UPDATE '.Daps::DAPS_TABLE_SUBMISSION_PARTICIPANT.'
            SET
                response_gender = :gender
              , response_date_of_birth = :date_of_birth
              , response_uhl_s_number = :uhl_system_number
              , response_nhs_number = :nhs_number
              , response_title = :title
              , response_forenames = :forenames
              , response_surname = :surname
              , response_address_1 = :address_line_1
              , response_address_2 = :address_line_2
              , response_address_3 = :address_line_3
              , response_address_4 = :address_line_4
              , response_address_5 = :address_line_5
              , response_postcode = :postcode
              , response_date_of_death = :date_of_death
              , response_is_deceased = :is_deceased
            WHERE id = :daps_submission_participant_id
            ', array(
                ':gender' => $p['gender'],
                ':date_of_birth' => $p['date_of_birth'],
                ':uhl_system_number' => $p['uhl_system_number'],
                ':nhs_number' => $p['nhs_number'],
                ':title' => $p['title'],
                ':forenames' => $p['forenames'],
                ':surname' => $p['surname'],
                ':address_line_1' => $p['address_line_1'],
                ':address_line_2' => $p['address_line_2'],
                ':address_line_3' => $p['address_line_3'],
                ':address_line_4' => $p['address_line_4'],
                ':address_line_5' => $p['address_line_5'],
                ':postcode' => $p['postcode'],
                ':date_of_death' => $p['date_of_death'],
                ':is_deceased' => $p['is_deceased'],
                ':daps_submission_participant_id' => $p['daps_submission_participant_id']
                ));
        }

        db_query('
          UPDATE '.Daps::DAPS_TABLE_SUBMISSION.'
          SET date_returned = :date_returned
          WHERE id = :submission_id
          ', array(
              ':submission_id' => $r['submission_id'],
              ':date_returned' => $daps_request_details['returned_date']
              ));
      }
    }
  }

  public function abandonOldSubmissions() {
    db_query('
      UPDATE '.Daps::DAPS_TABLE_SUBMISSION.'
      SET date_abandoned = CURDATE()
      WHERE date_returned IS NULL
        AND date_abandoned IS NULL
        AND date_submitted < DATE_SUB(CURDATE(),INTERVAL 15 DAY)
      ')->execute();
  }

  private function getDapsReturnedDetailsFor($submission_id) {
    Guard::AssertString_NotEmpty('$submission_id', $submission_id);

    db_set_active('daps');
    try {
      $result = ArrayHelper::objectToArray(db_query('
        SELECT
            b.ID AS batch_id
          , b.FILENAME AS request_filename
          , b.LOAD_DATE AS load_date
          , CONVERT(VARCHAR(100), f.DATESENT, 126) AS sent_date
          , CONVERT(VARCHAR(100), f.DATERECV, 126) AS returned_date
        FROM DBS_TRACING_BATCH b
        JOIN DBS_TRACING_FILES f ON f.BATCH_ID = b.ID
        WHERE b.FILENAME LIKE \'%\' + :filename
        ', array(':filename' => $this->getSubmissionFilename($submission_id))
        )->fetchAll());
    } finally {
      db_set_active();
    }

    if (count($result) == 0) {
      return null;
    }
    
    return $result[0];
  }

  private function getDapsReturnedPatientsFor($batch_id) {
    Guard::AssertInteger('$batch_id', $batch_id);

    db_set_active('daps');
    try {
      $result = ArrayHelper::objectToArray(db_query('
          SELECT
              LOCAL_ID AS daps_submission_participant_id
            , SYSTEM_NUMBER_CURRENT AS uhl_system_number
            , NHS_NUMBER AS nhs_number
            , GENDER AS gender
            , DBS_FORENAMES AS forenames
            , DBS_SURNAME AS surname
            , CONVERT(VARCHAR(8), [DATE OF BIRTH], 112) AS date_of_birth
            , DBS_TITLE AS title
            , DBS_ADDRESS_LINE_1 AS address_line_1
            , DBS_ADDRESS_LINE_2 AS address_line_2
            , DBS_ADDRESS_LINE_3 AS address_line_3
            , DBS_ADDRESS_LINE_4 AS address_line_4
            , DBS_ADDRESS_LINE_5 AS address_line_5
            , DBS_POSTCODE AS postcode
            , DATE_OF_DEATH AS date_of_death
            , CASE WHEN NSTS_RETURNED_CURRENT_CENTRAL_REGISTER_POSTING = \'D\' THEN 1 ELSE 0 END AS is_deceased
          FROM DBS_TRACING
          WHERE batch_id = :batch_id
            AND LEN(COALESCE(LOCAL_ID, \'\')) > 0
        ', array(':batch_id' => $batch_id))
        ->fetchAll());
    } finally {
      db_set_active();
    }

    return $result;
  }

  public function getReturnedParticipant($daps_submission_participant_id) {
    Guard::AssertString_NotEmpty('$daps_submission_participant_id', $daps_submission_participant_id);

    $result = ArrayHelper::objectToArray(db_query('
      SELECT
          response_gender
        , response_date_of_birth
        , response_uhl_s_number
        , response_nhs_number
        , response_title
        , response_forenames
        , response_surname
        , response_address_1
        , response_address_2
        , response_address_3
        , response_address_4
        , response_address_5
        , response_postcode
        , response_date_of_death
        , response_is_deceased
      FROM '.Daps::DAPS_TABLE_SUBMISSION_PARTICIPANT.'
      WHERE id = :daps_submission_participant_id
      ', array(':daps_submission_participant_id' => $daps_submission_participant_id))
      ->fetchObject());

    return $result;
  }
}
