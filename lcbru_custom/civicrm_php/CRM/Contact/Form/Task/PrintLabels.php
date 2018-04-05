 <?php

class CRM_Contact_Form_Task_PrintLabels extends CRM_Contact_Form_Task {

    const TEMPLATE_START = '
^XA~TA000~JSN^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD10^JUS^LRN^CI0^XZ
^XA^LL0300
^FO650,1020^AT^FD{NAME}^FS
^PQ{LABEL_QUANTITY},0,1,Y^XZ
';

	function preProcess() {
		parent::preProcess();
	}

    function buildQuickForm() {
        CRM_Utils_System::setTitle(ts('Print Labels for Each Subject'));

        $label_type_options = $this->_get_label_type_options();
        $label_count_options = array(
            1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
        );

        $this->add('static', 'contact_count', NULL, count($this->_contactIds));
        $this->add('select', 'label_type', ts('Label Type'), $label_type_options, TRUE);
        $this->add('select', 'label_count', ts('Labels per Person'), $label_count_options, TRUE);

        $this->addDefaultButtons(ts('Print Labels'));
    }

	public function postProcess( ) {
        $options =$this->controller->exportValues();
        $ch = new ContactHelper();
        $sensibleContactIdsArray = array();

        if (is_array($this->_contactIds)) {
            $sensibleContactIdsArray = $this->_contactIds;
        } else {
            $sensibleContactIdsArray[] = $this->_contactIds;
        }

        $label = self::_get_label_types()[$options['label_type']];
        $label_count = $options['label_count'];

        $printer = new LabelPrinter($label['printer']);

        $contacts = $ch->getSubjectsFromContactIds($sensibleContactIdsArray);

        foreach ($contacts as $contact) {
            $address = AddressHelper::extract_combined_address($contact, True);

            $address = array_combine(
                array(
                    'ADDRESS_1',
                    'ADDRESS_2',
                    'ADDRESS_3',
                    'ADDRESS_4',
                    'ADDRESS_5',
                    'ADDRESS_6',
                )
                ,array_pad($address, 6, '')
            );

            if (!empty($address)) {
                $contact = array_merge($contact, $address);
            }

            $fields = array_map(function ($x) use ($contact) { return $contact[$x]; }, $label['place_holders']);

            $content = str_replace(array_keys($fields), array_values($fields), $label['template']);

            $printer->printLabel($content, $label_count);
        }

    }


    private function _get_label_types(){
        return array(
            'Name_Only' => array(
                'name' => 'Name Only',
                'printer' => LabelPrinter::PRINTER_CVRC_LAB_SAMPLE,
                'place_holders' => array(
                    '{FIRST_NAME}' => 'first_name',
                    '{LAST_NAME}' => ContactHelper::LAST_NAME_UPPER,
                ),
                'template' => '
^XA~TA000~JSN^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD10^JUS^LRN^CI0^XZ
^XA^LL0300
^FO40,60^AU^FD{FIRST_NAME}^FS
^FO40,120^AU^FD{LAST_NAME}^FS
^PW600
^PQ{LABEL_QUANTITY},0,1,Y^XZ
',
            ),
            'Name_Dob' => array(
                'name' => 'Name and DOB',
                'printer' => LabelPrinter::PRINTER_CVRC_LAB_SAMPLE,
                'place_holders' => array(
                    '{FIRST_NAME}' => 'first_name',
                    '{LAST_NAME}' => ContactHelper::LAST_NAME_UPPER,
                    '{DOB}' => ContactHelper::DISPLAY_DATE_OF_BIRTH,
                ),
                'template' => '
^XA~TA000~JSN^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD10^JUS^LRN^CI0^XZ
^XA^LL0300
^FO40,60^AU^FD{FIRST_NAME}^FS
^FO40,120^AU^FD{LAST_NAME}^FS
^FO40,190^AS^FDDate of Birth: {DOB}^FS
^PW600
^PQ{LABEL_QUANTITY},0,1,Y^XZ
',
            ),
            'Name_Nhs' => array(
                'name' => 'Name and NHS Number',
                'printer' => LabelPrinter::PRINTER_CVRC_LAB_SAMPLE,
                'place_holders' => array(
                    '{FIRST_NAME}' => 'first_name',
                    '{LAST_NAME}' => ContactHelper::LAST_NAME_UPPER,
                    '{NHS}' => 'NHS number',
                ),
                'template' => '
^XA~TA000~JSN^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD10^JUS^LRN^CI0^XZ
^XA^LL0300
^FO40,60^AU^FD{FIRST_NAME}^FS
^FO40,120^AU^FD{LAST_NAME}^FS
^FO40,190^AS^FDNHS Number: {NHS}^FS
^PW600
^PQ{LABEL_QUANTITY},0,1,Y^XZ
',
            ),
            'Address' => array(
                'name' => 'Address',
                'printer' => LabelPrinter::PRINTER_ADDRESS,
                'place_holders' => array(
                    '{FIRST_NAME}' => 'first_name',
                    '{LAST_NAME}' => ContactHelper::LAST_NAME_UPPER,
                    '{ADDRESS_1}' => 'ADDRESS_1',
                    '{ADDRESS_2}' => 'ADDRESS_2',
                    '{ADDRESS_3}' => 'ADDRESS_3',
                    '{ADDRESS_4}' => 'ADDRESS_4',
                    '{ADDRESS_5}' => 'ADDRESS_5',
                    '{ADDRESS_6}' => 'ADDRESS_6',
                ),
                'template' => '
^XA
^FO250,60^AU^FD{FIRST_NAME} {LAST_NAME}^FS
^FO250,120^AU^FD{ADDRESS_1}^FS
^FO250,180^AU^FD{ADDRESS_2}^FS
^FO250,240^AU^FD{ADDRESS_3}^FS
^FO250,300^AU^FD{ADDRESS_4}^FS
^FO250,360^AU^FD{ADDRESS_5}^FS
^FO250,420^AU^FD{ADDRESS_6}^FS
^PQ{LABEL_QUANTITY},0,1,Y^XZ
',
            ),
        );
    }


    private function _get_label_type_options(){
        return array_map(function ($x) { return $x['name']; }, $this->_get_label_types());
    }
}