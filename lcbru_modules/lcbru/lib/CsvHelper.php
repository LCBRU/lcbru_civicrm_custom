<?php

/**
 * This class provides utility functions for working with CSV files.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function($csvFilePath) {
 *
 *   $csv = new CsvHelper($csvFilePath, CsvHelper::NAMES_IN_HEADER);
 *
 *   if ($csv.hasValidationErrors()) {
 *       foreach($csv.getValidationErrors() as $error) {
 *          ... do something with error
 *       }
 *   }
 *
 *   $csv->foreachRow(function($row){
 *       ... do something with row
 *    });
 * }
 * </code>
 * 
 * Things to note are:
 *
 *   + The constructor takes flag the values of which are defined in
 *     the class as constants.  Multiple flags can be defined by using
 *     the bitwise | operator
 *   + The foreachRow function takes either an anonymous function or a
 *     function name as it's argument.
*/

class CsvHelper
{
/**
 * Flags
 */
    const NAMES_IN_HEADER = 1;

    private $filePath;
    private $namesInHeader = False;
    private $validationErrors = null;

    /**
     * Constructor.
     *
     * @param string $filePath location of CSV file.
     * @param integer $flags optional parameters of CSV
     */
     public function __construct($filePath, $flags = 0) {
        Guard::AssertString('$filePath', $filePath);
        Guard::AssertInteger('$flags', $flags);

        $this->filePath = $filePath;
        $this->namesInHeader = $flags & Self::NAMES_IN_HEADER;
    }

    /**
     * Returns whether the CSV file has validation errors.
     *
     * @return boolean
     */
    public function hasValidationErrors() {
        return count($this->getValidationErrors()) > 0;
    }

    /**
     * Returns a list of validation errors
     *
     * @return string[]
     */
    public function getValidationErrors() {
        if (is_null($this->validationErrors)) {
            $this->validationErrors = array();

            if (($f = fopen($this->filePath,'r')) !== FALSE) {

                $header = fgetcsv($f);
                $row = 0;

                while (!feof($f)) {
                    $row++;
                    $record = fgetcsv($f);

                    if (count($record) == 1 && trim($record[0]) == "") {
                        continue;
                    }

                    if (count($record) != count($header)) {
                        $this->validationErrors[] = "CSV File Error: Row $row does not have the correct number of columns.";
                    }
                }

                fclose($f);
            } else {
                $this->validationErrors[] = "Could not open file '{$this->filePath}'.";
            }
        }

        return $this->validationErrors;
    }

    /**
     * Utility function to run an anonymous function as a for each CSV row
     *
     * @param Closure $func anonymous function to run for each CSV row.
     *
     */
    public function foreachRow($func) {
        Guard::AssertFunction('$func', $func);

        if (($f = fopen($this->filePath,'r')) !== FALSE) {

            if ($this->namesInHeader) {
                $header = array();

                foreach (fgetcsv($f) as $title) {
                    $header[] = trim($title);
                }
            }

            while (!feof($f)) {
                $record = fgetcsv($f);

                if (count($record) == 1 && trim($record[0]) == "") {
                    continue;
                }

                if ($this->namesInHeader) {
                    $row = array_combine($header, $record);
                } else {
                    $row = $record;
                }

                $func($row);
            }

            fclose($f);
        }
    }
}
