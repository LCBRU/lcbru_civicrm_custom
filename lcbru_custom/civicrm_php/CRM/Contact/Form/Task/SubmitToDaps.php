 <?php

class CRM_Contact_Form_Task_SubmitToDaps extends CRM_Contact_Form_Task {

	function preProcess() {
		parent::preProcess();
	}

	function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Submit to DAPS'));

    $this->add('static', 'contact_count', NULL, count($this->_contactIds));

    $this->add('text', 'file_name', ts('DAPS export file name'));
    $this->add('checkbox', 'dowload_only', ts('Download File Only'));

    $this->addDefaultButtons(ts('Submit to DAPS'));
  }

	public function postProcess( ) {
      $transaction = new CRM_Core_Transaction();

      $params =$this->controller->exportValues();

      $sensibleContactIdsArray = array();

      if (is_array($this->_contactIds)) {
          $sensibleContactIdsArray = $this->_contactIds;
      } else {
        $sensibleContactIdsArray[] = $this->_contactIds;
      }

      try {
        $outstream = fopen("php://temp", 'r+');

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

        foreach ($this->_contactIds as $contactId) {
          $csvData[] = self::createCsvEntry($contactId);
        }

        self::mssafe_csv($outstream, $csvData, $headings);

        rewind($outstream);
        
        $filename = self::buildFilename($params['file_name']);

        if ($params['dowload_only']) {
          self::downloadStream($outstream, $filename);
        } else {
          self::ftpData($outstream, $filename);
    
          drupal_set_message("Patient details submitted to DAPS in file $filename.");
        }
      } catch (Exception $ex) {
        drupal_set_message("ERROR: $ex.message", "error");
      } finally {
        fclose($outstream);
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

  private function buildFilename($userFilename) {
      if (empty(trim($userFilename))) {
        $result = "CiviCRM_Export_";
      } else {
        $result = trim($userFilename) . "_";
      }

      $result .= date(DATE_W3C);
      $result = preg_replace("([^\w\s\d\-_~,;\[\]\(\]]|[\.]{2,})", '', $result);
      $result .= '.csv';

      return $result;
  }

  private function createCsvEntry($contactID) {
    $values = array();

    $contact = get_civi_contact($contactID);

    return array(
      'FORENAMES' => $contact['first_name'],
      'SURNAME' => $contact['last_name'],
      'DOB' => $contact['birth_date'],
      'SEX' => $contact['gender'],
      'POSTCODE' => $contact['postal_code'],
      'NHS_NUMBER' => self::getNhsNumber($contactID),
      'SYSTEM_NUMBER' => self::getSNumber($contactID),
      'ADDRESS1' => $contact['supplemental_address_1'],
      'ADDRESS2' => $contact['street_address'],
      'ADDRESS3' => $contact['supplemental_address_2'],
      'ADDRESS4' => $contact['city'],
      'ADDRESS5' => $contact['state_province_name'],
      'LOCAL_ID' => $contactID,
      );
  }

  function mssafe_csv($fp, $data, $header = array())
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

	private function getSNumber($contactID) {
   		$sNumberField = get_civi_custom_value($contactID, CIVI_FIELD_SET_IDENTIFIERS, CIVI_FIELD_S_NUMBER);
   		return $sNumberField['latest'];
	}

  private function getNhsNumber($contactID) {
      $sNumberField = get_civi_custom_value($contactID, CIVI_FIELD_SET_IDENTIFIERS, CIVI_FIELD_NHS_NUMBER);
      return $sNumberField['latest'];
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
}