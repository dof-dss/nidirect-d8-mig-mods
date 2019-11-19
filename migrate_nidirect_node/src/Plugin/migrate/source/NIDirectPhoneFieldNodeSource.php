<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\source;

use Drupal\migrate_nidirect_utils\TelephonePlusUtils;
use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;

/**
 * Prepares nodes with phone fields for conversion to telephone plus field.
 *
 * @MigrateSource(
 *   id = "phone_field_node_source",
 * )
 */
class NIDirectPhoneFieldNodeSource extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $ids = $row->getSourceIdValues();

    // Fetch phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_phone_value
      FROM {field_data_field_contact_phone}
      WHERE entity_id = :nid', [
        ':nid' => $ids['nid'],
      ]
    );

    $phone = $query->fetchField();
    $telephone = TelephonePlusUtils::parse($phone);

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
