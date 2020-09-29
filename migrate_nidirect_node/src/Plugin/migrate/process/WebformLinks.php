<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'WebformLinks' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "webform_links"
 * )
 */
class WebformLinks extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Reason for writing this filter was that a Drupal 7 webform link
    // cannot be converted to a Drupal 8 webform link.
    // We need to map by nid.
    $nid = $value;

    return 'taxi_driver_theory_practice_1';
  }

}
