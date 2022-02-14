<?php

class CRM_Cncdrfm_RfmQueue {
  private $queue;
  private $queueName = 'rfmcncd';

  public function __construct() {
    $this->queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => $this->queueName,
      'reset' => TRUE,
    ]);
  }

  public function storeContacts($year) {
    // store all the selected participant id's in the queue
    $minYear = date('Y') - CRM_Cncdrfm_RfmContact::NUM_YEARS;
    $dao = CRM_Cncdrfm_RfmContact::getContactsWithDonations($minYear);
    while ($dao->fetch()) {
      $task = new CRM_Queue_Task(['CRM_Cncdrfm_RfmContact', 'calculateRFM'], [$dao->id, $year]);
      $this->queue->createItem($task);
    }
  }

  public function runViaWeb() {
    $runner = new CRM_Queue_Runner([
      'title' => 'Calculate RFM',
      'queue' => $this->queue,
      'onEndUrl'  => CRM_Utils_System::url('civicrm/cncdrfm-generate', 'reset=1'),
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    ]);
    $runner->runAllViaWeb();
  }

  public function runInBackground() {
    $runner = new CRM_Queue_Runner([
      'title' => 'Calculate RFM',
      'queue' => $this->queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    ]);
    $runner->runAll();
  }

}
