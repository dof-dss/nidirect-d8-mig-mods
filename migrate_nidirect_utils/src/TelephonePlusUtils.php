<?php

namespace Drupal\migrate_nidirect_utils;

/**
 * Class TelephonePlusUtils.
 *
 * @package Drupal\migrate_nidirect_utils
 */
class TelephonePlusUtils {

  const COUNTRY_CODE = 'GB';
  const DISPLAY_INTERNATIONAL_NUMBER = 0;

  /**
   * Telephone data lookup.
   *
   * Fetches manually adjusted contact data for D7 nodes with
   * troublesome contact data that cannot be parsed using the
   * TelephonePlusUtils::parse() function.
   *
   * @param int $nid
   *   Node ID to lookup.
   *
   * @return array|null
   *   Null or an array of suitable Telephone Plus data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function lookup($nid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_telephone_lookup_nid' => $nid]);

    if (empty($node)) {
      return NULL;
    }

    $node = current($node);
    $telephone = [];
    $telephone_lookup_data = $node->get('field_telephone_lookup_data');

    foreach ($telephone_lookup_data as $telephone_item) {
      $telephone[] = [
        'telephone_title' => $telephone_item->telephone_title ?? '',
        'telephone_number' => $telephone_item->telephone_number ?? '',
        'telephone_extension' => $telephone_item->telephone_extension ?? '',
        'telephone_supplementary' => $telephone_item->telephone_supplementary ?? '',
        'country_code' => $telephone_item->country_code ?? 'GB',
        'display_international_number' => $telephone_item->display_international_number ?? '0',
      ];
    }

    return $telephone;
  }

  /**
   * Parse a string to TelephonePlus field format.
   *
   * Extracts details from a string to return
   * an array of telephone numbers.
   *
   * @param string $input
   *   String of contact information such as title, number, extension etc.
   *
   * @return array
   *   Array of telephone objects for insertion into Telephone Plus field.
   */
  public static function parse($input) {

    // See https://digitaldevelopment.atlassian.net/browse/D8NID-326 for info.
    // Number only regex (D8NID-326 : Case 1).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)(\h+)?$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'telephone_title' => '',
        'telephone_number' => $matches[0][0],
        'telephone_extension' => '',
        'telephone_supplementary' => '',
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];

      return $telephone;
    }

    // Number and title regex (D8NID-326 : Case 2).
    preg_match_all('/^(\h+)?([a-zA-Z\-\'\h:,]+[a-zA-Z])\h?\:?(\h\-)?\h(\+?[0-9\h\(\)]{8,16}\d\d\d)(\h+)?$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      if (count($matches) == 4) {
        $telephone[] = [
          'telephone_title' => $matches[0][3],
          'telephone_number' => $matches[0][5],
          'telephone_extension' => '',
          'telephone_supplementary' => '',
          'country_code' => static::COUNTRY_CODE,
          'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
        ];
      }
      else {
        $telephone[] = [
          'telephone_title' => $matches[0][2],
          'telephone_number' => $matches[0][4],
          'telephone_extension' => '',
          'telephone_supplementary' => '',
          'country_code' => static::COUNTRY_CODE,
          'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
        ];
      }
      return $telephone;
    }

    // Number and supplementary regex (D8NID-326 : Case 3).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h+(\(?\w+[a-zA-Z0-9\-\'\h:;,\.\)]+[a-zA-Z]+\)?)\.?(\h+)?$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'telephone_title' => '',
        'telephone_number' => $matches[0][2],
        'telephone_extension' => '',
        'telephone_supplementary' => $matches[0][3],
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];
      return $telephone;
    }

    // Number and extension regex (D8NID-326 : Case 4).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h+([eE]xt\.?(ension)?\.?\:?\h?)([0-9]{4,6})\h?$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'telephone_title' => '',
        'telephone_number' => $matches[0][2],
        'telephone_extension' => $matches[0][5],
        'telephone_supplementary' => '',
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];
      return $telephone;
    }

    // Number, title and supplementary regex (D8NID-326 : Case 5).
    preg_match_all('/^(\h+)?([a-zA-Z\-\'\h]+[a-zA-Z\)])\h?(:|-)?\h?(\+?[0-9\h\(\)]{8,16}\d\d\d)\h?(\([a-zA-Z0-9\-\'\h:\.,]+\))\.?(\h+)?$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'telephone_title' => $matches[0][2],
        'telephone_number' => $matches[0][4],
        'telephone_extension' => '',
        'telephone_supplementary' => $matches[0][5],
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];
      return $telephone;
    }

    // Multiple numbers regex (D8NID-326 : Case 6).
    preg_match_all('/^(\h+)?(\+?[0-9\h\(\)]*\d\d\d)\h*([eE]xt\.?(ension)?\.?\:?\h*)?([0-9]{4,6})?\h*(\/|or|and)\h+(\+?[0-9\h\(\)]*\d\d\d)\h*([eE]xt\.?(ension)?\.?\:?\h*)?([0-9]{4,6})?\h*$/m', $input, $matches, PREG_SET_ORDER, 0);

    if ($matches) {
      $telephone[] = [
        'telephone_title' => '',
        'telephone_number' => $matches[0][2],
        'telephone_extension' => $matches[0][5] ?? '',
        'telephone_supplementary' => '',
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];

      $telephone[] = [
        'telephone_title' => '',
        'telephone_number' => $matches[0][7],
        'telephone_extension' => $matches[0][10] ?? '',
        'telephone_supplementary' => '',
        'country_code' => static::COUNTRY_CODE,
        'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
      ];
      return $telephone;
    }

    $telephone[] = [
      'telephone_title' => '',
      'telephone_number' => '',
      'telephone_extension' => '',
      'telephone_supplementary' => '',
      'country_code' => static::COUNTRY_CODE,
      'display_international_number' => static::DISPLAY_INTERNATIONAL_NUMBER,
    ];

    return $telephone;
  }


  /**
   * Extract a suitable label based on the telephone number prefix/area code.
   *
   * @param string $input
   *   A telephone number.
   *
   * @return string
   *   A descriptive text label.
   */
  public static function createLabel($input) {
    $input = ltrim($input ['+']);
    $area_code = substr($input, 0, strpos($input, ' '));
  }

}
