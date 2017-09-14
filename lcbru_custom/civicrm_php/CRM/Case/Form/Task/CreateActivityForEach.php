<?php
/**
 * This class allows an activity to be created for each case
 * found by a search.
 */
class CRM_Case_Form_Task_CreateActivityForEach extends CRM_Case_Form_Task {

  /**
   * The id of activity type
   *
   * @var int
   */
  public $_activityTypeId;

  /**
   * The name of activity type
   *
   * @var string
   */
  public $_activityTypeName;

  /**
   * The id of source contact and target contact
   *
   * @var int
   */
  protected $_sourceContactId;
  protected $_targetContactId;
  protected $_asigneeContactId;

  public $_single = FALSE;

  public $_context;
  public $_compContext;
  public $_action;
  public $_activityTypeFile;

  /**
   * The id of the logged in user, used when add / edit
   *
   * @var int
   */
  public $_currentUserId;

  /**
   * The array of form field attributes
   *
   * @var array
   */
  public $_fields;

  /**
   * The the directory inside CRM, to include activity type file from
   *
   * @var string
   */
  protected $_crmDir = 'Activity';

  /*
     * Survey activity
     *
     * @var boolean
     */

  protected $_values = array();

  /**
   * The _fields var can be used by sub class to set/unset/edit the
   * form fields based on their requirement
   *
   */
  function setFields() {
    $activityTypes = CRM_Core_PseudoConstant::ActivityType(FALSE, TRUE);
    asort($activityTypes);

    $this->_fields = array(
      'activity_type_id' => array(
        'type' => 'select',
        'label' => ts('Activity Type'),
        'attributes' => array('' => '- ' . ts('select activity') . ' -') + $activityTypes
      ),
      'subject' => array(
        'type' => 'text',
        'label' => ts('Subject'),
        'attributes' => CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity', 'subject'),
      ),
      'duration' => array(
        'type' => 'text',
        'label' => ts('Duration'),
        'attributes' => array('size' => 4, 'maxlength' => 8),
        'required' => FALSE,
      ),
      'location' => array(
        'type' => 'text',
        'label' => ts('Location'),
        'attributes' => CRM_Core_DAO::getAttribute('CRM_Activity_DAO_Activity', 'location'),
        'required' => FALSE
      ),
      'details' => array(
        'type' => 'wysiwyg',
        'label' => ts('Details'),
        // forces a smaller edit window
        'attributes' => array('rows' => 4, 'cols' => 60),
        'required' => FALSE
      ),
      'status_id' => array(
        'type' => 'select',
        'label' => ts('Status'),
        'attributes' => CRM_Core_PseudoConstant::activityStatus(),
        'required' => TRUE
      ),
      'priority_id' => array(
        'type' => 'select',
        'label' => ts('Priority'),
        'attributes' => CRM_Core_PseudoConstant::get('CRM_Activity_DAO_Activity', 'priority_id'),
        'required' => TRUE
      ),
      'source_contact_id' => array(
        'type' => 'text',
        'label' => ts('Added By'),
        'required' => FALSE
      ),
      'source_contact_qid' => array(
        'type' => 'hidden',
        'label' => '',
        'attributes' => array('id' => 'source_contact_qid')
      ),
      'assignee_contact_id' => array(
        'type' => 'text',
        'label' => ts('Assignee'),
        'required' => FALSE
      ),
    );
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  function preProcess() {

      $session = CRM_Core_Session::singleton();
      $this->_currentUserId = $session->get('userID');

      parent::preProcess();

      $this->setFields();
  }

  /**
   * This function sets the default values for the form. For edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    // if it's a new activity, we need to set default values for associated contact fields
    // since those are jQuery fields, unfortunately we cannot use defaults directly
    $this->_sourceContactId = $this->_currentUserId;

    $this->_values['source_contact_id'] = self::_getDisplayNameById($this->_sourceContactId);
    $this->_values['source_contact_qid'] = $this->_sourceContactId;
/*
    if (!CRM_Utils_Array::crmIsEmptyArray($this->_values['assignee_contact'])) {
      $assignee_contact_value = explode(';', trim($this->_values['assignee_contact_value']));
      $assignee_contact = array_combine($this->_values['assignee_contact'], $assignee_contact_value);

      if ($this->_action & CRM_Core_Action::VIEW) {
        $this->assign('assignee_contact', $assignee_contact);
      } else {
        $this->assign('assignee_contact', $this->formatContactValues($assignee_contact));
      }
    }
*/

    list($this->_values['activity_date_time'], $this->_values['activity_date_time_time']) = CRM_Utils_Date::setDateDefaults(NULL, 'activityDateTime');

    if (!CRM_Utils_Array::value('priority_id', $this->_values)) {
      $priority = CRM_Core_PseudoConstant::get('CRM_Activity_DAO_Activity', 'priority_id');
      $this->_values['priority_id'] = array_search('Normal', $priority);
    }
    if (!CRM_Utils_Array::value('status_id', $this->_values)) {
      $this->_values['status_id'] = CRM_Core_OptionGroup::getDefaultValue('activity_status');
    }
    return $this->_values;
  }

  public function buildQuickForm() {
    foreach ($this->_fields as $field => $values) {
      if (CRM_Utils_Array::value($field, $this->_fields)) {
        $attribute = NULL;
        if (CRM_Utils_Array::value('attributes', $values)) {
          $attribute = $values['attributes'];
        }

        $required = FALSE;
        if (CRM_Utils_Array::value('required', $values)) {
          $required = TRUE;
        }
        if ($values['type'] == 'wysiwyg') {
          $this->addWysiwyg($field, $values['label'], $attribute, $required);
        } else {
          $this->add($values['type'], $field, $values['label'], $attribute, $required);
        }
      }
    }

    $encounterMediums = CRM_Case_PseudoConstant::encounterMedium();
    $this->add('select', 'medium_id', ts('Medium'), $encounterMediums, TRUE);

    $this->addRule('duration',
      ts('Please enter the duration as number of minutes (integers only).'), 'positiveInteger'
    );

    $this->addDateTime('activity_date_time', ts('Date'), TRUE, array('formatType' => 'activityDateTime'));

    //autocomplete url
    $dataUrl = CRM_Utils_System::url("civicrm/ajax/rest", "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=activity&reset=1", FALSE, NULL, FALSE);
    $this->assign('dataUrl', $dataUrl);

    //tokeninput url
    $tokenUrl = CRM_Utils_System::url("civicrm/ajax/checkemail", "noemail=1", FALSE, NULL, FALSE);
    $this->assign('tokenUrl', $tokenUrl);

    $tags = CRM_Core_BAO_Tag::getTags('civicrm_activity');

    if (!empty($tags)) {
      $this->add('select', 'tag', ts('Tags'), $tags, FALSE,
        array('id' => 'tags', 'multiple' => 'multiple', 'title' => ts('- select -'))
      );
    }

    $message = array(
      'completed' => ts('Are you sure? This is a COMPLETED activity with the DATE in the FUTURE. Click Cancel to change the date / status. Otherwise, click OK to save.'),
      'scheduled' => ts('Are you sure? This is a SCHEDULED activity with the DATE in the PAST. Click Cancel to change the date / status. Otherwise, click OK to save.'),
    );

    $js = array('onclick' => "return activityStatus(" . json_encode($message) . ");");
    $this->addButtons(array(
          array(
            'type' => 'upload',
            'name' => ts('Save'),
            'js' => $js,
            'isDefault' => TRUE
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel')
          )
        )
      );

    $this->addFormRule(array('CRM_Activity_Form_Activity', 'formRule'), $this);

    if (CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,'activity_assignee_notification')) {
      $this->assign('activityAssigneeNotification', TRUE);
    } else {
      $this->assign('activityAssigneeNotification', FALSE);
    }
  }

  /**
   * global form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $self) {
    $errors = array();
    if (!$fields['activity_type_id']) {
      $errors['activity_type_id'] = ts('Activity Type is a required field');
    }

    //Activity type is mandatory if creating new activity, CRM-4515
    if (array_key_exists('activity_type_id', $fields) && !CRM_Utils_Array::value('activity_type_id', $fields)) {
      $errors['activity_type_id'] = ts('Activity Type is required field.');
    }
    //FIX me temp. comment
    // make sure if associated contacts exist

    if ($fields['source_contact_id'] && !is_numeric($fields['source_contact_qid'])) {
      $errors['source_contact_id'] = ts('Source Contact non-existent!');
    }

    if (CRM_Utils_Array::value('activity_type_id', $fields) == 3 && CRM_Utils_Array::value('status_id', $fields) == 1) {
      $errors['status_id'] = ts('You cannot record scheduled email activity.');
    } elseif (CRM_Utils_Array::value('activity_type_id', $fields) == 4 && CRM_Utils_Array::value('status_id', $fields) == 1) {
      $errors['status_id'] = ts('You cannot record scheduled SMS activity.');
    }

    return $errors;
  }

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess($params = NULL) {
    // store the submitted values in an array
    if (!$params) {
      $params = $this->controller->exportValues($this->_name);
    }

    $params['activity_date_time'] = CRM_Utils_Date::processDate($params['activity_date_time'], $params['activity_date_time_time']);

    // get ids for associated contacts
    if (!$params['source_contact_id']) {
      $params['source_contact_id'] = $this->_currentUserId;
    } else {
      $params['source_contact_id'] = $this->_submitValues['source_contact_qid'];
    }

    // assigning formated value to related variable
    if (CRM_Utils_Array::value('assignee_contact_id', $params)) {
      $params['assignee_contact_id'] = explode(',', $params['assignee_contact_id']);
    }
    else {
      $params['assignee_contact_id'] = array();
    }

    $activities = array();
    $mailSent = false;

    foreach ($this->_caseIds as $caseId) {
      $params['target_contact_id'] = CRM_Case_BAO_Case::retrieveContactIdsByCaseId($caseId);

      $params['case_id'] = $caseId;
      $activity = $this->processActivity($params);

      $dumbLanguage = array(
        'activity_id' => $activity->id,
        'case_id' => $caseId);

      CRM_Case_BAO_Case::processCaseActivity($dumbLanguage);

      $activities[] = $activity;

      $mailSent = $this->mailActivity($params, $activity) || $mailSent; // How very strange
    }

    CRM_Core_Session::setStatus(ts('%1 Activity(s) "%2" have been saved. %3',
      array(
        1 => count($this->_caseIds),
        2 => $params['subject'],
        3 => $mailSent ? ts("A copy of the activity has also been sent to assignee contacts(s).") : ''
      )
    ), ts('Saved'), 'success');

    return array('activity' => $activities);
  }

  /**
   * Process activity creation
   *
   * @param array $params associated array of submitted values
   * @access protected
   */
  protected function processActivity($params) {
    // call begin post process. Idea is to let injecting file do
    // any processing before the activity is added/updated.
    $this->beginPostProcess($params);

    $activity = CRM_Activity_BAO_Activity::create($params);

    // add tags if exists
    $tagParams = array();
    if (!empty($params['tag'])) {
      foreach ($params['tag'] as $tag) {
        $tagParams[$tag] = 1;
      }
    }

    //save static tags
    CRM_Core_BAO_EntityTag::create($tagParams, 'civicrm_activity', $activity->id);

    //save free tags
    if (isset($params['activity_taglist']) && !empty($params['activity_taglist'])) {
      CRM_Core_Form_Tag::postProcess($params['activity_taglist'], $activity->id, 'civicrm_activity', $this);
    }

    // call end post process. Idea is to let injecting file do any
    // processing needed, after the activity has been added/updated.
    $this->endPostProcess($params, $activity);

    return $activity;
  }

  /**
   * Mail Activity Contacts
   *
   * @param array $params associated array of submitted values
   * @access protected
   */
  protected function mailActivity($params, $activity) {
    $activityAssigned = array();
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);

    if (!CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id'])) {
      $activityAssigned = array_flip($params['assignee_contact_id']);
    }

    if (!CRM_Utils_Array::crmIsEmptyArray($params['assignee_contact_id']) &&
      CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'activity_assignee_notification')) {
      $mailToContacts = array();

      $assigneeContacts = CRM_Activity_BAO_ActivityAssignment::getAssigneeNames($activity->id, TRUE, FALSE);

      //build an associative array with unique email addresses.
      foreach ($activityAssigned as $id => $dnc) {
        if (isset($id) && array_key_exists($id, $assigneeContacts)) {
          $mailToContacts[$assigneeContacts[$id]['email']] = $assigneeContacts[$id];
        }
      }

      if (!CRM_Utils_array::crmIsEmptyArray($mailToContacts)) {
        //include attachments while sending a copy of activity.
        $attachments = CRM_Core_BAO_File::getEntityFile('civicrm_activity', $activity->id);

        $ics = new CRM_Activity_BAO_ICalendar($activity);
        $ics->addAttachment($attachments, $mailToContacts);

        $this->_caseType = CRM_Case_BAO_Case::getCaseType($params['case_id'], 'name');
        $this->assign('caseType', $this->_caseType);

        // CRM-8400 add param with _currentlyViewedContactId for URL link in mail
        CRM_Case_BAO_Case::sendActivityCopy(reset($params['target_contact_id']), $activity->id, $mailToContacts, $attachments, $params['case_id']);

        $ics->cleanup();

        return true;
      }
    }

    return false;
  }

  /**
   * Shorthand for getting id by display name (makes code more readable)
   *
   * @access protected
   */
  protected function _getIdByDisplayName($displayName) {
    return CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $displayName,
      'id',
      'sort_name'
    );
  }

  /**
   * Shorthand for getting display name by id (makes code more readable)
   *
   * @access protected
   */
  protected function _getDisplayNameById($id) {
    return CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
      $id,
      'sort_name',
      'id'
    );
  }

  /**
   * Function to let injecting activity type file do any processing
   * needed, before the activity is added/updated
   *
   */
  function beginPostProcess(&$params) {
    if ($this->_activityTypeFile) {
      $className = "CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}";
      $className::beginPostProcess($this, $params);
    }
  }

  /**
   * Function to let injecting activity type file do any processing
   * needed, after the activity has been added/updated
   *
   */
  function endPostProcess(&$params, &$activity) {
    if ($this->_activityTypeFile) {
      $className = "CRM_{$this->_crmDir}_Form_Activity_{$this->_activityTypeFile}";
      $className::endPostProcess($this, $params, $activity );
    }
  }
}

