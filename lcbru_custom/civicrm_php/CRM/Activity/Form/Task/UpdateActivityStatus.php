<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class provides the functionality to group
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */
class CRM_Activity_Form_Task_UpdateActivityStatus extends CRM_Activity_Form_Task {

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */
  function buildQuickForm() {
    $activityStatuses = CRM_Core_PseudoConstant::activityStatus();
    asort($activityStatuses);
    $caseStatuses = CRM_Case_PseudoConstant::caseStatus();
    asort($caseStatuses);

    $this->addSelectOther(
      'case_status',
      ts('Case Status'),
      array('' => ts('All statuses')) + $caseStatuses,
      false
      );

    $this->addSelectOther(
      'activity_status',
      ts('Activity status'),
      array('' => ts('No change')) + $activityStatuses,
      false
      );

    CRM_Utils_System::setTitle(ts('Update Activity Status from Case Status'));

    $this->addDefaultButtons(ts('Update Activity Statuses'));
  }

/**
   * @param string $name
   * @param $label
   * @param $options
   * @param $attributes
   * @param null $required
   * @param null $javascriptMethod
   */
  public function addSelectOther($name, $label, $options, $attributes, $required = NULL, $javascriptMethod = NULL) {
    $this->addElement('select', $name . '_id', $label, $options, $javascriptMethod);
    if ($required) {
      $this->addRule($name . '_id', ts('Please select %1', array(1 => $label)), 'required');
    }
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    $mappings = $this->getMappings();

    $activitiesProcessed = 0;

    $params = $this->controller->exportValues($this->_name);

    foreach ($this->_activityHolderIds as $activityIdUnsafe) {
      $activityId = intval($activityIdUnsafe);
      $caseID = CRM_Case_BAO_Case::getCaseIdByActivityId($activityId);

      if (empty($caseID)) {
        continue;
      }

      $case = civicrm_api('Case', 'getsingle', array('version' => '3', 'id' => $caseID));

      if (empty($case)) {
        continue;
      }

      if (!array_key_exists('activities', $case)) {
        continue;
      }

      if (!in_array($activityId, $case['activities'])) {
        continue;
      }

      $currentDate = date('YmdHis');

      foreach ($mappings as $mapping) {
        if ($mapping['case_status_id'] == $case['status_id'] || $mapping['case_status_id'] == '') {
          if (is_numeric($mapping['activity_status_id'])) {
            civicrm_api('activity', 'create', array(
                'version' => 3,
                'id' => $activityId,
                'status_id' => $mapping['activity_status_id'],
                'activity_date_time' => $currentDate
              ));
          }
          $activitiesProcessed++;
          break;
        }
      }
    }

    $statuses[] = ts('%count activity status updated', array('count' => $activitiesProcessed, 'plural' => '%count activity statuses updated'));
    $status = '<ul><li>' . implode('</li><li>', $statuses) . '</li></ul>';
    CRM_Core_Session::setStatus($status, ts('Update Activity Status from Case Status'), 'success', array('expires' => 0));
  }

  private function getMappings() {
    $result = array();

    $this->addMapping('', $result);

    for($i = 1; ; $i++){
      if (!$this->addMapping($i, $result)) {
        break;
      }
    }

    return $result;
  }

  private function addMapping($suffix, &$mappings) {
    $caseStatusIdName = "case_status_id$suffix";
    $activityStatusIdName = "activity_status_id$suffix";

    if (array_key_exists($caseStatusIdName, $_POST) && array_key_exists($activityStatusIdName, $_POST)) {
      $mappings[] = array(
        'case_status_id' => intval($_POST[$caseStatusIdName]),
        'activity_status_id' => intval($_POST[$activityStatusIdName])
        );
      return true;
    } else {
      return false;
    }
  }
}

