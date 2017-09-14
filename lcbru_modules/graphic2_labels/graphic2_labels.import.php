<?php

/**
 * @file
 * This script provides a specific data import process for the GRAPHIC2 study using the CiviCRM API instead of the GUI data import process.
 * The intention is for the functions contained in this script to be accessed using the GRAPHIC 2 label printing module / admin page - it should only be run once!
 * 
 */
 
require_once("constants.php");

function graphic2_lookup_surgeries() {
  civicrm_initialize();
  try {
  // Read the csv file of importable data, one row at a time, into an array
  if (($file_handle = fopen("/var/www/drupal/sites/all/modules/graphic2_labels/gp_list.csv",'r')) !== FALSE) {
    $output = fopen("/var/www/drupal/sites/all/modules/graphic2_labels/gp_output.csv",'w');
    $gp_array = fgetcsv($file_handle);
    while (!feof($file_handle)) {
      $surgery = "";
      $result = civicrm_api('Contact','get',array ('version' =>'3', 'contact_sub_type' => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), 'organization_name' => trim($gp_array['0']))); // Not going to be that lucky, are we?
      if ($result['is_error']) {
        drupal_set_message("Error looking for GP surgery: " . $gp_array['0'] . ": " . $result['error_message'], "error");
  	  }
      if ($result['count'] == 1) {
        $surgery = $result['id'];
      } else {  // We're going to have to try using the address - line 1
        $result = civicrm_api('Contact','get',array ('version' =>'3', 'contact_sub_type' => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), 'supplemental_address_1' => trim($gp_array['0']))); 
        if ($result['is_error']) {
          drupal_set_message("Error looking for GP surgery: " . $gp_array['0'] . ": " . $result['error_message'], "error");
  	    }
        if ($result['count'] == 1) {
          $surgery = $result['id'];
        } else { // We're going to have to try using the post code
          $result = civicrm_api('Contact','get',array ('version' =>'3', 'contact_sub_type' => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), 'postal_code' => trim($gp_array['3']))); 
          if ($result['is_error']) {
            drupal_set_message("Error looking for GP surgery: " . $gp_array['0'] . ": " . $result['error_message'], "error");
    	  }
          if ($result['count'] == 1) {
            $surgery = $result['id'];
          } else if ($result['count'] == 0) {
            drupal_set_message("GP surgery couldn't be found: " . $gp_array['3'], "error");
          } else {
            drupal_set_message("Too many matching GP surgeries: " . $gp_array['3'], "error");
          }
        }
      }

      $gp_array['4'] = $surgery;
      if ($gp_array['4'] > 0) {
        $surgery_array = civicrm_api('Contact','get',array ('version' =>'3', 'contact_sub_type' => str_replace(" ","_",CIVI_SUBTYPE_SURGERY), 'id' => $gp_array['4']));
        $gp_array['5'] = $surgery_array['values'][$surgery]['organization_name'];
      }
      fputcsv($output,$gp_array); // Output the array to csv file
      $gp_array = fgetcsv($file_handle); // Now get the next line and start again, unless there is no next line...
    }
    fclose($file_handle);
    fclose($output);
    drupal_set_message("GP surgeries were looked up.");
  }
  }
  catch(Exception $ex) {
    drupal_set_message("An unexpected error occured during the GP surgery lookup: ".$ex->getMessage(),"error");
    }
}
  

function graphic2_import() {
	civicrm_initialize();
	try {
                
        // Read the csv file of importable data, one row at a time, into an array
        if (($file_handle = fopen("/var/www/drupal/sites/all/modules/graphic2_labels/import_file.csv",'r')) !== FALSE) {
        $graphic2_import_array = fgetcsv($file_handle);
        while (!feof($file_handle)) {

            if ($graphic2_import_array['0'] == "" || $graphic2_import_array['3'] == "" || $graphic2_import_array['4'] == "") {
              drupal_set_message("A line was ignored because it had blank entries in the ID columns.");
              $graphic2_import_array = fgetcsv($file_handle);
              continue;
            }

            if (!is_numeric($graphic2_import_array['0'])) {
              drupal_set_message("A line was ignored beginning with '" . $graphic2_import_array[0] . "' because it was probably a header row.");
              $graphic2_import_array = fgetcsv($file_handle);
              continue;
            }

          // Create a contact record for the patient, create their address and phone number, find their GP surgery and create a relationship 

              // Look up the country to get the country_id - it won't always be United Kingdom - but the array will only populate with active countries in civicrm
              $countries=civicrm_api("Constant","get", array ('version' =>'3', 'name' =>'country'));              
              $country = array_search($graphic2_import_array['16'], $countries['values']);
              
              // Look up the county to get the state_province_id - it won't always be Leicestershire - but the array will only populate with active counties in civicrm
              $stateProvinces=civicrm_api("Constant","get", array ('version' =>'3', 'name' =>'stateProvince'));              
              $county = array_search($graphic2_import_array['15'], $stateProvinces['values']);
              
              // Look up the prefix to get the prefix_id
              $og = get_civi_option_group(array("name" => "individual_prefix")); // Get option group for 'individual prefix'
              $prefix = get_civi_option_value($og,$graphic2_import_array['6']);

              $address = array();
              $address["api.address.create"] = array(
                "supplemental_address_1" => $graphic2_import_array['11'],
                "street_address" => $graphic2_import_array['12'],
                "supplemental_address_2" => $graphic2_import_array['13'],
                "city" => $graphic2_import_array['14'],
                "postal_code" => $graphic2_import_array['17'],
                "location_type_id" => "1", 
                "is_primary" => "1", 
                "country_id" => "$country", 
                "state_province_id" => $county
              );
              
              $phone = array();
              $phone["api.phone.create"] = array("phone" => $graphic2_import_array['18'], "location_type_id" => "1", "is_primary" => "1", "phone_type_id" => "1");

              // Check if the patient exists in civicrm already.
              $subject = civicrm_api("Contact","get", array("version" => "3", "contact_type" => "Individual", "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_CONTACT)), "first_name" => $graphic2_import_array['8'], "last_name" => $graphic2_import_array['7'], "birth_date" => $graphic2_import_array['10']));
              if ($subject['is_error']) {
                throw new Exception("Error looking for contact:" . $contact['error_message']);
           	  }
              if ($subject['count'] != "0") {
              drupal_set_message("This contact already exists, jumping to group, relationship and study enrollment section. Check address, etc., manually later: " . $graphic2_import_array['8'] . " " . $graphic2_import_array['7']);

              } else {
              drupal_set_message("This contact does not exist, creating... " . $graphic2_import_array['8'] . " " . $graphic2_import_array['7']);

              //Create the subject record.
              $subject = create_civi_contact_with_custom_data(array_merge(array(
                "contact_type" => "Individual", 
                "contact_sub_type" => array(str_replace(" ","_",CIVI_SUBTYPE_CONTACT)), 
                "first_name" => $graphic2_import_array['8'], 
                "middle_name" => $graphic2_import_array['9'], 
                "last_name" => $graphic2_import_array['7'], 
                "birth_date" => $graphic2_import_array['10'], 
                "prefix_id" => $prefix['value'], 
                ),
                $address, 
                $phone
                ), 
                array(
                CIVI_FIELD_S_NUMBER => $graphic2_import_array['2'],
                )); // Create Subject sub-type
                
              }

                // Put the contact in the GRAPHIC2 group
                $group_id = civicrm_api("group","get", array ('version' => '3', 'title' => 'GRAPHIC2'));     
                $group = civicrm_api("GroupContact","create", array ('version' => '3', 'contact_id' => $subject['id'], 'group_id' => $group_id['id']));     

                // Create a relationship to the GP Surgery - if there is an entry in column 27
                if ($graphic2_import_array['27'] != "") {
                  $rel = find_civi_relationship_type(CIVI_REL_SURGERY_PATIENT);
                  create_civi_relationship($rel, $subject['id'], $graphic2_import_array['27']); // This is a new column, populated by lookup and manual entry. Thanks, Sue!
                }

                // Create a CiviCase entry for the GRAPHIC 2 study enrollment with the associated custom data
                $case_statuses = civicrm_api("OptionGroup","get", array ('version' => '3', 'name' => 'case_status'));     
                $case_status = civicrm_api("OptionValue","get", array ('version' => '3', 'option_group_id' => $case_statuses['id'], name => CIVI_CASE_PENDING));     

                $enrollment = civicrm_api("Case","create", array ('version' => '3', 'contact_id' => $subject['id'], 'case_type' => CIVI_CASETYPE_GRAPHIC2, 'subject' => CIVI_CASETYPE_GRAPHIC2, 'case_status_id' => $case_status['values'][$case_status['id']]['value']  ));
                if ($enrollment['id'] != "") {
                  $lab_id_input = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(" ","_",CIVI_FIELD_G2_LAB_ID) => floatval($graphic2_import_array['3'])));
                  $participant_id_input = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(" ","_",CIVI_FIELD_G2_PAT_ID) => floatval($graphic2_import_array['0'])));
                  $family_id_input = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(" ","_",CIVI_FIELD_G2_FAM_ID) => floatval($graphic2_import_array['4'])));
                  $further_studies = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(" ","_",CIVI_FIELD_G2_FURTHER) => $graphic2_import_array['50']));
                  $g1_blood_consent = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(" ","_",CIVI_FIELD_G2_G1_BLOOD) => $graphic2_import_array['51']));
                  $g2_pre_consent = civicrm_api("CustomValue","create",array('version' => '3', entity_id => $enrollment['id'], 'custom_' . str_replace(" ","_",CIVI_CUSTOMGROUP_GRAPHIC2) . ":" . str_replace(array(" ","-"),"_",CIVI_FIELD_G2_PRE_CON) => $graphic2_import_array['63']));

                  if ($graphic2_import_array['61'] == "1")  {
                    $activity_types = civicrm_api("OptionGroup","get", array ('version' => '3', 'name' => 'activity_type'));     
                    $activity_type = civicrm_api("OptionValue","get", array ('version' => '3', 'name' => CIVI_ACTIVITY_INV_LETTER));     
                    $g2_mother_letter = civicrm_api("Activity","create",array('version' => '3', source_contact_id => $subject['id'], activity_type_id => $activity_type['values'][$activity_type['id']]['value'], 'subject' => "GRAPHIC 2 mother - initial letter sent", 'status_id' => '2', 'priority_id' => '2', 'case_id' => $enrollment['id']));
                  }
                }

            $graphic2_import_array = fgetcsv($file_handle);
            }
        fclose($file_handle);
 
        drupal_set_message("GRAPHIC 2 data was loaded into the CiviCRM database.");
        }
                
    }
    catch(Exception $ex) {
            drupal_set_message("An unexpected error occured during the GRAPHIC 2 data import: ".$ex->getMessage(),"error");
    }

}


