<?php

namespace Drupal\migrate_nidirect_utils;

/**
 * Class ContentExtractors.
 *
 * @package Drupal\migrate_nidirect_utils
 */
class ContentProcessors {

  /**
   * Extracts link elements from html after the string 'More useful links'.
   *
   * @param array $content
   *   Drupal content field array.
   *
   * @return array
   *   Array of Drupal Link field values..
   */
  public static function relatedLinks(array $content) {
    // Find the character index of the Useful links heading.
    $value = $content[0][0]['value'];
    $links_header_position = strpos($value, 'More useful links');

    if ($links_header_position === FALSE) {
      return [];
    }

    // Extract everything after the 'More useful links'.
    $links_markup = substr($value, $links_header_position);
    // Extract uri and text.
    $link_regex = '/<a href="(\S+?)".*?>\s*([^<]*)<\//m';
    preg_match_all($link_regex, $links_markup, $matches, PREG_SET_ORDER, 0);

    $links = [];

    // Create a Drupal link field entry for each extracted HTML link element.
    foreach ($matches as $link) {
      // Prune away any absolute paths for NIDirect.
      if (preg_match('/^http(.+)nidirect.gov.uk/', $link[1])) {
        $link[1] = parse_url($link[1], PHP_URL_PATH);
      }

      $links[] = [
        'uri' => (strpos($link[1], '/') === 0 ? 'internal:' . $link[1] : $link[1]),
        'title' => strip_tags(html_entity_decode($link[2])),
        'options' => [
          'attributes' => [],
        ],
      ];
    }

    return $links;
  }

}
