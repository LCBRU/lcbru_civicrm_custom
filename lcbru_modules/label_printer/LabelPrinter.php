<?php

/**
 * This class provides utility functions for printing labels
 * 
 * Example usage:
 *
 * <code>
 *
 * // Print a label
 *
 *   $lp = new LabelPrinter($hostnameOrIpAddress, $port);
 *   $lp->printLabel($labelContent, $labelCount);
 *
 * </code>
 * 
*/

class LabelPrinter
{
/*
  const PRINTER_CVRC_LAB_SAMPLE = '10.161.54.248';
  const PRINTER_BRU_CRF_SAMPLE = '10.161.54.248';
  const PRINTER_BRU_CRF_BAG = '10.161.54.248';
  const PRINTER_TMF_SAMPLE = '10.161.54.248';
  const PRINTER_TMF_BAG = '10.161.54.248';
  const PRINTER_ADDRESS = '10.161.54.248';
*/

  const PRINTER_CVRC_LAB_SAMPLE = 'ZBR3713388';
  const PRINTER_BRU_CRF_SAMPLE = '125.200.4.45';
  const PRINTER_BRU_CRF_BAG = '125.200.4.46';
  const PRINTER_TMF_SAMPLE = '10.161.60.121';
  const PRINTER_TMF_BAG = '10.161.60.122';
  const PRINTER_ADDRESS = '10.161.54.248';

  const LABEL_BAG_PREDICT = 'PredictBag.zpl';
  const LABEL_BAG_CARDIOMET = 'CardiometBag.zpl';
  const LABEL_BAG_CARDIOMET_WITH_PURPLE = 'CardiometBag_WithPurple.zpl';
  const LABEL_BAG_LENTEN_EDTA = 'LentenEdtaBag.zpl';
  const LABEL_BAG_LENTEN_UHL = 'LentenUhlBag.zpl';
  const LABEL_BAG_URINE = 'UrineLabel.zpl';
  const LABEL_BAG_CITRATE = 'CitrateLabel.zpl';
  const LABEL_BAG_SERUM = 'SerumLabel.zpl';
  const LABEL_BAG_EDTA_EXTERNAL = 'EdtaExternalLabel.zpl';
  const LABEL_BAG_SERUM_POLISH = 'SerumPolish.zpl';
  const LABEL_BAG_CITRATE_POLISH = 'CitratePolish.zpl';
  const LABEL_SAMPLE = 'SampleLabel.zpl';
  const LABEL_SAMPLE_AND_MESSAGE = 'SampleAndMessageLabel.zpl';
  const LABEL_BAG_PE = 'PeBagLabels.zpl';
  const LABEL_PARTICIPANT_BAG = 'participant_bag.zpl';
  const LABEL_SERUN_AND_EDTA_BAG = 'SerumAndEdtaBag.zpl';
  const LABEL_INDAPAMIDE_SERUM_BAG = 'IndapamideSampleBag_Serum.zpl';
  const LABEL_INDAPAMIDE_EDTA_BAG = 'IndapamideSampleBag_EDTA.zpl';
  const LABEL_INDAPAMIDE_URINE_BAG = 'IndapamideSampleBag_Urine.zpl';
  const LABEL_RECRUITED_PATIENT_NOTES = 'RecruitedLabelForPatientNotes.zpl';

  const PRINTER_PORT = '9100';

  public function __construct($printername) {

    Guard::AssertString_InArray('$printername', $printername, array(
        LabelPrinter::PRINTER_CVRC_LAB_SAMPLE,
        LabelPrinter::PRINTER_BRU_CRF_SAMPLE,
        LabelPrinter::PRINTER_BRU_CRF_BAG,
        LabelPrinter::PRINTER_TMF_SAMPLE,
        LabelPrinter::PRINTER_TMF_BAG,
        LabelPrinter::PRINTER_ADDRESS,
      ));

    $this->hostnameOrIpAddress = $printername;
  }

  public function printLabel($labelContent, $labelCount) {
    Guard::AssertString_NotEmpty('$labelContent', $labelContent);
    Guard::AssertInteger('$labelCount', $labelCount);

    usleep ( 500000 );

    $labelContentWithQuantity = str_replace('{LABEL_QUANTITY}', $labelCount, $labelContent);
    try {
      $socket = $this->openSocket($this->hostnameOrIpAddress, LabelPrinter::PRINTER_PORT);

      if(!socket_send ($socket, $labelContentWithQuantity, strlen($labelContentWithQuantity) , 0)) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        throw new Exception("Could not send data to labels printer: [$errorcode] $errormsg \n");
      } 

      socket_close($socket);

      } catch(exception $e) {

        pp($e);
        if (isset($socket)) {
          socket_close($socket);
        }
        throw $e;
      }
  }

  public function printLabelFromTemplateFile($templateFilePath, array $fields, $labelCount) {
    Guard::AssertString_NotEmpty('$templateFilePath', $templateFilePath);
    Guard::AssertArray('$fields', $fields);
    Guard::AssertInteger('$labelCount', $labelCount);

    $this->printLabel($this->getLabelFromTemplateFile($templateFilePath, $fields), $labelCount);
  }

  public function getLabelFromTemplateFile($templateFilePath, array $fields) {
    Guard::AssertString_NotEmpty('$templateFilePath', $templateFilePath);
    Guard::AssertArray('$fields', $fields);

    $template = file_get_contents(drupal_get_path('module','label_printer') . '/' . $templateFilePath);

    Guard::AssertString_NotEmpty('$template', $template);

    return str_replace(array_keys($fields), array_values($fields), $template);

  }

  public function printStudySample($participantId, $labelCount) {
    Guard::AssertString_NotEmpty('$participantId', $participantId);
    Guard::AssertInteger('$labelCount', $labelCount);

    $this->printLabelFromTemplateFile(
        'SampleLabel.zpl',
        array (
            '{ID_PLACEHOLDER}' => $participantId,
          ),
        $labelCount
      );
  }
  
  public function printStudyBag($participantId, $studyName, $labelBagType, $labelCount) {
    Guard::AssertString_NotEmpty('$participantId', $participantId);
    Guard::AssertString_NotEmpty('$studyName', $studyName);
    Guard::AssertInteger('$labelCount', $labelCount);
    Guard::AssertString_InArray('$labelBagType', $labelBagType, array(
        LabelPrinter::LABEL_BAG_URINE,
        LabelPrinter::LABEL_BAG_CITRATE,
        LabelPrinter::LABEL_BAG_SERUM,
        LabelPrinter::LABEL_BAG_SERUM_POLISH,
        LabelPrinter::LABEL_BAG_CITRATE_POLISH,
        LabelPrinter::LABEL_INDAPAMIDE_BAG,
        LabelPrinter::LABEL_BAG_LENTEN_EDTA,
        LabelPrinter::LABEL_BAG_LENTEN_UHL,
        LabelPrinter::LABEL_BAG_CARDIOMET,
        LabelPrinter::LABEL_BAG_CARDIOMET,
      ));


    $this->printLabelFromTemplateFile(
        $labelBagType,
        array (
            '{ID_PLACEHOLDER}' => $participantId,
            '{STUDY_PLACEHOLDER}' => $studyName
          ),
        $labelCount
      );
  }

  private function openSocket($printer_address, $printer_port) {
    Guard::AssertString_NotEmpty('$printer_address', $printer_address);
    Guard::AssertInteger('$printer_port', $printer_port);

    if(!($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
      throw new Exception("Couldn't create socket for labels printer: [$errorcode] $errormsg \n");
    }
    
    if(!socket_connect($socket, $printer_address, $printer_port)) {
      $errorcode = socket_last_error();
      $errormsg = socket_strerror($errorcode);
      throw new Exception("Couldn't establish connection for labels printer: [$errorcode] $errormsg \n");
    }
    return $socket;
  }
}
