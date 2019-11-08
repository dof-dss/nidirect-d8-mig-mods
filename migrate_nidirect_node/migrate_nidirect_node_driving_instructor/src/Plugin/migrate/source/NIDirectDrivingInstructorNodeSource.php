<?php

namespace Drupal\migrate_nidirect_node_driving_instructor\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;

/**
 * Prepares Contact nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "driving_instructor_node_source",
 * )
 */
class NIDirectDrivingInstructorNodeSource extends Node
{

  public function prepareRow(Row $row)
  {

    return parent::prepareRow($row);
  }

}
