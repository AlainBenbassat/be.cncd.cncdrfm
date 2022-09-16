<?php

class CRM_Cncdrfm_RfmContact {
  public const NUM_YEARS = 7;

  public static function getContribWhere() {
    $donPourUneCampagne = 3;
    $donPonctuel = 15;

    return "contrib.financial_type_id in ($donPourUneCampagne, $donPonctuel) and contrib.contribution_status_id = 1";
  }

  public static function getAllContribWhere() {
    $don = 1;
    $donNonDeductible = 19;
    $donPourUneCampagne = 3;
    $donPonctuel = 15;
    $donRecurrent = 16;

    return "contrib.financial_type_id in ($don, $donNonDeductible, $donPourUneCampagne, $donPonctuel, $donRecurrent) and contrib.contribution_status_id = 1";
  }

  public static function getYears() {
    $years = [];

    $y = date('Y');
    for ($i = 0; $i < self::NUM_YEARS; $i++) {
      $years[$y - $i] = $y - $i;
    }

    return $years;
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

  public static function calculateRFM($ctx, $id, $year) {
    if (self::hasRfmForYear($id, $year)) {
      [$r, $f, $m] = self::updateRFM($id, $year);
    }
    else {
      [$r, $f, $m] = self::insertRFM($id, $year);
    }

    if ($f == 12 && self::convertPseudoRecurringDonations($id, $year)) {
      self::updateRFM($id, $year);
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
    $customField_new_donor = 'custom_' . $config->getCustomFieldIsNewDonor()['id'];

    $r = self::calcRecency($id, $year);
    $f = self::calcFrequency($id, $year);
    $m = self::calcMonetaryValue($id, $year);
    $avgM = self::calcAverageMonetaryValue($id, $year);
    $isNewDonor = self::calcIsNewDonor($id, $year);

    $params = [
      'id' => $id,
      $customField_year => $year,
      $customField_recency => $r,
      $customField_frequency => $f,
      $customField_monetaryValue => $m,
      $customField_average => $avgM,
    ];
    civicrm_api3('contact', 'create', $params);

    return [$r, $f, $m];
  }

  public static function updateRFM($id, $year) {
    $r = self::calcRecency($id, $year);
    $f = self::calcFrequency($id, $year);
    $m = self::calcMonetaryValue($id, $year);
    $avgM = self::calcAverageMonetaryValue($id, $year);

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
      1 => [$r, 'String'],
      2 => [$f, 'Integer'],
      3 => [$m, 'Money'],
      4 => [$avgM, 'Money'],
    ];

    CRM_Core_DAO::executeQuery($sql, $sqlParams);

    return [$r, $f, $m];
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

    return $rfm;
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

  public static function calcIsNewDonor($id, $year) {
    $sql = "
      select
        count(*)
      from
        civicrm_contribution contrib
      where
        contrib.contact_id = $id
      and
        year(contrib.receive_date) < $year
      and " . self::getAllContribWhere();
    $n = CRM_Core_DAO::singleValueQuery($sql);

    return $n == 0 ? 1 : 0;
  }

  public static function getRfmForContactAndYear($id, $year) {
    $sql = "select * from civicrm_value_cncd_rfm where entity_id = $id and reference_year = $year";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();
    return $dao;
  }

  public static function getTwelveDonationsWithSameAmount($id, $year) {
    $sql = "
      SELECT
        contrib.total_amount,
        group_concat(month(contrib.receive_date)) receive_month,
        group_concat(day(contrib.receive_date)) receive_day,
        group_concat(contrib.id) contrib_ids,
        count(contrib.id) num_contribs
      from
          civicrm_contribution contrib
      where
        " . self::getContribWhere() . "
      and
        contrib.contact_id = $id
      and
        year(contrib.receive_date) = $year
      group BY
        contrib.total_amount
      having
        count(contrib.id) = 12
    ";

    return CRM_Core_DAO::executeQuery($sql);
  }

  public static function convertPseudoRecurringDonations($id, $year) {
    $hasConvertedDonations = FALSE;

    $dao = self::getTwelveDonationsWithSameAmount($id, $year);
    while ($dao->fetch()) {
      if (self::hasDonationsEveryMonth($dao->receive_month) && self::hasDonationsOnSameDay($dao->receive_day)) {
        self::convertContributionsToRecurring($dao->contrib_ids);
        $hasConvertedDonations = TRUE;
      }
    }

    return $hasConvertedDonations;
  }

  public static function hasDonationsEveryMonth($months) {
    $monthArray = explode(',', $months);
    if (count($monthArray) != 12) {
      return FALSE;
    }

    sort($monthArray);
    for ($i = 0; $i < 12; $i++) {
      if ($monthArray[$i] != $i + 1) {
        return FALSE;
      }
    }

    return TRUE;
  }

  public static function hasDonationsOnSameDay($days) {
    $dayArray = explode(',', $days);
    $tolerance = 5;

    if (max($dayArray) - min($dayArray) > $tolerance) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  public static function convertContributionsToRecurring($contribIds) {
    $sql = "
      update
        civicrm_contribution
      set
        financial_type_id = 16,
        source = 'Don NRG converti en don récurrent'
      where
        id in ($contribIds)
    ";
    CRM_Core_DAO::executeQuery($sql);
  }
}
