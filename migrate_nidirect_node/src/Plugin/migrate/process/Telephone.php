<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Transforms string based phone field to Telephone Plus field.
 *
 * Takes a string value and processes for compatibility with Telephone Plus by:
 * - Preform a lookup against a table of known values that cannot be
 *   automatically imported and use the defined replacement value.
 * - Pass the value through a series of regex statements to return a
 *   suitable value for import.
 *
 * Examples:
 *
 * @MigrateProcessPlugin(
 *  id = "telephone"
 * )
 */
class Telephone extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Plugin logic goes here.
  }

}
