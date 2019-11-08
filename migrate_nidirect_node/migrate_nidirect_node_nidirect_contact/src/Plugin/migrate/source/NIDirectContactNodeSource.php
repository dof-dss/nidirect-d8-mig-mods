<?php

namespace Drupal\migrate_nidirect_node_nidirect_contact\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;
use Drupal\migrate_nidirect_utils\TelephonePlusUtils;

/**
 * Prepares Contact nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "nidirect_contact_node_source",
 * )
 */
class NIDirectContactNodeSource extends Node {

  public function prepareRow(Row $row) {
    $ids = $row->getSourceIdValues();
    $nid = $ids['nid'];

    // Check if we have a telephone lookup table entry for the node.
    $value = TelephonePlusUtils::lookup($nid);

    // If we don't have a lookup fetch the value for parsing.
    if (empty($value)) {
      $query = $this->getDatabase()->query('
        SELECT field_contact_phone_value 
        FROM {field_data_field_contact_phone} 
        WHERE entity_id = :nid', [
          ':nid' => $nid
        ]
      );

      $contact_details = $query->fetchCol(0);
      $contact_details = $contact_details[0];

      $value = TelephonePlusUtils::parse($contact_details);
    }

    $row->setSourceProperty('telephone_number', $value);
    return parent::prepareRow($row);
  }

}
