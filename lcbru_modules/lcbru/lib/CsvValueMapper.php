<?php

/**
 * This class renames the columns of a CSV file.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function($csvFilePath) {
 *
 *   $csv = new CsvHelper($csvFilePath, CsvHelper::NAMES_IN_HEADER);
 *   $mappingValue = array(
 *       'column_name' => array(
 *           'mapping' => array(
 *               'old_value' => 'new_value',
 *               'old_value_2' => 'new_value_2'
 *           )
 *           'value_if_no_mapping' => 'default value'
 *       )
 *   )
 *
 *   $mcsv = new CsvValueMapper($csv, array('old_column_name' => 'new_column_name', 'old_column_name_2' => 'new_column_name_2'));
 *
 *   if ($mcsv.hasValidationErrors()) {
 *       foreach($mcsv.getValidationErrors() as $error) {
 *          ... do something with error
 *       }
 *   }
 *
 *   $mcsv->foreachRow(function($row){
 *       ... do something with row
 *    });
 * }
 * </code>
 * 
*/

class CsvValueMapper
{
/**
 * Flags
 */

    /**
     * Constructor.
     *
     * @param CsvHelper $csv.
     * @param array $column_mapping
     */
     public function __construct($csv, array $column_mapping) {
        $this->csv = $csv;
        $this->column_mapping = $column_mapping;
    }

    /**
     * Returns whether the CSV file has validation errors.
     *
     * @return boolean
     */
    public function hasValidationErrors() {
        return $this->csv->hasValidationErrors();
    }

    /**
     * Returns a list of validation errors
     *
     * @return string[]
     */
    public function getValidationErrors() {
        return $this->csv->getValidationErrors();
    }

    /**
     * Utility function to run an anonymous function as a for each CSV row
     *
     * @param Closure $func anonymous function to run for each CSV row.
     *
     */
    public function foreachRow($func) {
        Guard::AssertFunction('$func', $func);

        $this->csv->foreachRow(function($row) use ($func) {
            $result = array();

            foreach ($row as $name => $value) {
                if (array_key_exists($name, $this->column_mapping)){
                    if (!empty($this->column_mapping[$name]['mapping']) && array_key_exists($value, $this->column_mapping[$name]['mapping'])) {
                        $result[$name] = $this->column_mapping[$name]['mapping'][$value];
                    } elseif (array_key_exists('value_if_no_mapping', $this->column_mapping[$name])) {
                        $result[$name] = $this->column_mapping[$name]['value_if_no_mapping'];
                    } elseif (!empty($this->column_mapping[$name]['title_case'])) {
                        $result[$name] = ucwords(strtolower($value));
                    } else {
                        $result[$name] = $value;
                    }
                } else {
                    $result[$name] = $value;
                }
            }

            $func($result);
        });
    }
}
