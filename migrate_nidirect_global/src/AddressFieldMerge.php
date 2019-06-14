<?php

namespace Drupal\migrate_nidirect_global;

class AddressFieldMerge {

  public static function convertToAddressFieldFormat($addressFragments) {

    $address_line1        = array_pop($addressFragments[0])['value'];
    $address_line2        = array_pop($addressFragments[1])['value'];
    $locality             = array_pop($addressFragments[2])['value'];
    $postal_code          = array_pop($addressFragments[3])['value'];
    $country_code         = isset($addressFragments[4]) ? array_pop($addressFragments[4])['value'] : 'GB';

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
