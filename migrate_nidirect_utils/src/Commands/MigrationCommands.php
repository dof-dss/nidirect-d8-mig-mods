<?php

namespace Drupal\migrate_nidirect_utils\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Database\Database;
use Drush\Commands\DrushCommands;
use Symfony\Component\Yaml\Yaml;

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

    $migrate_tasks = [];
    $class_methods = get_class_methods($this);

    $migrate_tasks = array_filter($class_methods, function ($key) {
      return substr($key, 0, 8) === "prepare_";
    });

    if (($total_tasks = count($migrate_tasks)) > 1) {
      foreach ($migrate_tasks as $task) {
        $this->$task();
      }
    }
    else {
      $this->logger()->notice('No migration preparation commands found');
    }

    $this->logger()->success(dt('Site ready for migrations'));
  }

  /**
   *
   *
   * @command nidirect-migrate:status
   *
   * @aliases mig-stat
   */
  public function status() {
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
   *  --== Migration Prepare tasks ==--
   */

  /**
   * Removes shortcuts from the default shortcut set.
   *
   * Remove to prevent errors during configuration import.
   */
// phpcs:disable
  public function prepare_remove_default_shortcuts()
  {
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
  protected function prepare_update_site_uuid()
  {
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
   * Fix Health Condition revision with broken JSON contents.
   */
// phpcs:disable
  protected function prepare_remove_broken_health_condition_revision()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM node_revision WHERE nid=10748 AND vid=71939");
  }

  /**
   * Fix Column 'title' cannot be null issues.
   */
// phpcs:disable
  protected function prepare_null_titles()
  {
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
  protected function prepare_null_redirect_zero_state()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("UPDATE redirect SET status_code=301 WHERE status_code=0 OR status_code IS NULL");
  }

  /**
   * Remove file usage data related to recipe images.
   */
// phpcs:disable
  protected function prepare_remove_recipe_image_file_usage_data()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_usage FROM field_data_field_recipe_image INNER JOIN file_usage ON field_data_field_recipe_image.field_recipe_image_fid = file_usage.fid");
  }

  /**
   * Remove file metadata data related to recipe images.
   */
// phpcs:disable
  protected function prepare_remove_recipe_image_file_metadata()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_metadata FROM field_data_field_recipe_image INNER JOIN file_metadata ON field_data_field_recipe_image.field_recipe_image_fid = file_metadata.fid");
  }

  /**
   * Remove managed file data related to recipe images.
   */
// phpcs:disable
  protected function prepare_remove_recipe_image_managed_file_data()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE file_managed FROM field_data_field_recipe_image INNER JOIN file_managed ON field_data_field_recipe_image.field_recipe_image_fid = file_managed.fid");
  }

  /**
   * Drop recipe image field tables.
   */
// phpcs:disable
  protected function prepare_drop_recipe_image_field_tables()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DROP TABLE IF EXISTS field_data_field_recipe_image,field_revision_field_recipe_image");
  }

  /**
   * Remove recipe node and taxonomy path aliases.
   */
// phpcs:disable
  protected function prepare_remove_recipe_url_aliases()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'recipes/%' OR url_alias.alias LIKE 'recipe-%'");
  }

  /**
   * Remove recipe node metatags.
   */
// phpcs:disable
  protected function prepare_remove_recipe_node_metatags()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe node revisions.
   */
// phpcs:disable
  protected function prepare_remove_recipe_node_revisions()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe nodes.
   */
// phpcs:disable
  protected function prepare_remove_recipe_nodes()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_recipe'");
  }

  /**
   * Remove umbrella body node and taxonomy path aliases.
   */
// phpcs:disable
  protected function prepare_remove_umbrella_body_url_aliases()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'accessni/%' OR url_alias.alias LIKE 'accessni-%'");
  }

  /**
   * Remove umbrella body node metatags.
   */
// phpcs:disable
  protected function prepare_remove_umbrella_body_node_metatags()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body node revisions.
   */
// phpcs:disable
  protected function prepare_remove_umbrella_body_node_revisions()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body nodes.
   */
// phpcs:disable
  protected function prepare_remove_umbrella_body_nodes()
  {
    // phpcs:enable
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_ub'");
  }

  protected function prepare_update_contact_links() {
    // Update 'Central Appointments Unit' revision URL's to full path.
    $this->drupal7DatabaseQuery("UPDATE field_revision_field_contact_additional_info
    SET field_contact_additional_info_value = REGEXP_REPLACE(field_contact_additional_info_value, 'public-appointment-vacancies-64059.htm', 'https://www.nidirect.gov.uk/articles/public-appointment-vacancies')
    WHERE entity_id = 439");

    $this->drupal7DatabaseQuery("UPDATE field_revision_field_contact_additional_info
    SET field_contact_additional_info_value = REGEXP_REPLACE(field_contact_additional_info_value, 'becoming-a-public-appointee-66246.htm', 'https://www.nidirect.gov.uk/articles/public-appointments-explained#toc-1')
    WHERE entity_id = 439");

    $this->drupal7DatabaseQuery("UPDATE field_revision_field_contact_additional_info
    SET field_contact_additional_info_value = REGEXP_REPLACE(field_contact_additional_info_value, 'public-appointments-explained-20147.htm', 'https://www.nidirect.gov.uk/articles/public-appointments-explained')
    WHERE entity_id = 439");
  }


}
