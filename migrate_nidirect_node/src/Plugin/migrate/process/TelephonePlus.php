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
      return $value;
    }

    // See https://digitaldevelopment.atlassian.net/browse/D8NID-326 for details.
    // Number only regex (D8NID-326 : Case 1).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)(\h+)?$/m', $value['value'], $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'title' => '',
        'number' => $matches[0][0],
        'extension' => '',
        'supplementary' => '',
      ];
      return $value;
    }

    // Number and title regex (D8NID-326 : Case 2).
    preg_match_all('/^(\h+)?([a-zA-Z\-\'\h:,]+[a-zA-Z])\h?\:?(\h\-)?\h(\+?[0-9\h\(\)]{8,16}\d\d\d)(\h+)?$/m', $value['value'], $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      if (count($matches) == 4) {
        $telephone[] =[
          'title' => $matches[0][3],
          'number' => $matches[0][5],
          'extension' => '',
          'supplementary' => '',
        ];
      } else {
        $telephone[] =[
          'title' => $matches[0][2],
          'number' => $matches[0][4],
          'extension' => '',
          'supplementary' => '',
        ];
      }
      return $value;
    }

    // Number and supplementary regex (D8NID-326 : Case 3).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h+(\(?\w+[a-zA-Z0-9\-\'\h:;,\.\)]+[a-zA-Z]+\)?)\.?(\h+)?$/m', $value['value'], $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] =[
        'title' => '',
        'number' => $matches[0][2],
        'extension' => '',
        'supplementary' => $matches[0][3],
      ];
      return $value;
    }

    // Number and extension regex (D8NID-326 : Case 4).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h+([eE]xt\.?(ension)?\.?\:?\h?)([0-9]{4,6})\h?$/m', $value['value'], $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] =[
        'title' => '',
        'number' => $matches[0][2],
        'extension' => $matches[0][5],
        'supplementary' => '',
      ];
      return $value;
    }

    // Number, title and supplementary regex (D8NID-326 : Case 5).
    preg_match_all('/^(\h+)?([a-zA-Z\-\'\h]+[a-zA-Z\)])\h?(:|-)?\h?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h?(\([a-zA-Z0-9\-\'\h:\.,]+\))\.?(\h+)?$/m', $value['value'], $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] =[
        'title' => $matches[0][2],
        'number' => $matches[0][4],
        'extension' => '',
        'supplementary' => $matches[0][5],
      ];
      return $value;
    }

    return $value;
  }
}
