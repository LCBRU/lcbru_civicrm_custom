<?php

/**
 * This class provides utility functions for working with arrays.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   $value = ArrayHelper::get($array, $key, $default)
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class AddressHelper
{
    const DAPS_ADDRESS_MAX_ELEMENTS = 5;
    const DAPS_ADDRESS_MAX_ELEMENT_SIZE = 30;

    public static function getDapsAddress(array $contact_address) {

        $address = array_values(array_filter(ArrayHelper::selectColumns(
                $contact_address, 
                array(
                        'supplemental_address_1',
                        'street_address',
                        'supplemental_address_2',
                        'city',
                        'state_province_name'
                    ))));

        if (!AddressHelper::valid_address_format($address, AddressHelper::DAPS_ADDRESS_MAX_ELEMENTS, AddressHelper::DAPS_ADDRESS_MAX_ELEMENT_SIZE)) {
            $address = AddressHelper::repack_address($address, AddressHelper::DAPS_ADDRESS_MAX_ELEMENTS, AddressHelper::DAPS_ADDRESS_MAX_ELEMENT_SIZE);
        }

        return array_combine(array(
                'ADDRESS1',
                'ADDRESS2',
                'ADDRESS3',
                'ADDRESS4',
                'ADDRESS5'
            )
            ,array_pad($address, AddressHelper::DAPS_ADDRESS_MAX_ELEMENTS, '')
            );
    }

    public static function repack_address(array $address, $max_elements, $max_length) {
        Guard::AssertArrayOfStrings('$address', $address);
        Guard::AssertInteger('$max_elements', $max_elements);
        Guard::AssertMin('$max_elements', $max_elements, 1);
        Guard::AssertInteger('$max_length', $max_length);
        Guard::AssertMin('$max_length', $max_length, 1);

        // Split the address by comma into array elements
        $address = array_map('trim', array_values(ArrayHelper::array_iunique(
            array_filter(
                explode(',', join(',', $address))
                )
            )));

        $aggressive = false;

        while (!AddressHelper::valid_address_format($address, $max_elements, $max_length)) {
            $address_amended = false;

            for ($i=count($address) - 1; $i > 0; $i--) {
                $folded_line = $address[$i -1] . ', ' . $address[$i];

                if (strlen($folded_line) <= $max_length || $aggressive) {
                    $address[$i -1] = substr($folded_line, 0, $max_length);
                    unset($address[$i]);
                    $address = array_values($address);
                    $address_amended = true;
                    break;
                }
            }

            $aggressive = (!$address_amended || $aggressive);
        }

        return  $address;
    }

    public static function valid_address_format(array $address, $max_elements, $max_length) {
        Guard::AssertArrayOfStrings('$address', $address);
        Guard::AssertInteger('$max_elements', $max_elements);
        Guard::AssertMin('$max_elements', $max_elements, 1);
        Guard::AssertInteger('$max_length', $max_length);
        Guard::AssertMin('$max_length', $max_length, 1);

        if (count($address) > $max_elements) {
            return false;
        }

        foreach ($address as $al) {
            if (strlen($al) > $max_length) {
                return false;
            }
        }

        return true;
    }
}
