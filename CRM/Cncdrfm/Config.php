<?php

class CRM_Cncdrfm_Config {
  public function checkConfig() {
    $this->getCustomFieldYear();
    $this->getCustomFieldRecency();
    $this->getCustomFieldFrequency();
    $this->getCustomFieldMonetaryValue();
    $this->getCustomFieldAverageMonetaryValue();
    $this->getCustomFieldIsNewDonor();
  }

  public function getCustomGroupRFM() {
    $params = [
      'name' => 'RFM',
      'title' => 'RFM',
      'extends' => 'Contact',
      'style' => 'Tab with table',
      'collapse_display' => '0',
      'is_active' => '1',
      'table_name' => 'civicrm_value_cncd_rfm',
      'is_multiple' => '1',
      'collapse_adv_display' => '0',
      'is_reserved' => '0',
      'is_public' => '1',
    ];
    return $this->createOrGetCustomGroup($params);
  }

  public function getCustomFieldYear() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'reference_year',
      'label' => 'Année',
      'data_type' => 'Int',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '1',
      'weight' => '1',
      'is_active' => '1',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'reference_year',
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomFieldRecency() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'recency',
      'label' => 'NRG',
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '2',
      'is_active' => '1',
      'text_length' => '3',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'recency',
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomFieldFrequency() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'frequency',
      'label' => 'Fréquence',
      'data_type' => 'Int',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '1',
      'weight' => '3',
      'is_active' => '1',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'frequency',
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomFieldAverageMonetaryValue() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'average_monetary_value',
      'label' => 'Valeur moyenne',
      'data_type' => 'Money',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '1',
      'weight' => '4',
      'is_active' => '1',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'average_monetary_value',
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomFieldMonetaryValue() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'monetary_value',
      'label' => 'Total',
      'data_type' => 'Money',
      'html_type' => 'Text',
      'is_searchable' => '1',
      'is_search_range' => '1',
      'weight' => '5',
      'is_active' => '1',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'monetary_value',
    ];
    return $this->createOrGetCustomField($params);
  }

  public function getCustomFieldIsNewDonor() {
    $params = [
      'custom_group_id' => $this->getCustomGroupRFM()['id'],
      'name' => 'new_donor',
      'label' => 'Nouveau donateur ?',
      'data_type' => 'Boolean',
      'html_type' => 'Radio',
      'is_searchable' => '1',
      'is_search_range' => '0',
      'weight' => '5',
      'text_length' => '255',
      'note_columns' => '60',
      'note_rows' => '4',
      'column_name' => 'new_donor',
    ];
    return $this->createOrGetCustomField($params);
  }

  private function createOrGetCustomGroup($params) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $customGroup = civicrm_api3('CustomGroup', 'create', $params);
    }

    return $customGroup;
  }

  private function createOrGetCustomField($params) {
    try {
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'custom_group_id' => $params['custom_group_id'],
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $customField = civicrm_api3('CustomField', 'create', $params);
    }

    return $customField;
  }
}
