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
    $result = NULL;
    switch ($value) {
      case 2843:
        $result = 'site_feedback';
        break;
      case 4683:
        $result = 'proni-submit-an-enquiry';
        break;
      case 4810:
      case 12696:
        $result = 'contact_the_make_the_call_team';
        break;
      case 7285:
        $result = 'debt_management_enquiry';
        break;
      case 9720:
        $result = 'taxi_driver_theory_practice_1';
        break;
      case 9721:
        $result = 'taxi_driver_theory_practice_2';
        break;
      case 9722:
        $result = 'taxi_driver_theory_practice_3';
        break;
      case 9723:
        $result = 'taxi_driver_theory_practice_4';
        break;
      case 9898:
        $result = 'state_pension_online_feedback';
        break;
      case 10030:
        $result = 'ready_for_universal_credit';
        break;
      case 12537:
        $result = 'your_comments';
        break;
    }
    return $result;
  }

}
