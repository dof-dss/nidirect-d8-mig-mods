<?php

namespace Drupal\migrate_nidirect_node_driving_instructor\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\NodeRevision;
use Drupal\migrate\Row;

/**
 * Prepares Driving instructor nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "driving_instructor_node_revision_source",
 *   source_module = "migrate_nidirect_node_driving_instructor"
 * )
 */
class NIDirectDrivingInstructorNodeRevisionSource extends NodeRevision {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $telephone = [];
    $ids = $row->getSourceIdValues();

    // Fetch landline phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_phone_value
      FROM {field_revision_field_contact_phone}
      WHERE revision_id = :vid', [
        ':vid' => $vid,
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
      FROM {field_revision_field_contact_sms}
      WHERE revision_id = :vid', [
        ':vid' => $vid,
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
