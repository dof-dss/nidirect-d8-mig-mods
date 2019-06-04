<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;
use Symfony\Component\Yaml\Yaml;
use Drupal\migrate_nidirect_utils\MigrateCommand;

/**
 * Class NidirectMigratePreCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePreCommand extends MigrateCommand {

   /**
   * Database connection to migrate db.
   *
   * @var object
   */
  protected $conn_migrate;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:pre')
      ->setDescription("Pre migration setup");
  }

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->conn_migrate = Database::getConnection('default', 'migrate');
  }

  /**
   * A simple migrate database query wrapper.
   *
   *  @param string $query
   *  SQL query to execute.
   */
  private function drupal7DatabaseQuery($query) {
    $conn_query =  $this->conn_migrate->query($query);
    return $conn_query->execute();
  }

  /**
   * Removes shortcuts from the default shortcut set to prevent errors
   * during configuration import.
   */
  public function task_remove_default_shortcuts() {
    // Remove the installed default admin shortcuts which trip up config sync import.
    $query = \Drupal::entityTypeManager()->getStorage('shortcut')->getQuery();
    $nids = $query->condition('shortcut_set', 'default')->execute();
    $shortcuts = \Drupal::entityTypeManager()->getStorage("shortcut")->loadMultiple($nids);

    if ($shortcuts) {
      foreach ($shortcuts as $shortcut) {
        $shortcut->delete();
      }	
    }
  }

  /**
   * Update the current site UUID to use the config/sync site UUID or we won't
   * be able to import configuration.
   */
  protected function task_update_site_uuid() {
    global $config_directories;
    $site_config = Yaml::parse(file_get_contents($config_directories['sync'] . '/system.site.yml'));

    // Config imports will fail if the exported Site UUID doesn't match the current
    // Site UUID.
    if ($site_config) {
      $site_uuid_sync = $site_config['uuid'];

      $config = \Drupal::service('config.factory')->getEditable('system.site');
      $site_uuid_curr = $config->get('uuid');
      
      if ($site_uuid_sync != $site_uuid_curr) {
	      $config->set('uuid', $site_uuid_sync)->save();
      }
    }
  }

  /**
   * Fix Column 'title' cannot be null issues.
   */
  protected function task_null_titles() {
    // Fix nodes.
    $this->drupal7DatabaseQuery("UPDATE node SET node.title = '<none>' WHERE title = '' or title IS NULL");
    // Fix node revisions.
    $this->drupal7DatabaseQuery("UPDATE node_revision SET node_revision.title = '<none>' WHERE title = '' or title IS NULL");
  }

  /**
   * Fix issue with zero status redirect imports to Drupal 8.
   * Credit to Jaime Contreras.
   */
  protected function task_null_redirect_zero_state() {
    $this->drupal7DatabaseQuery("UPDATE redirect SET status_code=301 WHERE status_code=0 OR status_code IS NULL");
  }

}
