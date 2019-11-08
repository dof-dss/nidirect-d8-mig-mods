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
   * Database connection to default (Drupal 8) db.
   *
   * @var object
   */
  protected $connDefault;

  /**
   * Database connection to migrate db.
   *
   * @var object
   */
  protected $connMigrate;

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
    $this->connMigrate = Database::getConnection('default', 'migrate');
    $this->connDefault = Database::getConnection('default', 'default');
  }

  /**
   * A simple migrate database query wrapper.
   *
   * @param string $query
   *   SQL query to execute.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   Prepared database statement.
   */
  private function drupal7DatabaseQuery($query) {
    $conn_query = $this->connMigrate->query($query);
    return $conn_query->execute();
  }

  /**
   * Remove shortcuts from the default shortcut set.
   *
   * Required to prevent errors during configuration import.
   */
  // phpcs:disable
  public function task_remove_default_shortcuts() {
    // phpcs:enable
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
   * Update the current site UUID.
   *
   * To use the config/sync site we need to update the active UUID
   * to match that of the import configuration UUID.
   */
  // phpcs:disable
  protected function task_update_site_uuid() {
    // phpcs:enable
    global $config_directories;
    $site_config = Yaml::parse(file_get_contents($config_directories['sync'] . '/system.site.yml'));

    // Config imports will fail if the exported Site UUID doesn't
    // match the current site UUID.
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
   * Update to lowercase traffic light rating values.
   *
   * Update to match the option keys on the 8.x widget.
   */
  // phpcs:disable
  protected function task_update_traffic_light_rating_values() {
  // phpcs:enable
    foreach (['fat_content', 'salt', 'sugar', 'saturates'] as $field_id) {
      $this->drupal7DatabaseQuery("UPDATE field_data_field_recipe_${field_id} SET field_recipe_${field_id}_status = LCASE(field_recipe_${field_id}_status)");
      $this->drupal7DatabaseQuery("UPDATE field_revision_field_recipe_${field_id} SET field_recipe_${field_id}_status = LCASE(field_recipe_${field_id}_status)");
    }
  }

  /**
   * Fix Column 'title' cannot be null issues.
   */
  // phpcs:disable
  protected function task_null_titles() {
  // phpcs:enable
    // Fix nodes.
    $this->drupal7DatabaseQuery("UPDATE node SET node.title = '<none>' WHERE title = '' or title IS NULL");
    // Fix node revisions.
    $this->drupal7DatabaseQuery("UPDATE node_revision SET node_revision.title = '<none>' WHERE title = '' or title IS NULL");
  }

  /**
   * Fix issue with zero status redirect imports to Drupal 8.
   *
   * Credit to Jaime Contreras.
   */
  // phpcs:disable
  protected function task_null_redirect_zero_state() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("UPDATE redirect SET status_code=301 WHERE status_code=0 OR status_code IS NULL");
  }

  /**
   * Import Drupal 7 URL aliases.
   */
  // phpcs:disable
  protected function task_import_url_aliases() {
  // phpcs:enable
    $aliases_query = $this->connMigrate->query('SELECT pid, source, alias FROM url_alias');
    $aliases = $aliases_query->fetchAllAssoc('pid');

    $insert = $this->connDefault->insert('url_alias')->fields([
      'pid',
      'source',
      'alias',
      'langcode',
    ]);

    foreach ($aliases as $pid => $alias) {
      $insert->values([$pid, '/' . $alias->source, '/' . $alias->alias, 'und']);
    }

    $insert->execute();
  }
  
}
