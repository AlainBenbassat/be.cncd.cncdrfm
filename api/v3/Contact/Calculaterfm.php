<?php
use CRM_Cncdrfm_ExtensionUtil as E;

function _civicrm_api3_contact_Calculaterfm_spec(&$spec) {
  $spec['reference_year']['api.required'] = 0;
}

function civicrm_api3_contact_Calculaterfm($params) {
  try {
    $referenceYear = civicrm_api3_contact_Calculaterfm_getYear($params);
    civicrm_api3_contact_Calculaterfm_ValidateYear($referenceYear);

    $queue = new CRM_Cncdrfm_RfmQueue();
    $queue->storeContacts($referenceYear);
    $queue->runInBackground();

    return civicrm_api3_create_success('OK', $params, 'Contact', 'Calculaterfm');
  }
  catch (Exception $e) {
    throw new API_Exception($e->getMessage(), $e->getCode());
  }
}

function civicrm_api3_contact_Calculaterfm_getYear($params) {
  if (array_key_exists('reference_year', $params)) {
    return $params['reference_year'];
  }
  else {
    return date('Y');
  }
}

function civicrm_api3_contact_Calculaterfm_ValidateYear($y) {
  $currentYear = date('Y');
  $minYear = $currentYear - CRM_Cncdrfm_RfmContact::NUM_YEARS;

  if ($y > $currentYear || $y < $minYear) {
    throw new Exception("Year: $y is not between $minYear and $currentYear");
  }
}
