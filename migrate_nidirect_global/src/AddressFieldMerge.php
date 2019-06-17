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
   *   - Address line 3.
   *   - Address line 4.
   *   - Address line 5.
   *   - Town/city.
   *   - Postal code.
   *   - Country code.
   * @return array
   *   Array of keyed data for use with the addressfield process plugin.
   */
  public static function convertToAddressFieldFormat(array $addressFragments) {

    $address_line1 = empty($addressFragments[0]) ? '' : array_pop($addressFragments[0])['value'];
    $address_line2 = empty($addressFragments[1]) ? '' : array_pop($addressFragments[1])['value'];
    $address_line3 = empty($addressFragments[2]) ? '' : array_pop($addressFragments[2])['value'];
    $address_line4 = empty($addressFragments[3]) ? '' : array_pop($addressFragments[3])['value'];
    $address_line5 = empty($addressFragments[4]) ? '' : array_pop($addressFragments[4])['value'];
    $town_city = empty($addressFragments[5]) ? '' : array_pop($addressFragments[5])['value'];
    $postal_code = empty($addressFragments[6]) ? '' : array_pop($addressFragments[6])['value'];
    $country_code = empty($addressFragments[7]) ? 'GB' : array_pop($addressFragments[7])['value'];

    // Flatten exploded/ambiguous address fields:
    // Always merge together address lines 1 and 2
    $flattened_addressline1 = $address_line1;
    if (!empty($address_line2)) {
      $flattened_addressline1 .=  ', ' . $address_line2;
    }

    // and 3 and 4
    $flattened_addressline2 = $address_line3;
    if (!empty($address_line4)) {
      $flattened_addressline2 .=  ', ' . $address_line4;
    }

    // Array labels mimic the D7 addressfield values.
    // See Drupal\address\Plugin\migrate\process\AddressField::transform().
    $address_data = [
      'country' => $country_code,
      'administrative_area' => $address_line5,
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
