<?php

class CRM_Cncdrfm_RfmContact {
  public static function getContribWhere() {
    return 'contrib.financial_type_id in (1, 19, 15, 3, 17) and contrib.contribution_status_id = 1';
  }

  public static function getContactsWithDonations($minYear) {
    $sql = "
      select
        c.id
      from
        civicrm_contact c
      where
        c.is_deleted = 0
      and
        exists (
          select * from civicrm_contribution contrib where contrib.contact_id = c.id and year(contrib.receive_date) >= $minYear
            and " . self::getContribWhere() .
    ')';

    return CRM_Core_DAO::executeQuery($sql);
  }

  public static function calculateRFM(CRM_Queue_TaskContext $ctx, $id, $year) {
    if (self::hasRfmForYear($id, $year)) {
      self::updateRFM($id, $year);
    }
    else {
      self::insertRFM($id, $year);
    }

    return TRUE;
  }

  public static function hasRfmForYear($id, $year) {
    $sql = "select id from civicrm_value_cncd_rfm where entity_id = $id and reference_year = $year";
    $dao = CRM_Core_DAO::executeQuery($sql);
    if ($dao->fetch()) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public static function insertRFM($id, $year) {
    $config = new CRM_Cncdrfm_Config();

    $customField_year = 'custom_' . $config->getCustomFieldYear()['id'];
    $customField_recency = 'custom_' . $config->getCustomFieldRecency()['id'];
    $customField_frequency = 'custom_' . $config->getCustomFieldFrequency()['id'];
    $customField_monetaryValue = 'custom_' . $config->getCustomFieldMonetaryValue()['id'];
    $customField_average = 'custom_' . $config->getCustomFieldAverageMonetaryValue()['id'];

    $params = [
      'id' => $id,
      $customField_year => $year,
      $customField_recency => self::calcRecency($id, $year),
      $customField_frequency => self::calcFrequency($id, $year),
      $customField_monetaryValue => self::calcMonetaryValue($id, $year),
      $customField_average => self::calcAverageMonetaryValue($id, $year),
    ];
    civicrm_api3('contact', 'create', $params);
  }

  public static function updateRFM($id, $year) {
    $sql = "
      update
        civicrm_value_cncd_rfm
      set
        recency = %1,
        frequency = %2,
        monetary_value = %3,
        average_monetary_value = %4
      where
        entity_id = $id
      and
        reference_year = $year
    ";

    $sqlParams = [
      1 => [self::calcRecency($id, $year), 'String'],
      2 => [self::calcFrequency($id, $year), 'Integer'],
      3 => [self::calcMonetaryValue($id, $year), 'Money'],
      4 => [self::calcAverageMonetaryValue($id, $year), 'Money'],
    ];

    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }


  public static function calcRecency($id, $year) {
    $rfmYearMinus1 = self::getRfmForContactAndYear($id, $year - 1);
    if (!$rfmYearMinus1) {
      return '';
    }

    $rfmYearMinus2 = self::getRfmForContactAndYear($id, $year - 2);
    if (!$rfmYearMinus2) {
      return '';
    }

    $rfmYearMinus3 = self::getRfmForContactAndYear($id, $year - 3);
    if (!$rfmYearMinus3) {
      return '';
    }

    $rfm = $rfmYearMinus3->frequency >= 1 ? '1' : '0';
    $rfm .= $rfmYearMinus2->frequency >= 1 ? '1' : '0';
    $rfm .= $rfmYearMinus1->frequency >= 1 ? '1' : '0';

    if ($rfm != '000') {
      return $rfm;
    }
    else {
      return '';
    }
  }

  public static function calcFrequency($id, $year) {
    $sql = "
      select
        count(contrib.id)
      from
        civicrm_contribution contrib
      where
        contrib.contact_id = $id
      and
        year(contrib.receive_date) = $year
      and " . self::getContribWhere();
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public static function calcMonetaryValue($id, $year) {
    $sql = "
      select
        ifnull(sum(contrib.total_amount), 0)
      from
        civicrm_contribution contrib
      where
        contrib.contact_id = $id
      and
        year(contrib.receive_date) = $year
      and " . self::getContribWhere();
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public static function calcAverageMonetaryValue($id, $year) {
    $sql = "
      select
        round(ifnull(avg(contrib.total_amount), 0), 2)
      from
        civicrm_contribution contrib
      where
        contrib.contact_id = $id
      and
        year(contrib.receive_date) = $year
      and " . self::getContribWhere();
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  public static function getRfmForContactAndYear($id, $year) {
    $sql = "select * from civicrm_value_cncd_rfm where entity_id = $id and reference_year = $year";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();
    return $dao;
  }
}
