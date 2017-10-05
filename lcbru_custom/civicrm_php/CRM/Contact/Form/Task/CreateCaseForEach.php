 <?php

class CRM_Contact_Form_Task_CreateCaseForEach extends CRM_Contact_Form_Task {
	public $_context = 'case';
	public $_caseTypeId = NULL;
	public $_currentlyViewedContactId = NULL;
	public $_activityTypeId = NULL;
	public $_allowMultiClient = FALSE;
	public $_activityId = NULL;
	public $_currentUserId = NULL;

	function preProcess() {

		parent::preProcess();

		$this->_activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Open Case', 'name');
		if (!$this->_activityTypeId) {
			CRM_Core_Error::fatal(ts('The Open Case activity type is missing or disabled. Please have your site administrator check Administer > Option Lists > Activity Types for the CiviCase component.'));
		}

		if ($this->_activityTypeFile = CRM_Activity_BAO_Activity::getFileForActivityTypeId($this->_activityTypeId, 'Case')) {
			$this->assign('activityTypeFile', $this->_activityTypeFile);
		}

		$details = CRM_Case_PseudoConstant::caseActivityType(FALSE);

	    CRM_Utils_System::setTitle($details[$this->_activityTypeId]['label']);
	    $this->assign('activityType', $details[$this->_activityTypeId]['label']);
	    $this->assign('activityTypeDescription', $details[$this->_activityTypeId]['description']);

		$session = CRM_Core_Session::singleton();
		$this->_currentUserId = $session->get('userID');
	}

	function setDefaultValues() {
		$className = "CRM_Case_Form_Activity_{$this->_activityTypeFile}";
		$defaults = $className::setDefaultValues($this);
		return $defaults;
	}

	function buildQuickForm() {
	    $s = CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity', 'subject');
	    if (!is_array($s)) {
	      $s = array();
	    }
	    $this->add('text', 'activity_subject', ts('Subject'),
	      array_merge($s, array(
	        'maxlength' => '128')), TRUE
	    );
		$className = "CRM_Case_Form_Activity_{$this->_activityTypeFile}";
		$className::buildQuickForm($this);
		$this->addDefaultButtons(ts('Create Cases'));
    }

	public function postProcess( ) {
		$transaction = new CRM_Core_Transaction();

   		$params =$this->controller->exportValues();

		if (CRM_Utils_Array::value('case_type_id', $params)) {
			$caseType = CRM_Case_PseudoConstant::caseType('name');
			$params['case_type'] = $caseType[$params['case_type_id']];
			$params['subject'] = $params['activity_subject'];
		//	$params['case_type_id'] = CRM_Core_DAO::VALUE_SEPARATOR . $params['case_type_id'] . CRM_Core_DAO::VALUE_SEPARATOR;
		}

		if (is_array($this->_contactIds)) {
	   		foreach ($this->_contactIds as $cId) {
	   			self::createCase($cId, $params);
	   		}
		} else {
			self::createCase($this->_contactIds, $params);
		}

		CRM_Core_Session::setStatus(ts('Cases created'), ts('Created'), 'success');
   	}

   	private function createCase($contactID, $params) {
		$this->_currentlyViewedContactId = $contactID;
   		
		// 1. call begin post process
		if ($this->_activityTypeFile) {
			$className = "CRM_Case_Form_Activity_{$this->_activityTypeFile}";
			$className::beginPostProcess($this, $params);
		}

		// 2. create/edit case
		$caseObj = CRM_Case_BAO_Case::create($params);
		$params['case_id'] = $caseObj->id;

		unset($params['id'], $params['custom']);

		// 3. call end post process
		if ($this->_activityTypeFile) {
			$className::endPostProcess($this, $params );
		}

   	}

   	// What does this do?
	function addRules() {
		$className = "CRM_Case_Form_Activity_{$this->_activityTypeFile}";
		$this->addFormRule(array($className, 'formRule'), $this);
		$this->addFormRule(array('CRM_Case_Form_Case', 'formRule'), $this);
	}

    // What does this do?
	static function formRule($values, $files, $form) {
		return TRUE;
	}
} 
