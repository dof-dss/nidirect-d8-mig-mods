<?php

namespace Drupal\migrate_nidirect_utils;

/**
 * Class ContentExtractors.
 *
 * @package Drupal\migrate_nidirect_utils
 */
class ContentProcessors {

  public static function relatedLinks($content) {

    // Extract everything after the 'More useful links'.
    $links_markup = substr($content[0][0]['value'], strpos($content[0][0]['value'], '<h2>More useful links</h2>' ) + 26);

    // Extract uri and text.
    $link_regex = '/<a href="(\S+?)".*?>\s*([^<]*)<\//m';
    preg_match_all($link_regex, $links_markup, $matches, PREG_SET_ORDER, 0);

    $links = [];

    foreach($matches as $link) {
      $links[] = [
        'uri' => (strpos($link[1], '/') === 0 ? 'internal:' . $link[1] : $link[1]),
        'title' => $link[2],
        'options' => '',
      ];
    }

    return $links;
  }


}
