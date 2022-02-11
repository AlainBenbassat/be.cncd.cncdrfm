<?php

class CRM_Cncdrfm_RfmSummary {

  public function getNumberOfContactsWithCode($referenceYear, $code) {
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
        rfm.recency = '$code'
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
        rfm.recency = '$code'
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getSumOfFrequencyWithCode($referenceYear, $code) {
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
      and
        rfm.recency = '$code'
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getAverageOfMonetaryValueWithCode($referenceYear, $code) {
    $sql = "
      select
        ifnull(avg(rfm.monetary_value), 0)
      from
        civicrm_contact c
      inner join
        civicrm_value_cncd_rfm rfm on rfm.entity_id = c.id
      where
        c.is_deleted = 0
      and
        rfm.reference_year = $referenceYear
      and
        rfm.recency = '$code'
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public function getSumOfMonetaryValueWithCode($referenceYear, $code) {
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
      and
        rfm.recency = '$code'
      and
        rfm.frequency > 0
    ";

    return CRM_Core_DAO::singleValueQuery($sql);
  }

}
