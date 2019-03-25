 <?php

class CRM_Contact_Form_Task_PmiAddressCheck extends CRM_Contact_Form_Task {

	private $contactIdsForAddressImport;
	private $contactIdsForDeceasedFlagImport;
	private $missingNhsNumberDetails;
	private $missingDOBDetails;

	function preProcess() {
		parent::preProcess();
	}

	function buildQuickForm() {
    	CRM_Utils_System::setTitle(ts('PMI Address Check'));

        $ph = new PmiHelper();
        
    	$this->contactIdsForAddressImport = array();
    	$this->contactIdsForDeceasedFlagImport = array();
    	$this->missingNhsNumberDetails = array();
    	$this->missingDOBDetails = array();

   		$counts = array(
			"contacts" => 0,
			"address_missing" => 0,
			"not_in_pmi" => 0,
			"address_missing_and_in_pmi" => 0,
			"address_different" => 0,
			"newly_deceased" => 0,
			"deceased_mismatch" => 0,
			"missing_nhs_number" => 0,
			"nhs_number_mismatch" => 0,
			"missing_dob" => 0,
			"dob_mismatch" => 0,
   			);

   		$sensibleContactIdsArray = array();

		if (is_array($this->_contactIds)) {
	   		$sensibleContactIdsArray = $this->_contactIds;
		} else {
			$sensibleContactIdsArray[] = $this->_contactIds;
		}

		$ch = new ContactHelper();

		foreach ($this->_contactIds as $cId) {
   			$counts["contacts"]++;
			$contact = $ch->getSubjectFromId($cId);

			if ($contact['UHL S number']) {
				$pmiLookupID = $contact['UHL S number'];
			} else {
				$pmiLookupID = $contact['NHS number'];
			}

			$pmiDetails = $ph->get_pmi_details($pmiLookupID);

   			$pmiDeceased = FALSE;
   			$civiDeceased = $contact['is_deceased'] == 1;
   			$pmiName = "";
			$pmiNhsNumber = "";
			$pmiDOB = "";
			if ($contact['birth_date']){
				$contactDOB = date_format(date_create($contact['birth_date']), "j M Y");
			} else {
				$contactDOB = "";
			}


   			$pmiAddress = array();
   			$civiAddress = array();

   			if (!empty($contact["address_id"])) {
   				$civiAddress = array_values(array_filter(array(
   						$contact["street_address"],
   						$contact["supplemental_address_1"],
   						$contact["supplemental_address_2"],
   						$contact["city"],
   						$contact["postal_code"]
   						)));
   			}

   			if (!is_null($pmiDetails)) {
   				$pmiAddress = array_values(array_filter(array(
   						ucwords(strtolower($pmiDetails["pat_addr1"])),
   						ucwords(strtolower($pmiDetails["pat_addr2"])),
   						ucwords(strtolower($pmiDetails["pat_addr3"])),
   						ucwords(strtolower($pmiDetails["pat_addr4"])),
   						strtoupper($pmiDetails["postcode"])
   						)));

				$pmiDeceased = $pmiDetails['death_indicator'] == '1';
				$pmiName = ucwords(strtolower($pmiDetails['first_name'] . " " . $pmiDetails['last_name']));
				$pmiNhsNumber = getFormattedNhsNumber($pmiDetails['nhs_number']);
				$pmiDOB = date_format(date_create($pmiDetails['dob']), "j M Y");

   			}

   			if (empty($contact["address_id"])) {
		    	$this->add('static', "AM_contactId_" . $counts["address_missing"], NULL, $cId);
		    	$this->add('static', "AM_sNumber_" . $counts["address_missing"], NULL, $contact['UHL S number']);
		    	$this->add('static', "AM_name_" . $counts["address_missing"], NULL, $contact['display_name']);
		    	$this->add('static', "AM_pmiAddress_" . $counts["address_missing"], NULL, implode(', ', $pmiAddress));
		    	$this->add('static', "AM_pmiName_" . $counts["address_missing"], NULL, $pmiName);
   				$counts["address_missing"]++;

   				if (!empty($pmiAddress)) {
   					$this->contactIdsForAddressImport[$cId] = $contact['UHL S number'];
   				}
   			}

   			if (is_null($pmiDetails)) {
		    	$this->add('static', "NoPmi_contactId_" . $counts["not_in_pmi"], NULL, $cId);
		    	$this->add('static', "NoPmi_sNumber_" . $counts["not_in_pmi"], NULL, $contact['UHL S number']);
          		$this->add('static', "NoPmi_name_" . $counts["not_in_pmi"], NULL, $contact['display_name']);
		    	$this->add('static', "NoPmi_dateOfBirth_" . $counts["not_in_pmi"], NULL, $contact['birth_date']);
		    	$this->add('static', "NoPmi_civiAddress_" . $counts["not_in_pmi"], NULL, implode(', ', $civiAddress));
   				$counts["not_in_pmi"]++;
   			}

		   if ($pmiNhsNumber && $pmiNhsNumber != $contact["NHS number"] && $contact["NHS number"]) {
				$this->add('static', "NhsMismatch_contactId_" . $counts["nhs_number_mismatch"], NULL, $cId);
				$this->add('static', "NhsMismatch_sNumber_" . $counts["nhs_number_mismatch"], NULL, $contact['UHL S number']);
				$this->add('static', "NhsMismatch_name_" . $counts["nhs_number_mismatch"], NULL, $contact['display_name']);
				$this->add('static', "NhsMismatch_cv_nhsnumber_" . $counts["nhs_number_mismatch"], NULL, $contact['NHS number']);
				$this->add('static', "NhsMismatch_pmi_nhsnumber_" . $counts["nhs_number_mismatch"], NULL, $pmiNhsNumber);
				$counts["nhs_number_mismatch"]++;
			}

			if ($pmiNhsNumber && !$contact["NHS number"]) {
				$this->add('static', "NhsMissing_contactId_" . $counts["nhs_number_mismatch"], NULL, $cId);
				$this->add('static', "NhsMissing_sNumber_" . $counts["nhs_number_mismatch"], NULL, $contact['UHL S number']);
				$this->add('static', "NhsMissing_name_" . $counts["nhs_number_mismatch"], NULL, $contact['display_name']);
				$this->add('static', "NhsMissing_pmi_nhsnumber_" . $counts["nhs_number_mismatch"], NULL, $pmiNhsNumber);
				$counts["missing_nhs_number"]++;

				$this->missingNhsNumberDetails[$cId] = $pmiNhsNumber;
			}

			if ($pmiDOB && $pmiDOB != $contactDOB && $contactDOB) {
				$this->add('static', "DobMismatch_contactId_" . $counts["dob_mismatch"], NULL, $cId);
				$this->add('static', "DobMismatch_sNumber_" . $counts["dob_mismatch"], NULL, $contact['UHL S number']);
				$this->add('static', "DobMismatch_name_" . $counts["dob_mismatch"], NULL, $contact['display_name']);
				$this->add('static', "DobMismatch_cv_dob_" . $counts["dob_mismatch"], NULL, $contactDOB);
				$this->add('static', "DobMismatch_pmi_dob_" . $counts["dob_mismatch"], NULL, $pmiDOB);
				$counts["dob_mismatch"]++;
			}

			if ($pmiDOB && !$contactDOB) {
				$this->add('static', "DobMissing_contactId_" . $counts["missing_dob"], NULL, $cId);
				$this->add('static', "DobMissing_sNumber_" . $counts["missing_dob"], NULL, $contact['UHL S number']);
				$this->add('static', "DobMissing_name_" . $counts["missing_dob"], NULL, $contact['display_name']);
				$this->add('static', "DobMissing_pmi_dob_" . $counts["missing_dob"], NULL, $pmiDOB);
				$counts["missing_dob"]++;

				$this->missingDOBDetails[$cId] = date_format(date_create($pmiDOB), "Ymd");
			}

			if ($civiDeceased && !$pmiDeceased) {
		    	$this->add('static', "DM_contactId_" . $counts["deceased_mismatch"], NULL, $cId);
		    	$this->add('static', "DM_sNumber_" . $counts["deceased_mismatch"], NULL, $contact['UHL S number']);
		    	$this->add('static', "DM_name_" . $counts["deceased_mismatch"], NULL, $contact['display_name']);
		    	$this->add('static', "DM_civiDeceasedFlag_" . $counts["deceased_mismatch"], NULL, $contact['is_deceased'] == '1' ? 'Y' : 'N');
		    	$this->add('static', "DM_civiDeceasedDate_" . $counts["deceased_mismatch"], NULL, $contact['deceased_date']);
		    	$this->add('static', "DM_pmiName_" . $counts["deceased_mismatch"], NULL, $pmiName);
		    	$this->add('static', "DM_pmiDeceasedFlag_" . $counts["deceased_mismatch"], NULL, $pmiDetails['death_indicator'] == '1' ? 'Y' : 'N');
		    	$this->add('static', "DM_pmiDeceasedDate_" . $counts["deceased_mismatch"], NULL, $pmiDetails['date_of_death']);
				$counts["deceased_mismatch"]++;
			}

			if (!$civiDeceased && $pmiDeceased) {
		    	$this->add('static', "ND_contactId_" . $counts["newly_deceased"], NULL, $cId);
		    	$this->add('static', "ND_sNumber_" . $counts["newly_deceased"], NULL, $contact['UHL S number']);
		    	$this->add('static', "ND_name_" . $counts["newly_deceased"], NULL, $contact['display_name']);
		    	$this->add('static', "ND_pmiName_" . $counts["newly_deceased"], NULL, $pmiName);
		    	$this->add('static', "ND_pmiDeceasedDate_" . $counts["newly_deceased"], NULL, $pmiDetails['date_of_death']);
				$counts["newly_deceased"]++;

				$this->contactIdsForDeceasedFlagImport[$cId] = $contact['UHL S number'];
			}

			if (!empty($contact["address_id"]) && !is_null($pmiDetails) && !areAddressesSimilar($civiAddress, $pmiAddress)) {
		    	$this->add('static', "AD_contactId_" . $counts["address_different"], NULL, $cId);
		    	$this->add('static', "AD_sNumber_" . $counts["address_different"], NULL, $contact['UHL S number']);
		    	$this->add('static', "AD_name_" . $counts["address_different"], NULL, $contact['display_name']);
		    	$this->add('static', "AD_civiAddress_" . $counts["address_different"], NULL, implode(', ', $civiAddress));
		    	$this->add('static', "AD_pmiAddress_" . $counts["address_different"], NULL, implode(', ', $pmiAddress));
		    	$this->add('static', "AD_pmiName_" . $counts["address_different"], NULL, $pmiName);
				$counts["address_different"]++;
			}
   		}

    	$this->add('static', 'contacts', NULL, $counts["contacts"]);
    	$this->add('static', 'address_missing', NULL, $counts["address_missing"]);
    	$this->add('static', 'not_in_pmi', NULL, $counts["not_in_pmi"]);
    	$this->add('static', 'address_different', NULL, $counts["address_different"]);
    	$this->add('static', 'address_missing_and_in_pmi', NULL, $counts["address_missing_and_in_pmi"]);
    	$this->add('static', 'newly_deceased', NULL, $counts["newly_deceased"]);
    	$this->add('static', 'deceased_mismatch', NULL, $counts["deceased_mismatch"]);
    	$this->add('static', 'missing_nhs_number', NULL, $counts["missing_nhs_number"]);
    	$this->add('static', 'nhs_number_mismatch', NULL, $counts["nhs_number_mismatch"]);
    	$this->add('static', 'missing_dob', NULL, $counts["missing_dob"]);
    	$this->add('static', 'dob_mismatch', NULL, $counts["dob_mismatch"]);
		
	    $this->add('checkbox', 'import_addresses', ts('Import missing addresses from UHL PMI where available'));
	    $this->add('checkbox', 'flag_deceased', ts('Import deceased flag and date into CiviCRM from UHL PMI for these participants'));
	    $this->add('checkbox', 'import_missing_nhs_number', ts('Import missing NHS Numbers from PMI'));
	    $this->add('checkbox', 'import_missing_dob', ts('Import missing date of birth from PMI'));

		$this->addDefaultButtons(ts('Import'));
    }

	public function postProcess( ) {
		$transaction = new CRM_Core_Transaction();

   		$params =$this->controller->exportValues();

   		if (!empty($params['import_addresses'])) {
   			self::importAddresses();
			drupal_set_message(t('Imported Addresses'));
   		}

   		if (!empty($params['flag_deceased'])) {
   			self::importDeceasedFlag();
			drupal_set_message(t('Imported Deceased Flag'));
   		}

   		if (!empty($params['import_missing_nhs_number'])) {
			self::importMissingNhsNumbers();
		 drupal_set_message(t('Imported Missing NHS Numbers'));
		}

		if (!empty($params['import_missing_dob'])) {
			self::importMissingDob();
		 drupal_set_message(t('Imported Missing Dates of Birth'));
		}
	}

   	private function importAddresses() {
        $ph = new PmiHelper();

   		foreach ($this->contactIdsForAddressImport as $cId => $sNumber) {
            $ph->import_address($cId);
   		}
   	}

   	private function importDeceasedFlag() {
        $ph = new PmiHelper();
        
   		foreach ($this->contactIdsForDeceasedFlagImport as $cId => $sNumber) {
   			$pmiDetails = $ph->get_pmi_details($sNumber);

   			$params = array(
   				"version" => "3",
   				"contact_id" => $cId,
   				"is_deceased" => 1,
   				"deceased_date" => $pmiDetails['date_of_death'],
   				);

   			civicrm_api('contact', 'create', $params);
   		}
   	}

   	private function importMissingNhsNumbers() {
        $nhsNumberFieldName = lcbru_get_custom_field_id_name(CIVI_FIELD_NHS_NUMBER);
        
   		foreach ($this->missingNhsNumberDetails as $cId => $nhsNumber) {
			CiviCrmApiHelper::createObject('contact', array(
				"contact_id" => $cId,
				$nhsNumberFieldName => $nhsNumber,
		 ));
   		}
   	}

   	private function importMissingDob() {
   		foreach ($this->missingDOBDetails as $cId => $dob) {
			CiviCrmApiHelper::createObject('contact', array(
				"contact_id" => $cId,
				"birth_date" => $dob,
		 ));
   		}
   	}
}