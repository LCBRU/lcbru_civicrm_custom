<?php

/**
 * @file
 * Genvasc Invoicing
 */

require_once("GenvascInvoicing.php");

function _genvasc_labels_invoicing_tab($action = '',$a = '', $b = '') {

  switch ($action) {
    case 'create_invoice':
      return drupal_get_form('_genvasc_labels_invoicing_create_form');
      break;
    case 'practice_summary':
      return _genvasc_labels_invoicing_practice_summary($a);
      break;
    case 'practice_participants':
      return _genvasc_labels_invoicing_practice_participants($a, $b);
      break;
    default:
      return drupal_get_form('_genvasc_labels_invoicing_tab_form');
      break;
  }
}

function _genvasc_labels_invoicing_tab_form($form, &$form_state) {

  $outstandingParticipants = GenvascInvoicing::getOutstandingParticipantCount();

  $form['Invoice_Participants'] = array(
    '#type' => 'link',
    '#title' => t("Submit $outstandingParticipants outstanding participants."),
    '#href' => 'content/genvasc_invoicing/create_invoice/',
  );

  $form['invoices'] = array(
    '#type' => 'item',
    '#title' => 'Invoices',
    '#markup' => _genvasc_labels_invoicing_invoice_list()
    );

  return $form;
}

function _genvasc_labels_invoicing_invoice_list() {
	$invoiceDetails = GenvascInvoicing::getInvoiceSummary();

  $rows = array();

  foreach (GenvascInvoicing::getInvoiceSummary() as $inv) {
    $links = array();

    $links[] = l('View',"content/genvasc_invoicing/practice_summary/{$inv['invoice_year']}:{$inv['invoice_quarter']}");

    $rows[] = array(
      $inv['invoice_year'],
      $inv['invoice_quarter'],
      $inv['participant_count'],
      join(' ', $links)
      );
  }

  $header = array(
    array(
      'data' => t('Year'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('Quarter'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('Participants'),
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
    array(
      'data' => '',
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
  );

  return theme('datatable', array('header' => $header, 'attributes' => array('datatable_options' => array()), 'rows' => $rows));
}

function _genvasc_labels_invoicing_practice_summary($year_quater) {
  $year_and_quarter = explode (':', $year_quater);

  $rows = array();

  foreach (GenvascInvoicing::getInvoicePracticeSummary($year_and_quarter[0], $year_and_quarter[1]) as $inv) {
    $links = array();

    $links[] = l('View',"content/genvasc_invoicing/practice_participants/{$year_quater}/{$inv['practice_id']}");

    $rows[] = array(
      $inv['practice'],
      $inv['participant_count'],
      join(' ', $links)
      );
  }

  $header = array(
    array(
      'data' => t('Practice'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('Participants'),
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
    array(
      'data' => '',
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
  );

  return theme('datatable', array('header' => $header, 'attributes' => array('datatable_options' => array()), 'rows' => $rows));
}

function _genvasc_labels_invoicing_practice_participants($year_quater, $practice_id) {
  $year_and_quarter = explode (':', $year_quater);

  $rows = array();

  foreach (GenvascInvoicing::getInvoicePracticeParticipants($year_and_quarter[0], $year_and_quarter[1], $practice_id) as $inv) {
    $links = array();

    $links[] = l('Manage', CaseHelper::getCaseUrl($inv['contact_id'], $inv['case_id']));

    $rows[] = array(
      $inv['gpt_number'],
      $inv['display_name'],
      join(' ', $links)
      );
  }

  $header = array(
    array(
      'data' => t('Study ID'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => t('Participants'),
      'datatable_options' => unserialize(DATATABLES_SEARCHABLE_COLUMN_OPTION)
    ),
    array(
      'data' => '',
      'datatable_options' => unserialize(DATATABLES_LINK_COLUMN_OPTION)
    ),
  );

  return theme('datatable', array('header' => $header, 'attributes' => array('datatable_options' => array()), 'rows' => $rows));
}

function _genvasc_labels_invoicing_create_form($form, &$form_state) {
	$current_year = date("Y");
  $year_options = array_map(function($year) { return "$year-" . ($year + 1) % 1000; }, range($current_year-1,$current_year+1));

	$form['invoice_year'] = array(
		'#type' => 'select',
		'#title' => t('Invoice Year'),
		'#options' => array_combine($year_options, $year_options),
		'#default_value' => $year_options[1],
	);

	$form['invoice_quarter'] = array(
		'#type' => 'select',
		'#title' => t('Invoice Quarter'),
		'#options' => array(
			'Q1' => 'Q1',
			'Q2' => 'Q2',
			'Q3' => 'Q3',
			'Q4' => 'Q4',
		),
		'#default_value' => 'Q1',
	);

	$outstandingParticipants = GenvascInvoicing::getOutstandingParticipantCount();

	return confirm_form($form,"Invoice $outstandingParticipants outstanding participants.",'content/genvasc_invoicing');
}

function _genvasc_labels_invoicing_create_form_validate($form, &$form_state) {
	$current_year = date("Y");

	$invoice_year = $form_state['values']['invoice_year'];
	$invoice_quarter = $form_state['values']['invoice_quarter'];

	if (!in_array($invoice_year, range($current_year-1,$current_year+1))) {
	    form_set_error('invoice_year', 'Invoice year not in valid range.');
	}
	if (!in_array($invoice_quarter, array('Q1', 'Q2', 'Q3', 'Q4'))) {
	    form_set_error('invoice_quarter', 'Invoice quarter is not valid.');
	}
}

function _genvasc_labels_invoicing_create_form_submit($form, &$form_state) {

    set_time_limit(500);

	GenvascInvoicing::invoiceOutstandingParticipants(
		$form_state['values']['invoice_year'],
		$form_state['values']['invoice_quarter']
	);

	drupal_goto('content/genvasc_invoicing');
}
