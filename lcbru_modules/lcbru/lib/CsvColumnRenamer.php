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
 *
 *   $rcsv = new CsvColumnRenamer($csv, array('old_column_name' => 'new_column_name', 'old_column_name_2' => 'new_column_name_2'));
 *
 *   if ($rcsv.hasValidationErrors()) {
 *       foreach($rcsv.getValidationErrors() as $error) {
 *          ... do something with error
 *       }
 *   }
 *
 *   $rcsv->foreachRow(function($row){
 *       ... do something with row
 *    });
 * }
 * </code>
 * 
*/

class CsvColumnRenamer
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
            $renamed_row = array();

            foreach ($this->column_mapping as $old_column_name => $new_column_name) {
                $renamed_row[$new_column_name] = $row[$old_column_name];
            }

            $func($renamed_row);
        });
    }
}
