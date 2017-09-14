<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Primarycarerecruitmentreport_Form_Report_PrimaryCareRecruitmentReport',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Primary Care Recruitment Report',
      'description' => 'Report Study Enrolments including specified case role(s) (com.lcbru.primarycarerecruitmentreport)',
      'class_name' => 'CRM_Primarycarerecruitmentreport_Form_Report_PrimaryCareRecruitmentReport',
      'report_url' => 'com.lcbru.primarycarerecruitmentreport/primarycarerecruitmentreport',
      'component' => 'CiviCase',
    ),
  ),
);
