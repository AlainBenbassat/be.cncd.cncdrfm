<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_Cncdrfm_Form_Report_AnalyseRFM',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Analyse RFM',
      'description' => 'Analyse RFM',
      'class_name' => 'CRM_Cncdrfm_Form_Report_AnalyseRFM',
      'report_url' => 'be.cncd.cncdrfm/analyserfm',
      'component' => '',
    ],
  ],
];
