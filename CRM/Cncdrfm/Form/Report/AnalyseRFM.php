<?php
use CRM_Cncdreporting_ExtensionUtil as E;

class CRM_Cncdrfm_Form_Report_AnalyseRFM extends CRM_Report_Form {
  public function __construct() {
    $this->_columns = [
      'civicrm_dummy_entity' => [
        'fields' => $this->getReportColumns(),
        'filters' => $this->getReportFilters(),
      ],
    ];

    parent::__construct();
  }

  private function getReportColumns() {
    $cols = [];

    $colTitles = [
      'code' => '',
      'combien' => 'Combien ?',
      'pct_activite' => '% Activité',
      'actifs' => 'Actifs',
      'frequence' => 'Fréquence',
      'valeur_moyenne' => 'Valeur moyenne',
      'total' => 'Total',
    ];

    $i = 1;
    foreach ($colTitles as $k => $colTitle) {
      $cols[$k] = [
        'title' => $colTitle,
        'required' => TRUE,
        'dbAlias' => '1',
      ];

      $i++;
    }

    return $cols;
  }

  private function getReportFilters() {
    $filters = [
      'annee_reference' => [
        'title' => 'Année de référence',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_INT,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => $this->getYears(),
      ],
      'age' => [
        'title' => 'Age',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_INT,
        'operatorType' => CRM_Report_Form::OP_INT,
      ],
      'valeur_moyenne' => [
        'title' => 'Valeur moyenne',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_MONEY,
        'operatorType' => CRM_Report_Form::OP_INT,
      ],
    ];

    return $filters;
  }

  public function preProcess() {
    $this->assign('reportTitle', 'Analyse RFM');
    parent::preProcess();
  }

  public function from() {
    // take small table
    $this->_from = "FROM  civicrm_domain {$this->_aliases['civicrm_contact']} ";
  }

  public function selectClause(&$tableName, $tableKey, &$fieldName, &$field) {
    return parent::selectClause($tableName, $tableKey, $fieldName, $field);
  }

  public function whereClause(&$field, $op, $value, $min, $max) {
    return '';
  }

  public function alterDisplay(&$rows) {
    $rfmCategories = CRM_Cncdrfm_Config::rfmCategories;
    $rfmSummary = new CRM_Cncdrfm_RfmSummary();

    $referenceYear = $this->getSubmittedFilterReferenceYear();

    $rows = [];

    foreach ($rfmCategories as $rfmCode => $rfmLabel) {
      $row['civicrm_dummy_entity_code'] = $rfmLabel;

      $numTotal = $rfmSummary->getNumberOfContactsWithCode($referenceYear, $rfmCode);
      $row['civicrm_dummy_entity_combien'] = $numTotal;
      if ($rfmCode == 'new') {
        $row['civicrm_dummy_entity_actifs'] = '';
        $row['civicrm_dummy_entity_pct_activite'] = '';
        $row['civicrm_dummy_entity_frequence'] = '';
        $row['civicrm_dummy_entity_valeur_moyenne'] = '';
        $row['civicrm_dummy_entity_total'] = '';
      }
      else {
        $numActive = $rfmSummary->getNumberOfActiveContactsWithCode($referenceYear, $rfmCode);
        $row['civicrm_dummy_entity_actifs'] = $numActive;
        $row['civicrm_dummy_entity_pct_activite'] = round($numActive / $numTotal * 100, 0) . ' %';

        $numContributions = $rfmSummary->getSumOfFrequencyWithCode($referenceYear, $rfmCode);
        $row['civicrm_dummy_entity_frequence'] = round($numContributions / $numTotal, 1);

        $avgContribtions = $rfmSummary->getAverageOfMonetaryValueWithCode($referenceYear, $rfmCode);
        $row['civicrm_dummy_entity_valeur_moyenne'] = round($avgContribtions, 2) . ' EUR';

        $row['civicrm_dummy_entity_total'] = $rfmSummary->getSumOfMonetaryValueWithCode($referenceYear, $rfmCode) . ' EUR';
      }

      $rows[] = $row;
    }

    $row['civicrm_dummy_entity_code'] = '<strong>Total</strong>';
    $row['civicrm_dummy_entity_combien'] = '';
    $row['civicrm_dummy_entity_actifs'] = '';
    $row['civicrm_dummy_entity_pct_activite'] = '';
    $row['civicrm_dummy_entity_frequence'] = '';
    $row['civicrm_dummy_entity_valeur_moyenne'] = '';
    $row['civicrm_dummy_entity_total'] = '';
    $rows[] = $row;
  }

  private function getSubmittedFilterReferenceYear() {
    return $this->getSubmitValues()['annee_reference_value'];
  }

  private function getYears() {
    $years = [];

    $y = date('Y');
    for ($i = 0; $i < 5; $i++) {
      $years[$y - $i] = $y - $i;
    }

    return $years;
  }

}
