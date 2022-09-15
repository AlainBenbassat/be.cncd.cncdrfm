<?php

class CRM_Cncdrfm_RfmSummary {

  public function getNumberOfContactsWithCode($referenceYear, $code) {
    $whereCode = $this->codeToWhere($code);

    $sql = "
      select
        count(c.id)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      $whereCode
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getNumberOfContactsNew($referenceYear) {
    $code = '000';
    $whereCode = $this->codeToWhere($code);
    $pastDonations = "
      select
        *
      from
        civicrm_contribution contrib
      where
        contrib.contact_id = c.id
      and
        year(contrib.receive_date) < $referenceYear
      and " . CRM_Cncdrfm_RfmContact::getAllContribWhere() . "
    ";
    
    $sql = "
      select
        count(c.id)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      and
        not exists ($pastDonations)
      $whereCode
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getNumberOfActiveContacts($referenceYear) {
    $sql = "
      select
        count(c.id)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getNumberOfActiveContactsWithCode($referenceYear, $code) {
    $whereCode = $this->codeToWhere($code);

    $sql = "
      select
        count(c.id)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      $whereCode
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getSumOfFrequencyWithCode($referenceYear, $code) {
    $whereCode = $this->codeToWhere($code);

    $sql = "
      select
        ifnull(sum(rfm.frequency), 0)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      $whereCode
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getSumOfMonetaryValueWithCode($referenceYear, $code) {
    $whereCode = $this->codeToWhere($code);

    $sql = "
      select
        ifnull(sum(rfm.monetary_value), 0)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      $whereCode
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  private function codeToWhere($code) {
    if ($code == 'total') {
      return " and rfm.recency <> '000'";
    }
    else {
      return  " and rfm.recency = '$code'";
    }
  }

}
