<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\source;

use Drupal\migrate_nidirect_utils\TelephonePlusUtils;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\NodeRevision;

/**
 * Prepares revisions with phone fields for conversion to telephone plus field.
 *
 * @MigrateSource(
 *   id = "phone_field_revision_node_source",
 *   source_module = "migrate_nidirect_node"
 * )
 */
class NIDirectPhoneFieldRevisionNodeSource extends NodeRevision {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $ids = $row->getSourceIdValues();

    // Fetch phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_phone_value
      FROM {field_revision_field_contact_phone}
      WHERE revision_id = :vid', [
        ':vid' => $ids['vid'],
      ]
    );

    $phone = $query->fetchField();
    $telephone = TelephonePlusUtils::parse($phone);

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
