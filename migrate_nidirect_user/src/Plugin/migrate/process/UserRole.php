<?php

namespace Drupal\migrate_nidirect_user\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'User Role' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "user_role"
 * )
 */
class UserRole extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // This is a text field in D8, decided to hard code the migration after
    // adding roles to config in the D8 NIDirect site.
    switch($value) {
      case 3:
        $role = 'admin_user';
        break;
      case 6:
        $role = 'editor_user';
        break;
      case 8:
        $role = 'apps_user';
        break;
      case 2:
        $role = 'authenticated';
        break;
      case 13:
        $role = 'health_condition_supervisor_user';
        break;
      case 14:
        $role = 'driving_instructor_supervisor_user';
        break;
      case 1:
        $role = 'anonymous';
        break;
      case 4:
        $role = 'author_user';
        break;
      case 10:
        $role = 'gp_supervisor_user';
        break;
      case 9:
        $role = 'gp_author_user';
        break;
      case 12:
        $role = 'health_condition_author_user';
        break;
      case 11:
        $role = 'news_supervisor';
        break;
      case 7:
        $role = 'supervisor_user';
        break;
    }
    return $role;
  }

}
