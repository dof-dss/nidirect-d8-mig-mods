<?php

namespace Drupal\migrate_nidirect_utils\Commands;

use Drupal\Core\Database\Database;
use Drush\Commands\DrushCommands;

/**
 * Drush pre migration command for NIDirect.
 */
class MigrationCommands extends DrushCommands {

  /**
   * Database connection to default (Drupal 8) db.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connDefault;

  /**
   * Database connection to migrate db.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connMigrate;

  /**
   * Class constructor.
   *
   */
  public function __construct() {
    parent::__construct();
    $this->connMigrate = Database::getConnection('default', 'migrate');
    $this->connDefault = Database::getConnection('default', 'default');
  }

  /**
   * Prepares the site for migrations of Drupal 7 content.
   *
   * @command nidirect-migrate:prepare
   *
   * @aliases mig-prep
   */
  public function prepare() {
    $this->logger()->success(dt('Site ready for migrations'));
  }

  /**
   * Displays the migration status.
   *
   * @command nidirect-migrate:status
   *
   * @aliases mig-stat
   */
  public function status() {
  }


}
