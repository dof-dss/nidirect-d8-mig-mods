<?php

namespace Drupal\migrate_nidirect_utils\Command;

// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEnd
use Drupal\Core\Database\Database;
use Symfony\Component\Yaml\Yaml;
use Drupal\migrate_nidirect_utils\MigrateCommand;

/**
 * Processes the database prior to migration.
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
   */
  private function drupal7DatabaseQuery($query) {
    $conn_query = $this->connMigrate->query($query);
    return $conn_query->execute();
  }

  /**
   * Removes shortcuts from the default shortcut set.
   *
   * Remove to prevent errors during configuration import.
   */
  // phpcs:disable
  public function task_remove_default_shortcuts() {
  // phpcs:enable
    // Remove the installed default admin shortcuts which trip up config
    // sync import.
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
   * Update to use the config/sync site UUID or we won't
   * be able to import configuration.
   */
  // phpcs:disable
  protected function task_update_site_uuid() {
  // phpcs:enable
    global $config_directories;
    $site_config = Yaml::parse(file_get_contents($config_directories['sync'] . '/system.site.yml'));

    // Config imports will fail if the exported Site UUID doesn't match the
    // current Site UUID.
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
   * Remove file usage data related to recipe images.
   */
  // phpcs:disable
  protected function task_remove_recipe_image_file_usage_data() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_usage FROM field_data_field_recipe_image INNER JOIN file_usage ON field_data_field_recipe_image.field_recipe_image_fid = file_usage.fid");
  }

  /**
   * Remove file metadata data related to recipe images.
   */
  // phpcs:disable
  protected function task_remove_recipe_image_file_metadata() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_metadata FROM field_data_field_recipe_image INNER JOIN file_metadata ON field_data_field_recipe_image.field_recipe_image_fid = file_metadata.fid");
  }

  /**
   * Remove managed file data related to recipe images.
   */
  // phpcs:disable
  protected function task_remove_recipe_image_managed_file_data() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_managed FROM field_data_field_recipe_image INNER JOIN file_managed ON field_data_field_recipe_image.field_recipe_image_fid = file_managed.fid");
  }

  /**
   * Drop recipe image field tables.
   */
  // phpcs:disable
  protected function task_drop_recipe_image_field_tables() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DROP TABLE IF EXISTS field_data_field_recipe_image,field_revision_field_recipe_image");
  }

  /**
   * Remove recipe node and taxonomy path aliases.
   */
  // phpcs:disable
  protected function task_remove_recipe_url_aliases() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'recipes/%' OR url_alias.alias LIKE 'recipe-%'");
  }

  /**
   * Remove recipe node metatags.
   */
  // phpcs:disable
  protected function task_remove_recipe_node_metatags() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe node revisions.
   */
  // phpcs:disable
  protected function task_remove_recipe_node_revisions() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe nodes.
   */
  // phpcs:disable
  protected function task_remove_recipe_nodes() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_recipe'");
  }

  /**
   * Remove umbrella body node and taxonomy path aliases.
   */
  // phpcs:disable
  protected function task_remove_umbrella_body_url_aliases() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'accessni/%' OR url_alias.alias LIKE 'accessni-%'");
  }

  /**
   * Remove umbrella body node metatags.
   */
  // phpcs:disable
  protected function task_remove_umbrella_body_node_metatags() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body node revisions.
   */
  // phpcs:disable
  protected function task_remove_umbrella_body_node_revisions() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body nodes.
   */
  // phpcs:disable
  protected function task_remove_umbrella_body_nodes() {
  // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_ub'");
  }

}
