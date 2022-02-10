<?php

use CRM_Cncdrfm_ExtensionUtil as E;

class CRM_Cncdrfm_Form_GenerateRfmData extends CRM_Core_Form {
  private $queue;
  private $queueName = 'rfmcncd';
  private const NUM_YEARS = 6;

  public function __construct($state = NULL, $action = CRM_Core_Action::NONE, $method = 'post', $name = NULL) {
    // create the queue
    $this->queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => $this->queueName,
      'reset' => TRUE,
    ]);

    parent::__construct($state, $action, $method, $name);
  }

  public function buildQuickForm() {
    $this->setTitle('Mise à jour RFM');

    $this->add('select','year','Année', $this->getYears(),TRUE);

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

    // store all the selected participant id's in the queue
    $minYear = date('Y') - self::NUM_YEARS;
    $dao = CRM_Cncdrfm_Helper::getContactsWithDonations($minYear);
    while($dao->fetch()) {
      $task = new CRM_Queue_Task(['CRM_Cncdrfm_Helper', 'calculateRFM'], [$dao->id, $year]);
      $this->queue->createItem($task);
    }

    // run the queue
    $runner = new CRM_Queue_Runner([
      'title' => E::ts('Calculate RFM'),
      'queue' => $this->queue,
      'onEndUrl'  => CRM_Utils_System::url('civicrm/cncdrfm-generate', 'reset=1'),
      'onEnd' => ['CRM_Cncdrfm_Form_GenerateRfmData', 'onEnd'],
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    ]);
    $runner->runAllViaWeb();
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

  private function getYears() {
    $years = [];

    $y = date('Y') - 1;
    for ($i = 0; $i < self::NUM_YEARS; $i++) {
      $years[$y - $i] = $y - $i;
    }

    return $years;
  }

}
