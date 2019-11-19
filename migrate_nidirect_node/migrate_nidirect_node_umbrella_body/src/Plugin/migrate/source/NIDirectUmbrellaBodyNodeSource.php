<?php

namespace Drupal\migrate_nidirect_node_umbrella_body\Plugin\migrate\source;

use Drupal\migrate_nidirect_utils\TelephonePlusUtils;
use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;

/**
 * Prepares Umbrella Body nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "umbrella_body_node_source",
 * )
 */
class NIDirectUmbrellaBodyNodeSource extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $ids = $row->getSourceIdValues();
    $nid = $ids['nid'];

    // Fetch phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_phone_value
      FROM {field_data_field_contact_phone}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $phone = $query->fetchField();
    $telephone = TelephonePlusUtils::parse($phone);

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
