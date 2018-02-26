<?php

/**
 * This class provides utility functions to interact with the PMI
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   ph = new PmiHelper();
 *   ph->import_address($contact_id)
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class PmiHelper
{
    function import_address($contactID) {
        Guard::AssertInteger('$contactID', $contactID);

        $ch = new ContactHelper();
        $contact = $ch->getSubjectFromId($contactID);

        if (empty($contact[CIVI_FIELD_S_NUMBER])) {
            return;
        }

        $pmiDetails = $this->get_pmi_details($contact[CIVI_FIELD_S_NUMBER]);

        if (empty($pmiDetails)) {
            return;
        }

        return AddressHelper::create_address(
            $contactID,
            $this->get_address_from_pmi($pmiDetails),
            True,
            1,
            ArrayHelper::get($contact, 'address_id')
        );
    }

    function add_pmi_details(array $contact_details) {
        $result = array();

        foreach ($contact_details as $p) {
            $pmi_details = $this->get_pmi_details($p[ContactHelper::UHL_SYSTEM_NUMBER_FIELD_NAME]);
            $p['NHS_number'] = getFormattedNhsNumber($pmi_details['nhs_number']);
            $p['first_name'] = $this->name_case($pmi_details['first_name']);
            $p['middle_name'] = $this->name_case($pmi_details['middle_name']);
            $p['last_name'] = $this->name_case($pmi_details['last_name']);
            $p['is_deceased'] = $pmi_details['death_indicator'];
            $pmiAddress = $this->get_address_from_pmi($pmi_details);

            $result[] = array_merge($p, $pmiAddress);
        }

        return $result;
    }

    function get_pmi_details($sNumber) {
        if (empty($sNumber)) {
            return NULL;
        }

        $result = NULL;

        db_set_active('PmiDb');

        try {
            $queryResult = db_query("Select * FROM UHL_PMI_QUERY_BY_ID( :sNumber )", array(':sNumber' => $sNumber));

            $pmiDetails = $queryResult->fetchAssoc();

            // The PMI helpfully returns a row of NULLs if it
            // doesn't find a record.  Therefore, if the 'main_pat_id'
            // is NULL, nothing was found.

            if (!is_null($pmiDetails['main_pat_id'])) {
                $result = $pmiDetails;
            }

        } catch (Exception $ex) {
            db_set_active();
            throw $ex;
        } finally {
            db_set_active();
        }

        return $result;
    }


    function get_address_from_pmi($pmiDetails) {
        if (!is_null($pmiDetails)) {
            return addressSplit(array(
                $pmiDetails["pat_addr1"],
                $pmiDetails["pat_addr2"],
                $pmiDetails["pat_addr3"],
                $pmiDetails["pat_addr4"],
                $pmiDetails["postcode"]
            ));
            }
    }


    function name_case($name){
        if (is_null($name)) {
            return '';
        }
        Guard::AssertString('$name', $name);
        return ucwords(strtolower($name));
// Have to remove until PHP is updated
//        return ucwords(strtolower($name), "-' \t\r\n\f\v");
    }


}
