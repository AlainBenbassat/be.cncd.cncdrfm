<?php

use CRM_Cncdrfm_ExtensionUtil as E;

class CRM_Cncdrfm_Form_GenerateRfmData extends CRM_Core_Form {
  public function buildQuickForm() {
    $this->setTitle('Mise à jour RFM');

    $this->add('select','year','Année', CRM_Cncdrfm_RfmContact::getYears(),TRUE);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    $year = $values['year'];

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
