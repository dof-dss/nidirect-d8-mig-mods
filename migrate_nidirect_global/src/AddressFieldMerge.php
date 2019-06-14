<?php

namespace Drupal\migrate_nidirect_global;

/**
 * Class AddressFieldMerge.
 *
 *  Gathers arbitary address fragments and assembles into array that AddressField
 * migrate process plugin can understand and work with.
 */
class AddressFieldMerge {

  /**
   * Take an array of address fragments (opinionated) and
   * return a structured addressfield plugin array.
   *
   * @param array $addressFragments
   *   Order matters:
   *   - Address line 1.
   *   - Address line 2.
   *   - Locality.
   *   - Postal code.
   *   - Country code.
   * @return array
   *   Array of keyed data for use with the addressfield process plugin.
   */
  public static function convertToAddressFieldFormat(array $addressFragments) {

    $address_line1 = array_pop($addressFragments[0])['value'];
    $address_line2 = array_pop($addressFragments[1])['value'];
    $locality = array_pop($addressFragments[2])['value'];
    $postal_code = array_pop($addressFragments[3])['value'];
    $country_code = isset($addressFragments[4]) ? array_pop($addressFragments[4])['value'] : 'GB';

    // Array labels mimic the D7 addressfield values.
    // See Drupal\address\Plugin\migrate\process\AddressField::transform().
    $address_data = [
      'country' => $country_code,
      'administrative_area' => '',
      'locality' => $locality,
      'dependent_locality' => '',
      'postal_code' => $postal_code,
      'thoroughfare' => $address_line1,
      'premise' => $address_line2,
      'organisation_name' => '',
    ];

    return $address_data;
  }

}
