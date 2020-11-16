<?php

namespace Drupal\migrate_nidirect_utils\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for NIDirect migration from Drupal 7 to 8.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class MigrateCommands extends DrushCommands {

  /**
   * Prepares the site for migrations of Drupal 7 content.
   *
   * @command nidirect-migrate:prepare
   *
   * @aliases mig-prep
   */
  public function prepare()
  {
    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * Displays the status of migrations.
   *
   * @command nidirect-migrate:status
   *
   * @aliases mig-stat
   */
  public function status()
  {
    $this->logger()->success(dt('Achievement unlocked.'));
  }
}
