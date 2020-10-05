<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'StripHTMLSummaryFilter' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "strip_html_summary_filter"
 * )
 */
class StripHTMLSummaryFilter extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Reason for writing this filter was that when migrating body fields
    // from D7 it was necessary to strip HTML from the summary part of the
    // body field but not from the main body text.
    $value['summary'] = html_entity_decode($value['summary'], ENT_QUOTES);

    // Remove any HTML tags.
    $value['summary'] = strip_tags($value['summary']);

    return $value;
  }

}
