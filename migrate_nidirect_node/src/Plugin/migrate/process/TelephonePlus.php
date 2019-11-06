<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Transforms string based phone field to TelephonePlus Plus field.
 *
 * Takes a string value and processes for compatibility with TelephonePlus Plus by:
 * - Preform a lookup against a table of known values that cannot be
 *   automatically imported and use the defined replacement value.
 * - Pass the value through a series of regex statements to return a
 *   suitable value for import.
 *
 * Examples:
 *
 * @MigrateProcessPlugin(
 *  id = "telephone_plus"
 * )
 */
class TelephonePlus extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
    $source_id = $row->getSourceIdValues();
    $nid = $source_id['nid'];

    // Checkup lookup table for problematic phone numbers;
    $db = Database::getConnection('default', 'default');

    $result = $db->query(
      'SELECT id, telephone_source, telephone_destination FROM {telephone_migration_lookup} t WHERE t.id = :id',
      [':id' => $nid]);

    if (is_array($data = $result->fetchAssoc())) {
      $multiple_numbers = explode('~', $data['telephone_destination']);
      foreach ($multiple_numbers as $number) {
        $number_parts = explode('|', $number);
        $telephone[] =[
          'title' => $number_parts[1] ?? '',
          'number' => $number_parts[0] ?? '',
          'supplementary' => $number_parts[2] ?? '',
        ];
      }
      return;
    }

  }

}
