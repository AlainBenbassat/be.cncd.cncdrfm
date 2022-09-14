<?php

use CRM_Cncdrfm_ExtensionUtil as E;

class CRM_Cncdrfm_Form_GenerateRfmData extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->setTitle('Mise à jour RFM');

    $this->add('select','year','Mettre à jour tout les contact pour l\'année', CRM_Cncdrfm_RfmContact::getYears(),FALSE);
    $this->addEntityRef('contact_id', 'ou mettre à jour ce contact pour toutes les années', [], FALSE);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ],
    ]);

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();

    if ($this->isCalculationForOnePerson($values)) {
      $this->calculateForContact($values['contact_id']);
    }
    else {
      $this->calculateForYear($values['year']);
    }

    CRM_Core_Session::setStatus('OK', '', 'success');
  }

  private function isCalculationForOnePerson($values) {
    if (!empty($values['contact_id'])) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private function calculateForContact($contactId) {
    $years = CRM_Cncdrfm_RfmContact::getYears();
    foreach ($years as $year) {
      CRM_Cncdrfm_RfmContact::calculateRFM(NULL, $contactId, $year);
    }
  }

  private function calculateForYear($year) {
    $queue = new CRM_Cncdrfm_RfmQueue();
    $queue->storeContacts($year);
    $queue->runViaWeb();
  }

  public function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }

    return $elementNames;
  }

}
