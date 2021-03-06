<?php

namespace Drupal\migrate_nidirect_node_driving_instructor\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;

/**
 * Prepares Driving instructor nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "driving_instructor_node_source",
 *   source_module = "node"
 * )
 */
class NIDirectDrivingInstructorNodeSource extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $telephone = [];
    $ids = $row->getSourceIdValues();
    $nid = $ids['nid'];

    // Fetch landline phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_phone_value
      FROM {field_data_field_contact_phone}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $landline = $query->fetchField();

    if (!empty($landline)) {
      $telephone[] = [
        'telephone_title' => 'Landline',
        'telephone_number' => $landline,
        'telephone_extension' => '',
        'telephone_supplementary' => '',
        'country_code' => 'GB',
        'display_international_number' => '0',
      ];
    }

    // Fetch mobile phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_sms_value
      FROM {field_data_field_contact_sms}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $mobile = $query->fetchField();

    if (!empty($mobile)) {
      $telephone[] = [
        'telephone_title' => 'Mobile',
        'telephone_number' => $mobile,
        'telephone_extension' => '',
        'telephone_supplementary' => '',
        'country_code' => 'GB',
        'display_international_number' => '0',
      ];
    }

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
