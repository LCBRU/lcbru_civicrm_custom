<?php

/**
 * @file
 * Utility functions for interacting with the civicrm API - used by the initialisation process for LCBRU but potentially used by other processes also. 
 *
 */

#utility method to create civi contact
# The dupcheck parameter allows calls to specify behaviour on finding a matching record, TRUE throws an error, FALSE does an update. Default is TRUE.

function create_civi_contact($params, $dupcheck = TRUE) {
  civicrm_initialize();
  $params['version']='3';
  $contact = civicrm_api('Contact','get',$params);
  if ($contact['is_error']) {
    throw new Exception(__FUNCTION__." Error looking for contact:" . $contact['error_message'].PHP_EOL.PHP_EOL.'Params: '.print_r($params, true).PHP_EOL);
	}
  if ($contact['count']==0 || $dupcheck == FALSE) {
    $contact = civicrm_api('Contact','create',$params);
    if ($contact['is_error']) {
      throw new Exception(__FUNCTION__." Error creating contact:" . $contact['error_message'].PHP_EOL.PHP_EOL.'Params: '.print_r($params, true).PHP_EOL);
	}
  } else {
      throw new Exception("Contact already exists.");
  }  
  return array_shift($contact['values']);
}

#utility method to delete civi contact
function delete_civi_contact($params) {
  civicrm_initialize();
  $params['version']='3';
  $contact = civicrm_api('Contact','get',$params);
  if ($contact['is_error']) {
    drupal_set_message("Error looking for contact: " . $params['display_name'] . ": " . $contact['error_message'].PHP_EOL.PHP_EOL.'Params: '.print_r($params, true).PHP_EOL, "error");
	}
  if ($contact['count'] == 1) {
    $params['id'] = $contact['id'];
    $contact = civicrm_api('Contact','delete',$params);
    if ($contact['is_error']) {
      drupal_set_message("Error deleting contact: " .  $params['display_name'] . ": " . $contact['error_message'].PHP_EOL.PHP_EOL.'Params: '.print_r($params, true).PHP_EOL, "error");
	}
  } else if ($contact['count'] == 0) {
    drupal_set_message("Contact couldn't be found: " . $params['display_name'], "error");
  } else {
    drupal_set_message("Too many matching contacts: " . $params['display_name'],"error");
  }
  return $contact['values'];
}

function get_civi_contact($contactID) {
  civicrm_initialize();

  $params = array(
    "version" => "3",
    "contact_id" => $contactID,
    );
  
  return civicrm_api('Contact','getsingle',$params);
}

# utility method to look up custom fields and then create a civi contact
# Takes 2 arrays - one with params for the create call, and one with the custom fields to look up.
# The dupcheck allows for specific behaviour in event of matching an existing contact. See create_civi_contact for details.

function create_civi_contact_with_custom_data($params,$custom_params,$dupcheck = TRUE) {
  civicrm_initialize();
  foreach ($custom_params as $key => $value) {
    $cf = get_civi_custom_field($key);
    $params['custom_' . $cf['id']] = $value;
  }
  $result = create_civi_contact($params,$dupcheck);
  return $result;
}

function create_civi_telephone($contactId, $phone, $locationName, $phoneTypeName, $params = array()) {
  $location = civicrm_api('LocationType','get', array('version' => 3, 'name' => $locationName));
  $phoneType = getPhoneTypeOptionValueFromTitle($phoneTypeName);

  $params["version"] = 3;
  $params["location_type_id"] = $location["id"];
  $params["phone_type_id"] = $phoneType;
  $params["contact_id"] = $contactId;
  $params["phone"] = $phone;

  return civicrm_api('Phone','create',$params);
}

# Utility method to attach an ICE code to a practice address, given the practice name, address and code
function attach_site_ice_code($org_name,$address,$ice_code) {
  civicrm_initialize();
  $params['version']='3';
  $params['organization_name'] = $org_name;
  $contact = civicrm_api('Contact','get',$params);
  if ($contact['is_error']) {
    drupal_set_message("Error looking for contact: " . $params['organization_name'] . ": " . $contact['error_message'], "error");
	}
  if ($contact['count'] == 1) {
    unset($params['organization_name']);
    $params['contact_id'] = $contact['id'];
    $params['supplemental_address_1'] = $address;
    $location = civicrm_api('Address','get',$params);
    if ($location['is_error']) {
      drupal_set_message("Error looking for address: " .  $params['supplemental_address_1'] . ": " . $location['error_message'], "error");
	}
	if  ($location['count'] == 1) {
      $cf = get_civi_custom_field(CIVI_FIELD_ICE_LOCATION);
      $params['custom_' . $cf['id']] = $ice_code;
      $params['id'] = $location['id'];
      $update = civicrm_api('Address','create',$params);
  	} else if ($location['count'] == 0) {
      drupal_set_message("Address couldn't be found: " . $params['supplemental_address_1'], "error");
    } else {
      drupal_set_message("Too many matching addresses: " . $params['supplemental_address_1'],"error");
    }
  } else if ($contact['count'] == 0) {
    drupal_set_message("Contact couldn't be found: " . $params['organization_name'], "error");
  } else {
    drupal_set_message("Too many matching contacts: " . $params['organization_name'],"error");
  }
  return $location['values'];
}

#utility method to create civi custom group
function create_civi_custom_group($params, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $cg = civicrm_api('CustomGroup','get',array('version' => '3', 'title' => $params['title']));
  if ($cg['is_error']) {
    throw new Exception("Error looking for custom group:".$cg['error_message']);
	}
  if ($cg['count']==0) {
    $cg = civicrm_api('CustomGroup','create',$params);
    if ($cg['is_error']) {
      throw new Exception("Error creating custom group: ".$cg['error_message']."\n\nParameters:\n\n".print_r($params, true));
	}
  } else {
      if (!$ignoreDuplicates) {
        throw new Exception("Custom group already exists.");
      }
  }  
  return array_shift($cg['values']);
}

#utility method to delete civi custom group
function delete_civi_custom_group($params) {
  civicrm_initialize();
  $params['version']='3';
  $cg = civicrm_api('CustomGroup','get',$params);
  if ($cg['is_error']) {
    drupal_set_message("Error looking for custom group: " . $params['title'] . ": " . $cg['error_message'], "error");
	}
  if ($cg['count'] == 1) {
    $params['id'] = $cg['id'];
    $cg = civicrm_api('CustomGroup','delete',$params);
    if ($cg['is_error']) {
      drupal_set_message("Error deleting custom group: " .  $params['title'] . ": " . $cg['error_message'], "error");
	}
  } else if ($cg['count'] == 0) {
    drupal_set_message("Custom group couldn't be found: " . $params['title'], "error");
  } else {
    drupal_set_message("Too many matching custom groups: " . $params['title'],"error");
  }
  return $cg['values'];
}

#utility method to create civi custom field
function create_civi_custom_field($cg,$params, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  watchdog(__FUNCTION__, print_r($params, true));

  $params['version']='3';

  if (!array_key_exists('name', $params)) {
    $params['name'] = str_replace(" ","_",$params['label']);
  }

  $cf = civicrm_api('CustomField','get', array('version' => '3', 'name' => $params['name']));

  if ($cf['is_error']) {
    throw new Exception("Error looking for custom field:".$cf['error_message']);
	}

  if ($cf['count']==1 && $ignoreDuplicates) {
    $params['id'] = reset($cf['values'])['id'];
  }

  if ($cf['count']==0 || ($cf['count']==1 && $ignoreDuplicates)) {
    $params['custom_group_id'] = $cg['id'];
    $cf = civicrm_api('CustomField','create',$params);
    if ($cf['is_error']) {
      throw new Exception("Error creating custom field:\n\nError: ".$cf['error_message']."\n\nParams: ".print_r($params, TRUE));
	}
  } else {
    if (!$ignoreDuplicates) {
      throw new Exception("Custom field already exists.");
    }
  }  
  return array_shift($cf['values']);
}

#utility method to delete civi custom field
function delete_civi_custom_field($params) {
  civicrm_initialize();
  $params['version']='3';
  $cf = civicrm_api('CustomField','get',$params);
  if ($cf['is_error']) {
    drupal_set_message("Error looking for custom field: " . $params['label'] . ": " . $cf['error_message'], "error");
	}
  if ($cf['count'] == 1) {
    $params['id'] = $cf['id'];
    $cf = civicrm_api('CustomField','delete',$params);
    if ($cf['is_error']) {
      drupal_set_message("Error deleting custom field: " .  $params['label'] . ": " . $cf['error_message'], "error");
	}
  } else if ($cf['count'] == 0) {
    drupal_set_message("Custom field couldn't be found: " . $params['label'], "error");
  } else {
    drupal_set_message("Too many matching custom fields: " . $params['label'],"error");
  }
  return $cf['values'];
}

# utility method to return a custom field, given a label,returns an array containing the matching cf
function get_civi_custom_field($label) {
  civicrm_initialize();
  $params['version']='3';
  $params['label'] = $label;
  $cf = civicrm_api('CustomField','get', $params);
  if ($cf['is_error']) {
    throw new Exception("Error looking for custom field:".$cf['error_message']);
  }
  if ($cf['count'] = '0') {
    drupal_set_message("Custom field couldn't be found: " . $params['label'], "error");
  } else if ($cf['count'] > '1') {
    drupal_set_message("Too many matching custom fields: " . $params['label'],"error");
  } else {
    return array_shift($cf['values']);
  }
}

# utility method to return a custom group, given a name,returns an array containing the matching cf
function get_civi_custom_group_byName($name) {
  civicrm_initialize();
  $params['version']='3';
  $params['name'] = $name;
  $cf = civicrm_api('CustomGroup','get', $params);
  if ($cf['is_error']) {
    throw new Exception("Error looking for custom group:".$cf['error_message']);
  }
  if ($cf['count'] = '0') {
    throw new Exception("Custom group not found: $name");
  } else if ($cf['count'] > '1') {
    throw new Exception("More than opne Custom group found: $name");
  } else {
    return array_shift($cf['values']);
  }
}

# utility method to return a custom field, given a name,returns an array containing the matching cf
function get_civi_custom_field_byName($name) {
  civicrm_initialize();
  $params['version']='3';
  $params['name'] = $name;
  $cf = civicrm_api('CustomField','get', $params);
  if ($cf['is_error']) {
    throw new Exception("Error looking for custom field:".$cf['error_message']);
  }
  if ($cf['count'] = '0') {
    drupal_set_message("Custom field couldn't be found: " . $params['name'], "error");
  } else if ($cf['count'] > '1') {
    drupal_set_message("Too many matching custom fields: " . $params['name'],"error");
  } else {
    return array_shift($cf['values']);
  }
}

#utility method to create civi contact subtype
function create_civi_contact_subtype($params, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  watchdog(__FUNCTION__, 'Params: ' . print_r($params, TRUE));
  $params['version']='3';
  $existsParams = array(
      'name' => $params['name']
    , 'version' => '3'
    );
  $cs = civicrm_api('ContactType','get',$existsParams);
  if ($cs['is_error']) {
    throw new Exception("Error looking for contact type:".$cs['error_message']);
	}
  if ($cs['count']==0) {
    $cs = civicrm_api('ContactType','create',$params);
    if ($cs['is_error']) {
      throw new Exception("Error creating contact type:".$cs['error_message']."\n\nParams: ".print_r($params, true));
	}
  } else {
      if (!$ignoreDuplicates) {
          throw new Exception("Contact type already exists.");
        }
  }
  return array_shift($cs['values']);
}

#utility method to delete civi contact subtype
function delete_civi_contact_subtype($params) {
  civicrm_initialize();
  $params['version']='3';
  $cs = civicrm_api('ContactType','get',$params);
  if ($cs['is_error']) {
    drupal_set_message("Error looking for contact type: " . $params['label'] . ": " . $cs['error_message'], "error");
	}
  if ($cs['count'] == 1) {
    $params['id'] = $cs['id'];
    $cs = civicrm_api('ContactType','delete',$params);
    if ($cs['is_error']) {
      drupal_set_message("Error deleting contact type: " .  $params['label'] . ": " . $cs['error_message'], "error");
	}
  } else if ($cs['count'] == 0) {
    drupal_set_message("Contact type couldn't be found: " . $params['label'], "error");
  } else {
    drupal_set_message("Too many matching contact types: " . $params['label'],"error");
  }
  return $cs['values'];
}

# utility function to create civi relationship type
function create_civi_relation_type($params, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $rt = civicrm_api('RelationshipType','get',array("name_a_b" => $params['name_a_b'], "version" => "3", "is_active" => "1"));
  if ($rt['is_error']) {
    throw new Exception("Error looking for relationship type: '" . $params['name_a_b'] . " / " . $params['name_b_a'] . "': " . $rt['error_message']);
	}
  if ($rt['count']==0) {
    $rt = civicrm_api('RelationshipType','create',$params);
    if ($rt['is_error']) {
      throw new Exception("Error creating relationship type - '" .  $params['name_a_b'] . " / " . $params['name_b_a'] . "': " . $rt['error_message']);
	}
  } else {
      if (!$ignoreDuplicates) {
        throw new Exception("Relationship type '" .  $params['name_a_b'] . " / " . $params['name_b_a'] . "' already exists.");
      }
  }
  return array_shift($rt['values']);
}

# utility function to delete civi relationship type
function delete_civi_relation_type($params) {
  civicrm_initialize();
  $params['version']='3';
  $rt = civicrm_api('RelationshipType','get',$params);
  if ($rt['is_error']) {
    drupal_set_message("Error looking for relationship type: " . $params['name_a_b'] . " / " . $params['name_b_a'] . ": " . $rt['error_message'], "error");
	}
  if ($rt['count'] == 1) {
    $params['id'] = $rt['id'];
    $rt = civicrm_api('RelationshipType','delete',$params);
    if ($rt['is_error']) {
      drupal_set_message("Error deleting relationship type: " .  $params['name_a_b'] . " / " . $params['name_b_a'] . ": " . $rt['error_message'], "error");
	}
  } else if ($rt['count'] == 0) {
    drupal_set_message("Relationship type couldn't be found: " . $params['name_a_b'] . " / " . $params['name_b_a'], "error");
  } else {
    drupal_set_message("Too many matching relationship types: " . $params['name_a_b'] . " / " . $params['name_b_a'], "error");
  }
  return $rt['values'];
}

# utility function to update civi relationship type
function update_civi_relation_type($params, $new_params) {
  civicrm_initialize();
  $params['version']='3';
  $new_params['version']='3';
  $rt = civicrm_api('RelationshipType','get',$params);
  if ($rt['is_error']) {
    drupal_set_message("Error looking for relationship type: " . $rt['error_message'], "error");
	}
  if ($rt['count'] == 1) {
    $new_params['id'] = $rt['id'];
    $rt = civicrm_api('RelationshipType','update',$new_params);
    if ($rt['is_error']) {
      drupal_set_message("Error updating relationship type: " .  $rt['error_message'], "error");
	}
  } else if ($rt['count'] == 0) {
    drupal_set_message("Relationship type couldn't be found.", "error");
  } else {
    drupal_set_message("Too many matching relationship types.", "error");
  }
  return $rt['values'];
}

# Utility function to find a civi relationship type, given the a-to-b relationship label.
function find_civi_relationship_type($a_to_b_name) {
  civicrm_initialize();
  $params['version']='3';
  $params['name_a_b']=$a_to_b_name;
  $rt = civicrm_api('RelationshipType','getsingle',$params);
  if (!empty($rt['is_error'])) {
    throw new Exception("Error looking for relationship type: '" . $params['name_a_b'] . "': " . $rt['error_message']);
  }
  return $rt['id'];
}

# Utility function to create a civi relationship, given the id of the relationship type and the two contact IDs.
function create_civi_relationship($rt,$a,$b, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  $relationship = civicrm_api('Relationship','get',array('version' => '3', 'contact_id_a' => $a, 'contact_id_b' => $b, 'relationship_type_id' => $rt, 'is_active' => '1'));
  $existing = civicrm_api('Relationship','get',array('version' => '3', 'contact_id_a' => $a, 'contact_id_b' => $b, 'relationship_type_id' => $rt, 'is_active' => '1'));
  if ($relationship['is_error']) {
    throw new Exception("Error looking for the relationship: " . $relationship['error_message']);
  }
  if ($relationship['count'] == 0) {
    $relationship = civicrm_api('Relationship','create',array('version' => '3', 'contact_id_a' => $a, 'contact_id_b' => $b, 'relationship_type_id' => $rt, 'is_active' => '1'));
    if ($relationship['is_error']) {
      throw new Exception("Error creating relationship (Relationship Type = '$rt'; Contact a: '$a'; Contact b: '$b'): " . $relationship['error_message']);
    }
  } else {
      if (!$ignoreDuplicates) {
        drupal_set_message("Relationship '" . $rt . "' already exists between " . $a . " and " . $b , "error");
      }
  }
  return array_shift($relationship['values']);
}

function delete_civi_relationship(array $params) {
    civicrm_initialize();

    if (empty($params)) {
        throw new Exception("Attempting to delete relationships without parameters.");
    }

    $params['version']='3';
    $relationships = civicrm_api('Relationship','get',$params);

    foreach ($relationships['values'] as $r) {
        $result = civicrm_api('Relationship','delete', array('version' => '3', 'id' => $r['id']));
    }
}

# utility function to create civi group
function create_civi_group($params, $ignoreDuplicates=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $group = civicrm_api('Group','get',$params);
  if ($group['is_error']) {
    throw new Exception("Error looking for group:".$group['error_message']);
	}
  if ($group['count']==0) {
    $group = civicrm_api('Group','create',$params);
    if ($group['is_error']) {
      throw new Exception("Error creating group:".$group['error_message']);
	}
  } else {
      if (!$ignoreDuplicates) {
        throw new Exception("Group already exists.");
      }
  }
  return array_shift($group['values']);
}

# utility function to delete civi group
function delete_civi_group($params) {
  civicrm_initialize();
  $params['version']='3';
  $group = civicrm_api('Group','get',$params);
  if ($group['is_error']) {
    drupal_set_message("Error looking for group: " . $params['title'] . ": " . $group['error_message'], "error");
	}
  if ($group['count'] == 1) {
    $params['id'] = $group['id'];
    $group = civicrm_api('Group','delete',$params);
    if ($group['is_error']) {
      drupal_set_message("Error deleting group: " .  $params['title'] . ": " . $group['error_message'], "error");
	}
  } else if ($group['count'] == 0) {
    drupal_set_message("Group couldn't be found: " . $params['title'], "error");
  } else {
    drupal_set_message("Too many matching groups: " . $params['title'],"error");
  }
  return $group['values'];
}

function add_contact_to_civi_group($contactID, $groupTitle) {
  // Put the contact in the BioResource group
  $group_id = civicrm_api("group","get", array ('version' => '3', 'title' => $groupTitle));
  $group = civicrm_api("GroupContact","create", array ('version' => '3', 'contact_id' => $contactID, 'group_id' => $group_id['id']));     

  if ($group['is_error'] != '0') {
    drupal_set_message('Error adding contact to group.', 'error');
  }
}

# Utility function to create civi option group
function create_civi_option_group($params) {
}

# Utility function to get civi option group and return it as an array
function get_civi_option_group($params) {
  civicrm_initialize();
  $params['version']='3';
  $og = civicrm_api('OptionGroup','get', $params);
  if ($og['is_error']) {
    throw new Exception("Error looking for option group:".$og['error_message']);
  }
  return array_shift($og['values']);
}

# Utility function to delete civi option group
function delete_civi_option_group($params) {
}

# utility function to create civi option value
function create_civi_option_value($og,$params,$ignoreDuplicate=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $ov = civicrm_api('OptionValue','get', $params);
  if ($ov['is_error']) {
    throw new Exception("Error looking for option value:".$ov['error_message']);
	}
  if ($ov['count']==0) {
    $params['option_group_id'] = $og['id'];
    $ov = civicrm_api('OptionValue','create',$params);
    if ($ov['is_error']) {
      throw new Exception("Error creating option value:".$ov['error_message']."\n\nParameters:\n\n".print_r($params, TRUE)."\n\nOption Group:\n\n".print_r($og, TRUE));
	  }
  } else {
      IF (!$ignoreDuplicate) {
        throw new Exception("Option value already exists.");
      }
  }
  return array_shift($ov['values']);
}

# utility function to update civi option value
function update_civi_option_value($params, $new_params) {
  civicrm_initialize();
  $params['version']='3';
  $new_params['version']='3';
  $ov = civicrm_api('OptionValue','get',$params);
  if ($ov['is_error']) {
    drupal_set_message("Error looking for option value: " . $ov['error_message'], "error");
	}
  if ($ov['count'] == 1) {
    $new_params['id'] = $ov['id'];
    $ov = civicrm_api('OptionValue','update',$new_params);
    if ($ov['is_error']) {
      drupal_set_message("Error updating option value: " .  $ov['error_message'], "error");
	}
  } else if ($ov['count'] == 0) {
    drupal_set_message("Option value couldn't be found.", "error");
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
  } else {
    drupal_set_message("Too many matching option values.", "error");
  }
  return $ov['values'];
}

# utility function to delete civi option value
function delete_civi_option_value($params) {
  civicrm_initialize();
  $params['version']='3';
  $ov = civicrm_api('OptionValue','get',$params);
  if ($ov['is_error']) {
    drupal_set_message("Error looking for option value: " . $params['label'] . ": " . $ov['error_message'], "error");
	}
  if ($ov['count'] == 1) {
    $params['id'] = $ov['id'];
    $ov = civicrm_api('OptionValue','delete',$params);
    if ($ov['is_error']) {
      drupal_set_message("Error deleting option value: " .  $params['label'] . ": " . $ov['error_message'], "error");
	}
  } else if ($ov['count'] == 0) {
    drupal_set_message("Option value couldn't be found: " . $params['label'], "error");
  } else {
    drupal_set_message("Too many matching option values: " . $params['label'],"error");
  }
  return $ov['values'];
}

# utility function to look up an option value given a label and an option group, returns an array containing the matching ov
function get_civi_option_value($og,$param,$ignoreMissing=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $params['option_group_id'] = $og['id'];
  $params['label'] = $param;
  $ov = civicrm_api('OptionValue','get', $params);
  if ($ov['is_error']) {
    throw new Exception("Error looking for option value:".$ov['error_message']);
  }
  if ($ov['count'] == '0') {
    if ($ignoreMissing) {
      return null;
    } else {
      watchdog("LCBRU Warning" ,"Option value couldn't be found: " . $params['label']);
    }
  } else if ($ov['count'] > '1') {
    watchdog("LCBRU Warning", "Too many matching option values: " . $params['label']);
  } else {
    return array_shift($ov['values']);
  }
}

# utility function to look up an all option values for an option group, returns an array
function get_civi_option_values($og) {
  civicrm_initialize();
  $params['version']='3';
  $params['option_group_id'] = $og['id'];
  $ov = civicrm_api('OptionValue','get', $params);
  if ($ov['is_error']) {
    throw new Exception("Error looking for option value:".$ov['error_message']);
  }

  $result = array();

  foreach ($ov['values'] as $value) {
    $result[$value['value']] = $value['label'];
  }

  return $result;
}

/**
 * Create / recreate a case type for the given name.
 *
 * @param string $caseTypeName the name of the case
 * @return integer case type ID
 * @throws InvalidArgumentException if the caseTypeName is null or empty.
 */
function recreateCaseType($caseTypeName, $caseTypeTitle=FALSE) {
    watchdog(__METHOD__, 'started');

    if (!isset($caseTypeName) || trim($caseTypeName)==='') {
        throw new InvalidArgumentException(__METHOD__ . ' cannot be called without a $caseTypeName.');
    }

    if (!$caseTypeTitle) {
      $caseTypeTitle = $caseTypeName;
    }

    $caseTypeOptionGroup = getCaseTypesOptionGroup();

    if (is_null($caseTypeOptionGroup)) {
      $caseType = civicrm_api3('CaseType', 'get', array('name' => $caseTypeName));

      if ($caseType['count'] == 0) {
        $caseType = civicrm_api3('CaseType', 'create', array('name' => $caseTypeName, 'title' => $caseTypeTitle, 'weight' => '1'));
      }

      return array_shift($caseType['values'])['id'];

    } else {
      return create_civi_option_value($caseTypeOptionGroup, array("name" => $caseTypeName), TRUE)['value'];
    }

    watchdog(__METHOD__, 'completed');
}

/**
 * Delete a case type for the given name.
 *
 * @param string $caseTypeName the name of the case
 * @throws InvalidArgumentException if the caseTypeName is null or empty.
 */
function deleteCaseType($caseTypeName) {
    watchdog(__METHOD__, 'started');

    if (!isset($caseTypeName) || trim($caseTypeName)==='') {
        throw new InvalidArgumentException(__METHOD__ . ' cannot be called without a $caseTypeName.');
    }

    $caseTypeOptionGroup = getCaseTypesOptionGroup();

    if (is_null($caseTypeOptionGroup)) {
      $caseType = civicrm_api3('CaseType', 'getsingle', array('name' => $caseTypeName));

      civicrm_api3('CaseType', 'delete', array('id' => $caseType['id']));
    } else {
      delete_civi_option_value(array(
        'name' => $caseTypeName,
        'option_group_id' => $caseTypeOptionGroup['id'],
        ));
    }

    watchdog(__METHOD__, 'completed');
}

/**
 * Gets the case type option group.
 *
 * @return array Case Type option group or NULL if after version 4.5 and case types now have their own table 
 */
function getCaseTypesOptionGroup()
{
    $caseTypeOptionGroup = get_civi_option_group(array("name" => "case_type"));

    if (is_null($caseTypeOptionGroup)) {
      // We're definitely after version 4.5 and case types have their own table so are not an option group.
      return null;
    }

    // Now then.  If you upgraded from 4.4 to 4.5 the case_type option group was deleted.  Fab!
    // However, if you installed straight to version 4.5 the case_type option group was still created.
    // So now I check if there are any case type option values.  If not, I'm assuming that we're in 4.5.
    $caseTypes = get_civi_option_values($caseTypeOptionGroup);

    if (count($caseTypes) > 0) {
      return $caseTypeOptionGroup;
    } else {
      return null;
    }
}

# utility function to create a case (i.e. a study enrolment), given the case type (i.e. the Study), the case status and the optional parameters
function create_civi_case($casetype,$casestatus, $params, $allowDuplicates=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $lookup_params = array('name' => 'case_status');
  $og = get_civi_option_group($lookup_params); // Should return '27'
  $lookup_params['option_group_id'] = $og['id'];
  $lookup_params['name'] = $casestatus;
  $lookup_params['version']='3';
  $ov = civicrm_api('OptionValue','getsingle',$lookup_params);
  if (!empty($ov['is_error'])) {
    throw new Exception("Error looking for option value: " . $ov['error_message']);
	}
  $params['status_id'] = $ov['value']; // sets status_id to value of the previous array returned matching the pending status

  $params['case_type'] = $casetype; // sets case_type to the supplied case type

  $case = civicrm_api('Case','get', $params);
  if ($case['is_error']) {
    throw new Exception("Error looking for study enrolment:" . $case['error_message']);
  }
  if ($case['count'] == 0 || $allowDuplicates) {
    $case = civicrm_api('Case','create', $params);
      if ($case['is_error']) {
        throw new Exception("Error creating study enrolment:".$case['error_message']);
      }
  } else {
  drupal_set_message("This participant is already recruited to this study.",'error');
  }
  return array_shift($case['values']);
}

# utility function to get a case (i.e. a study enrolment), given the case type (i.e. the Study) and the contact id
function get_civi_cases_of_type_for_contact($contactId, $caseTypeId) {
  civicrm_initialize();

  $contactCases = civicrm_api("Case","get", array ('version' => '3', 'client_id' => $contactId));

  $result = array();

  foreach ($contactCases['values'] as $case) {
    if ($caseTypeId == $case['case_type_id']) {
      $result[] = $case;
    }
  }

  return $result;
}

# utility function to get the custom values for an entity
function get_civi_custom_values($entityId, $entityTable) {
  civicrm_initialize();

  return civicrm_api("CustomValue","get", array ('version' => '3', 'entity_id' => $entityId, 'entity_table' => $entityTable));
}

# utility function to get the custom value for an entity
function get_civi_custom_value($entityId, $customGroupName, $customFieldName) {
  civicrm_initialize();

  return civicrm_api(
      "CustomValue"
    , "getSingle"
    , array (
        'version' => '3'
      , 'entity_id' => $entityId
      , 'return.' .  str_replace(" ","_",$customGroupName) . ":" . str_replace(" ","_",$customFieldName)  => '1'
      )
    );
}

function get_civi_custom_latest_value_by_name($entityId, $customFieldName) {
  civicrm_initialize();

  $customField = get_civi_custom_field($customFieldName);

  $result = civicrm_api(
      "CustomValue"
    , "getSingle"
    , array (
        'version' => '3'
      , 'entity_id' => $entityId
      , 'return.custom_' .  $customField['id']  => '1'
      )
    );

  if (empty($result['is_error'])) {
    return $result['latest'];
  }
}

# utility function to update the custom values for an entity
function create_civi_custom_value($entityId, $customGroupName, $customFieldName, $value) {
  civicrm_initialize();

  drupal_set_message('Start');
  drupal_set_message($entityId);
  drupal_set_message($customGroupName);
  drupal_set_message($customFieldName);
  drupal_set_message($value);
  drupal_set_message('End');

  return civicrm_api(
    "CustomValue",
    "create",
    array(
      'version' => '3',
      'entity_id' => $entityId,
      'custom_' . str_replace(" ","_",$customGroupName) . ":" . str_replace(" ","_",$customFieldName) => $value
    )
  );
}

# utility function to create civi Access Control List (ACL) role
function create_civi_acl_role($params,$ignoreDuplicate=FALSE) {
  civicrm_initialize();
  $params['version']='3';
  $acl = civicrm_api('OptionGroup','get', array('version' => '3', 'name' =>'acl_role'));
  if ($acl['is_error']) {
    throw new Exception("Error looking for option group for ACLs:".$acl['error_message']);
  }
  $params['option_group_id'] = $acl['id'];
  $acl_value = civicrm_api('OptionValue','get',$params);
  if ($acl_value['is_error']) {
    throw new Exception("Error looking for ACL role:".$acl_value['error_message']);
	}
  if ($acl_value['count']==0) {
    $acl_value = civicrm_api('OptionValue','create',$params);
    if ($acl_value['is_error']) {
      throw new Exception("Error creating ACL role:".$acl_value['error_message']);
	}
  } else {
      IF (!$ignoreDuplicate) {
        throw new Exception("ACL role already exists.");
      }
  }
  return array_shift($acl_value['values']);
}

# utility function to delete civi Access Control List (ACL) role
function delete_civi_acl_role($params) {
  civicrm_initialize();
  $params['version']='3';
  $acl_value = civicrm_api('OptionValue','get',$params);
  if ($acl_value['is_error']) {
    drupal_set_message("Error looking for ACL role: " . $params['label'] . ": " . $acl_value['error_message'], "error");
	}
  if ($acl_value['count'] == 1) {
    $params['id'] = $acl_value['id'];
    $acl_value = civicrm_api('OptionValue','delete',$params);
    if ($acl_value['is_error']) {
      drupal_set_message("Error deleting ACL role: " .  $params['label'] . ": " . $acl_value['error_message'], "error");
	}
  } else if ($acl_value['count'] == 0) {
    drupal_set_message("ACL role couldn't be found: " . $params['label'], "error");
  } else {
    drupal_set_message("Too many matching ACL roles: " . $params['label'],"error");
  }
  return $acl_value['values'];
}

# utility function to test whether a string is a UK postcode
function is_postcode($string) {
  if (preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]) ?[0-9][ABD-HJLNP-UW-Z]{2})$#i', $string))  {
    return true;
  } else {
    return false;
  }
}

function addressSplit($address) {
  $address = array_values(array_filter($address));
  $use_google_maps = TRUE;
  $result = _lcbru_manualAddressSplit($address);

  if ($use_google_maps) {
    $googleAddressDetails = _lcbru_googleAddressSplit($address);

    if (!is_null($googleAddressDetails)) {
      if (!empty($googleAddressDetails["city"])) {
        $result["city"] = $googleAddressDetails["city"];
      }
      if (!empty($googleAddressDetails["county"])) {
        $result["county"] = $googleAddressDetails["county"];
      }
      if (!empty($googleAddressDetails["country"])) {
        $result["country"] = $googleAddressDetails["country"];
      }
    }
  }

  if (isset($result['supplemental_address_2']) && strtolower($result["supplemental_address_2"]) == strtolower($result["city"])) {
    $result["supplemental_address_2"] = "";
  }

  if (isset($result["county"])) {
    $stateProvinces = civicrm_api("Constant","get", array ('version' =>'3', 'name' =>'stateProvince'));
    $result["state_province_id"] = array_search($result["county"], $stateProvinces['values']);
  }

  return $result;
}

function _lcbru_googleAddressSplit($address) {

  $result = array(
    "supplemental_address_1" => "",
    "street_address" => "",
    "supplemental_address_2" => "",
    "city" => "",
    "county" => "",
    "postal_code" => "",
    "country" => "",
    "state_province_id" => "",
    );

  $address_url = "http://maps.googleapis.com/maps/api/geocode/json?region=uk&sensor=false&address=" . urlencode(implode(", ", $address));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $address_url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $googleAddressJson = curl_exec($ch);
  curl_close($ch);

  //drupal_set_message("Submitted to Google's API: " . print_r($address_url,TRUE));
  //drupal_set_message("JSON returned from Google's API: " . print_r($googleAddressJson,TRUE));

  $googleAddressArray = json_decode($googleAddressJson, TRUE);

  if (count($googleAddressArray['results']) == 0) {
    return NULL;    
  }
  
  $google_address = $googleAddressArray['results']['0']['address_components'];

  if (!is_array($google_address)) {
    drupal_set_message("Something went wrong looking the address up in google maps API: " . print_r($googleAddressArray,TRUE) . " was returned when looking up '" . print_r($address,TRUE) . "'. You will need to check this address manually before any mailings are sent out.", 'error');
    return NULL;
  }

  foreach ($google_address as $component) {
    $tidy_address_array[$component['types'][0]] = $component['long_name'];
  }

  if (isset($tidy_address_array['postal_town'])) {
    $result["city"] = $tidy_address_array['postal_town'];
  }
  
  if (isset($tidy_address_array['administrative_area_level_2']) && $tidy_address_array['administrative_area_level_2'] != $result["city"]) {
    $result["county"] = $tidy_address_array['administrative_area_level_2'];
  }
  $result["country"] = $tidy_address_array['country'];

  return $result;
}

function _lcbru_get_postCodeIndex($address) {
  for ($i = (count($address) - 1); $i > -1; $i--) {
    if (is_postcode(strtoupper($address[$i]))) {
      return $i;
    }
  }

  return -1;
}

function _lcbru_get_streetAddressIndex($address) {
  $iCount = count($address);

  if ($iCount == 0) {
    return -1;
  }

  for ($i = 0; $i < $iCount; $i++) {
    if (preg_match('/^\d+/',$address[$i])) {
      return $i;
    }
  }

  return 0;
}

function _lcbru_get_house_number($addressPart) {
  $numbers = array();

  if (preg_match('/^\d+/',$addressPart, $numbers)) {
    return $numbers[0];
  } else {
    return NULL;
  }

}

function _lcbru_manualAddressSplit($address) {
  $result = array(
    "supplemental_address_1" => "",
    "street_address" => "",
    "supplemental_address_2" => "",
    "city" => "",
    "county" => "",
    "postal_code" => "",
    "country" => "",
    "state_province_id" => "",
    );

  $filteredAddress = array();

  // The following code has loads of temporary variables because PHP is as dumb as compost
  foreach ($address as $addressPart) {
    $addressSubParts = explode(',', $addressPart);
    foreach ($addressSubParts as $addressSubPart) {
      $trimmedAddressSubPart = trim($addressSubPart);
      if (!empty($trimmedAddressSubPart)) {
        $filteredAddress[] = ucwords(strtolower($trimmedAddressSubPart));
      }
    }
  }

  $filteredAddressCount = count($filteredAddress);

  // No data
  if ($filteredAddressCount < 1) {
    return NULL;
  }

  $postCodeIndex = _lcbru_get_postCodeIndex($filteredAddress);
  $streetAddressIndex = _lcbru_get_streetAddressIndex($filteredAddress);
  $lastUnprocessedIndex = $filteredAddressCount - 1;

  if ($postCodeIndex != -1) {
    $result["postal_code"] = strtoupper($filteredAddress[$postCodeIndex]);
    $lastUnprocessedIndex = $postCodeIndex - 1;
  }

  if ($lastUnprocessedIndex > $streetAddressIndex) {
    $countiesXRef = unserialize(CIVI_LCBRU_COUNTY_CROSS_REFERENCE);
    $normalisedCounty = strtoupper(preg_replace('/[^a-z]+/i', '', $filteredAddress[$lastUnprocessedIndex]));

    if (array_key_exists($normalisedCounty, $countiesXRef)) {
      $result['county'] = $countiesXRef[$normalisedCounty];
      $lastUnprocessedIndex--;
    }
  }

  if ($lastUnprocessedIndex > $streetAddressIndex) {
    $result["city"] = $filteredAddress[$lastUnprocessedIndex];
    $lastUnprocessedIndex--;
  } else {
    $result["city"] = "";
  }

  if ($lastUnprocessedIndex > $streetAddressIndex) {
    $result["supplemental_address_2"] = implode(", ", array_slice($filteredAddress, $streetAddressIndex + 1, $lastUnprocessedIndex - $streetAddressIndex));
  }

  if (!empty($filteredAddress[$streetAddressIndex])) {
    $result["street_address"] = $filteredAddress[$streetAddressIndex];
  }

  if ($streetAddressIndex > 0) {
    $result["supplemental_address_1"] = implode(", ", array_slice($filteredAddress, 0, $streetAddressIndex));
  }

/*
  Taken our because it was producing empty results just containing Leicestershire and United Kingdom
  if (empty($result["county"])){
    if ($result["city"] == "Oakham") {
      $result["county"] = "Rutland";
    } else {
      $result["county"] = "Leicestershire";
    }
  }

  $result["country"] = "United Kingdom";
*/

  if (!empty($result)) {
    return $result;
  } else {
    return NULL;
  }
}

function areAddressesSimilar($address1, $address2) {
  $address1PostCodeIndex = _lcbru_get_postCodeIndex($address1);
  $address2PostCodeIndex = _lcbru_get_postCodeIndex($address2);

  if ($address1PostCodeIndex < 0 || $address2PostCodeIndex < 0) {
    return false;
  }

  if (strtoupper($address1[$address1PostCodeIndex]) != strtoupper($address2[$address2PostCodeIndex])) {
    return false;
  }

  $address1StreetAddressIndex = _lcbru_get_streetAddressIndex($address1);
  $address2StreetAddressIndex = _lcbru_get_streetAddressIndex($address2);

  if ($address1StreetAddressIndex < 0 || $address2StreetAddressIndex < 0) {
    return false;
  }

  return _lcbru_get_house_number($address1[$address1StreetAddressIndex]) == _lcbru_get_house_number($address1[$address1StreetAddressIndex]);
}

function lcbru_getAddressFromContact(array $contact) {
    return array(
        "supplemental_address_1" => $contact["supplemental_address_1"] ?: '',
        "street_address" => $contact["street_address"] ?: '',
        "supplemental_address_2" => $contact["supplemental_address_2"] ?: '',
        "city" => $contact["city"] ?: '',
        "postal_code" => $contact["postal_code"] ?: '',
        "country" => $contact["country"] ?: '',
        "state_province_id" => $contact["state_province_id"] ?: '',
        );
}

function getLocationTypeValueFromTitle($title) {
    $location = civicrm_api('LocationType','get', array('version' => 3, 'name' => $title));
    return $location["id"];
}

function getPrefixOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "individual_prefix"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getPrefixOptionValues() {
      $og = get_civi_option_group(array("name" => "individual_prefix"));
      return get_civi_option_values($og);
}

function getActivityTypeOptionValues() {
      $og = get_civi_option_group(array("name" => "activity_type"));
      return get_civi_option_values($og);
}

function getActivityStatusOptionValues() {
      $og = get_civi_option_group(array("name" => "activity_status"));
      return get_civi_option_values($og);
}

function getGenderOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "gender"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getPreferredCommunicationMethodOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "preferred_communication_method"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getStateProvinceValueFromTitle($title) {
    $stateProvinces = civicrm_api("Constant","get", array ('version' =>'3', 'name' =>'stateProvince'));
    return array_search($title, $stateProvinces['values']);
}

function getCaseTypeIdFromTitle($title) {
    if (!isset($title) || trim($title)==='') {
        throw new InvalidArgumentException(__METHOD__ . ' cannot be called without a $title.');
    }

    $caseTypeOptionGroup = get_civi_option_group(array("name" => "case_type"));

    if (is_null($caseTypeOptionGroup)) {
      $caseType = civicrm_api3('CaseType', 'get', array('name' => $title));

      return array_shift($caseType['values'])['id'];
    } else {
      $value = get_civi_option_value($caseTypeOptionGroup,$title);
      return $value["value"];
    }
}

function getCaseStatusOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "case_status"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getPhoneTypeOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "phone_type"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getActivityTypeOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "activity_type"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function getActivityStatusOptionValueFromTitle($title) {
      $og = get_civi_option_group(array("name" => "activity_status"));
      $value = get_civi_option_value($og,$title);
      return $value["value"];
}

function isInvalidNhsNumber($nhsNumber) {
  $normalised = $nhsNumber;

  if (strlen($normalised) == 0) {
    return false;
  }

  if (!preg_match('/^\d{10}$/', $normalised)) {
    return true;
  }

  $total = 0;

  for ($i = 0; $i < 9; $i++) {
    $total += ($normalised[$i] * (10 - $i));
  }

  $calculatedCheckDigit = 11 - ($total % 11);

  if ($calculatedCheckDigit == 11) {
    $calculatedCheckDigit = 0;
  } elseif ($calculatedCheckDigit == 10) {
    return true;
  }

  return ($calculatedCheckDigit != $normalised[9]);
}

function getFormattedNhsNumber($nhsNumber) {
  return preg_replace('/\s+/', '', $nhsNumber);
}

function isInvalidUhlSystemNumber($uhlSystemNumber) {
  return !preg_match('/^[Ss]\d{7}$/', $uhlSystemNumber);
}

function isInvalidEmailAddress($emailAddress) {
  $emailMatcher = "[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]*)\b";
  return !preg_match("/^(" . $emailMatcher . ")(," . $emailMatcher . ")*,?$/i", $emailAddress);
}

function isInvalidGpCode($code) {
  return !preg_match('/^[A-Z]\d{7}$/', $code);
}

function isInvalidGpBranchCode($code) {
  return !preg_match('/^[A-Z]\d{8}$/', $code);
}

function isInvalidGpPracticeCode($code) {
  return !preg_match('/^[A-Z]\d{5}$/', $code);
}


function getFormattedUhlSystemNumber($uhlSystemNumber) {
  return strtoupper(preg_replace('/\s+/', '', $uhlSystemNumber));
}

function isDateStringValid($dateString) {
  if (empty($dateString)) {
    return FALSE;
  }

  $parsedDate = date_parse($dateString);

  if (empty($parsedDate)) {
    return FALSE;
  }
  return ($parsedDate["error_count"] == 0);
}

function isDateInRange($date, $startDate, $endDate) {
  return ( ( $date >= $startDate ) && ( $date <= $endDate ) );
}

function isDateValidAndInRange($date, $startDate, $endDate) {
  if (!isDateStringValid($date)) {
    return FALSE;
  }
  return isDateInRange(strtotime($date), $startDate, $endDate);
}

function lcbru_get_contact_by_custom_field($filterFieldName, $filterValue) {
  
  if (empty($filterValue)) {
    return null;
  }

  $filter_number_field = get_civi_custom_field($filterFieldName);

  $response = civicrm_api('Contact', 'Get', array(
                'custom_' . $filter_number_field['id'] => $filterValue,
                'version' =>3)
                );

  if ($response['count'] > 1) {
    throw new Exception(
        "There is more than one CiviCRM record with a $filterFieldName of '$filterValue'. This is a fatal error. Please search manually and fix this.");
  }

  if ($response['count'] == 1) {
    $result = $response["values"][$response["id"]];
    return $result;
  } else {
    return null;
  }

}

function lcbru_get_participant_by_custom_field($filterFieldName, $filterValue) {
  // TO DO - refactor to use the function lcbru_get_contact_by_custom_field
  if (empty($filterValue)) {
    return null;
  }

  $filter_number_field = get_civi_custom_field($filterFieldName);
  $s_number_field = get_civi_custom_field(CIVI_FIELD_S_NUMBER);
  $nhs_number_field = get_civi_custom_field(CIVI_FIELD_NHS_NUMBER);

  $response = civicrm_api('Contact', 'Get', array(
                'custom_' . $filter_number_field['id'] => $filterValue,
                'return' => 'custom_' . $s_number_field['id'] . ',custom_' . $nhs_number_field['id'] . ',birth_date,gender_id,gender,street_address,supplemental_address_1,supplemental_address_2,city,postal_code,state_province,country',
                'version' =>3)
                );

  if ($response['count'] > 1) {
    throw new Exception(
        "There is more than one CiviCRM record with a $filterFieldName of '$filterValue'. This is a fatal error. Please search manually and fix this.");
  }

  if ($response['count'] == 1) {
    $result = $response["values"][$response["id"]];
    // Copy the custom fields to reasonable names
    if (array_key_exists('custom_' . $s_number_field['id'], $result)) {
      $result[CIVI_FIELD_S_NUMBER] = getFormattedUhlSystemNumber($result['custom_' . $s_number_field['id']]);
    } else {
      $result[CIVI_FIELD_S_NUMBER] = '';
    }
    if (array_key_exists('custom_' . $nhs_number_field['id'], $result)) {
      $result[CIVI_FIELD_NHS_NUMBER] = getFormattedNhsNumber($result['custom_' . $nhs_number_field['id']]);
    } else {
      $result[CIVI_FIELD_NHS_NUMBER] = '';
    }

    return $result;
  } else {
    return null;
  }

}

function lcbru_get_participant_by_nhs_or_s_number($s_number, $nhs_number) {
  $s_number = getFormattedUhlSystemNumber($s_number);
  $nhs_number = getFormattedNhsNumber($nhs_number);

  $result = lcbru_get_participant_by_custom_field(CIVI_FIELD_S_NUMBER, $s_number);

  if (is_null($result)) {
    $result = lcbru_get_participant_by_custom_field(CIVI_FIELD_NHS_NUMBER, $nhs_number);
  }

  if (is_null($result)) {
    return null;
  }

  if (!empty($s_number) && !empty($result[CIVI_FIELD_S_NUMBER])) {
    if ($s_number != $result[CIVI_FIELD_S_NUMBER]) {
      throw new Exception(
          "S Numbers do not match.  Existing is '{$result[CIVI_FIELD_S_NUMBER]}', but provided is '$s_number' for record with NHS Number = '{$result[CIVI_FIELD_NHS_NUMBER]}'");
    }
  }

  if (!empty($nhs_number) && !empty($result[CIVI_FIELD_NHS_NUMBER])) {
    pp($s_number, 'S Number');
    pp($nhs_number, 'NHS Number');
    if ($nhs_number != $result[CIVI_FIELD_NHS_NUMBER]) {
      throw new Exception(
          "NHS Numbers do not match.  Existing is '{$result[CIVI_FIELD_NHS_NUMBER]}', but provided is '$nhs_number' for record with S Number = '{$result[CIVI_FIELD_S_NUMBER]}'");
    }
  }

  return $result;
}

function lcbru_synchronise_participant_nhs_and_s_number($s_number, $nhs_number) {
  $s_number = getFormattedUhlSystemNumber($s_number);
  $nhs_number = getFormattedNhsNumber($nhs_number);

  $participant = lcbru_get_participant_by_nhs_or_s_number($s_number, $nhs_number);

  if (is_null($participant)) {
    return null;
  }

  $amendedIds = array();

  if (!empty($s_number)
        && $s_number != $participant[CIVI_FIELD_S_NUMBER]) {
    $amendedIds[CIVI_FIELD_S_NUMBER] = $s_number;
  }


  if (!empty($nhs_number)
        && $nhs_number != $participant[CIVI_FIELD_NHS_NUMBER]) {
    $amendedIds[CIVI_FIELD_NHS_NUMBER] = $nhs_number;
  }

  if (!empty($amendedIds)) {
    create_civi_contact_with_custom_data(
      array('id' => $participant['id']),
      $amendedIds
    );
  }
}

function lcbru_get_practice_contact_id_by_ice_code_quick($iceCode) {
    //Main Sites
    $result = _lcbru_get_practice_contact_id_by_ice_code_getValidContact_quick($iceCode, array(
        'location_type_id' => '3'
    ));

    if ($result != null) {
        return $result;
    }

    //Branch Sites
    $result = _lcbru_get_practice_contact_id_by_ice_code_getValidContact_quick($iceCode, array(
        'location_type_id' => '4'
    ));

    return $result;
}

function _lcbru_get_practice_contact_id_by_ice_code_getValidContact_quick($iceCode, $params) {
    $iceCodeColumnName = lcbru_get_custom_field_id_name(CIVI_FIELD_ICE_LOCATION);

    $allParams = array_merge($params, array(
        'return' => "$iceCodeColumnName,contact_id"
    ));

    $addresses = lcbru_civicrm_api_getall('Address', $allParams);

    foreach($addresses as $address) {
        if (array_key_exists($iceCodeColumnName, $address)) {
            if ($address[$iceCodeColumnName] == $iceCode) {
                $contactId = $address['contact_id'];
                $contact = lcbru_get_contact_for_contactId($contactId);

                if (!is_null($contact) && $contact['contact_is_deleted'] == '0') {
                  return $contactId;
                }
            }
        }
    }

    return null;
}


function lcbru_get_address_for_addressId($addressId) {

  $address = civicrm_api('Address','get',array('version' => '3', 'id' => $addressId));

  if ($address['is_error']) {
    throw new Exception("Error looking for Address with address ID: $addressId: " . $address['error_message']);
  } else if ($address['count'] > 1) {
    throw new Exception("Error looking for Address with address ID: $addressId: found more than one address match to that ICE code, please check manually.");
  }

  if ($address['count'] == 1) {
    return array_shift($address['values']);
  } else {
    return null;
  }
}

function lcbru_get_contact_for_contactId($contactId) {

  $contact = civicrm_api('Contact','get',array('version' => '3', 'id' => $contactId));

  if ($contact['is_error']) {
    throw new Exception("Error looking for Contact with contact ID: $contactId: " . $contact['error_message']);
  } else if ($contact['count'] > 1) {
    throw new Exception("Error looking for Contact with contact ID: $contactId: found more than one address match to that ICE code, please check manually.");
  }

  if ($contact['count'] == 1) {
    return array_shift($contact['values']);
  } else {
    return null;
  }
}

function lcbru_civicrm_api_getall($type, $parameters) {
  $result = array();

  $parameters['version'] = '3';

  $offset = 0;
  
  do {
    $parameters['options'] = array(
        'limit' => '50',
        'offset' => $offset
      );
    
    $chunk = civicrm_api($type, 'get', $parameters);

    if (!empty($chunk['is_error'])) {
      throw new Exception("Error using API get for $type: " . $chunk['error_message']);
    }
    
    $result = array_merge($result,$chunk['values']);
    $offset += 50;
  } while ($chunk['count'] <> '0');

  return $result;
}

function lcbru_string_starts_with($string, $substring) {
  return !strncmp($string, $substring, strlen($substring));
}

function lcbru_get_contact_s_number($contactID) {
    $sNumberField = get_civi_custom_value($contactID, CIVI_FIELD_SET_IDENTIFIERS, CIVI_FIELD_S_NUMBER);
    return $sNumberField['latest'];
}

function lcbru_get_custom_field_id_name($name) {
    $standardName = str_replace(" ","_",$name);
    $cv = get_civi_custom_field_byName($standardName);
    return "custom_$cv[id]";
}

function lcbru_get_custom_group_table_name($name) {
    $cv = get_civi_custom_group_byName($name);
    return $cv['table_name'];
}

function lcbru_get_custom_field_column_name($name) {
    $cv = get_civi_custom_field_byName($name);
    return $cv['column_name'];
}
