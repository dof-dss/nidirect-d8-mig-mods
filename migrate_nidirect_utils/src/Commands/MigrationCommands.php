<?php

namespace Drupal\migrate_nidirect_utils\Commands;

use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
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
   * Core EntityTypeManager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct() {
    parent::__construct();
    $this->connMigrate = Database::getConnection('default', 'migrate');
    $this->connDefault = Database::getConnection('default', 'default');
    $this->entityTypeManager = \Drupal::entityTypeManager();
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
      return substr($key, 0, 7) === "prepare";
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
   * Remove select content from the site.
   *
   * @command nidirect-migrate:content-purge
   *
   * @aliases mig-purge
   */
  public function contentPurge() {
    $bundle_index = 1;
    // List of bundles the user is allowed to delete content from.
    $content_types = [
      'application'                     => 'node',
      'article'                         => 'node',
      'contact'                         => 'node',
      'driving_instructor'              => 'node',
      'embargoed_publication'           => 'node',
      'external_link'                   => 'node',
      'gp_practice'                     => 'node',
      'health_condition'                => 'node',
      'health_condition_alternative'    => 'node',
      'link'                            => 'node',
      'news'                            => 'node',
      'page'                            => 'node',
      'publication'                     => 'node',
      'gp'                              => 'gp',
      'drive_instr_categories'          => 'taxonomy_term',
      'hc_body_location'                => 'taxonomy_term',
      'hc_body_system'                  => 'taxonomy_term',
      'hc_condition_type'               => 'taxonomy_term',
      'hc_info_sources'                 => 'taxonomy_term',
      'hc_symptoms'                     => 'taxonomy_term',
      'contact_categories'              => 'taxonomy_term',
      'ni_postcodes'                    => 'taxonomy_term',
      'site_themes'                     => 'taxonomy_term',
    ];

    // Display each bundle and the content count.
    foreach ($content_types as $bundle => $entity) {
      $storage = $this->entityTypeManager->getStorage($entity);

      if ($entity == 'taxonomy_term') {
        $entities = $storage->loadByProperties(["vid" => $bundle]);
      }
      elseif ($entity !== $bundle) {
        $entities = $storage->loadByProperties(["type" => $bundle]);
      }
      else {
        $entities = $storage->loadMultiple();
      }

      $rows[] = [
        'id' => $bundle_index++,
        'entity' => $entity,
        'bundle' => $bundle,
        'total' => count($entities),
      ];
    }
    $this->io()->table(['Index', 'Entity', 'Bundle', 'Total'], $rows);

    $result = $this->io()->ask('What content do want to delete? 0 to exit', NULL, function ($input) use ($bundle_index, $content_types) {
      if (!is_numeric($input)) {
        throw new \RuntimeException('You must type an Index number.');
      }

      if ($input < 0 || $input > $bundle_index) {
        throw new \RuntimeException("Number outside of range of the Index ($bundle_index)");
      }

      return (int) $input;
    });

    if ($result !== 0) {
      // Decrement to get the true array index, bundle and entity type.
      $result--;
      $bundle = array_keys($content_types)[$result];
      $entity = $content_types[$bundle];

      $storage = $this->entityTypeManager->getStorage($entity);

      if ($entity === 'taxonomy_term') {
        $entities = $storage->loadByProperties(["vid" => $bundle]);
      }
      elseif ($entity !== $bundle) {
        $entities = $storage->loadByProperties(["type" => $bundle]);
      }
      else {
        $entities = $storage->loadMultiple();
      }

      if ($this->io()->confirm("Are you sure you want to delete all $bundle content", TRUE)) {
        $storage->delete($entities);
        $this->io()->write("<comment>$bundle content deleted</comment>", TRUE);
      }
      $this->contentPurge();
    }
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
   * Migration Prepare tasks.
   *
   * Tasks prefixed with prepare_ will be automatically called by
   * the prepare() function.
   */

  /**
   * Removes default shortcut set.
   *
   * Remove to prevent errors during configuration import.
   */
  public function prepareRemoveDefaultShortcuts() {
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
  protected function prepareUpdateSiteUuid() {
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
  protected function prepareRemoveBrokenHealthConditionRevision() {
    $this->drupal7DatabaseQuery("DELETE FROM node_revision WHERE nid=10748 AND vid=71939");
  }

  /**
   * Fix Column 'title' cannot be null issues.
   */
  protected function prepareNullTitles() {
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
  protected function prepareNullRedirectZeroState() {
    $this->drupal7DatabaseQuery("UPDATE redirect SET status_code=301 WHERE status_code=0 OR status_code IS NULL");
  }

  /**
   * Remove file usage data related to recipe images.
   */
  protected function prepareRemoveRecipeImageFileUsageData() {
    $this->drupal7DatabaseQuery("DELETE file_usage FROM field_data_field_recipe_image INNER JOIN file_usage ON field_data_field_recipe_image.field_recipe_image_fid = file_usage.fid");
  }

  /**
   * Remove file metadata data related to recipe images.
   */
  protected function prepareRemoveRecipeImageFileMetadata() {
    $this->drupal7DatabaseQuery("DELETE file_metadata FROM field_data_field_recipe_image INNER JOIN file_metadata ON field_data_field_recipe_image.field_recipe_image_fid = file_metadata.fid");
  }

  /**
   * Remove managed file data related to recipe images.
   */
  protected function prepareRemoveRecipeImageManagedFileData() {
    $this->drupal7DatabaseQuery("DELETE file_managed FROM field_data_field_recipe_image INNER JOIN file_managed ON field_data_field_recipe_image.field_recipe_image_fid = file_managed.fid");
  }

  /**
   * Drop recipe image field tables.
   */
  protected function prepareDropRecipeImageFieldTables() {
    $this->drupal7DatabaseQuery("DROP TABLE IF EXISTS field_data_field_recipe_image,field_revision_field_recipe_image");
  }

  /**
   * Remove recipe node and taxonomy path aliases.
   */
  protected function prepareRemoveRecipeUrlAliases() {
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'recipes/%' OR url_alias.alias LIKE 'recipe-%'");
  }

  /**
   * Remove recipe node metatags.
   */
  protected function prepareRemoveRecipeNodeMetatags() {
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe node revisions.
   */
  protected function prepareRemoveRecipeNodeRevisions() {
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_recipe'");
  }

  /**
   * Remove recipe nodes.
   */
  protected function prepareRemoveRecipeNodes() {
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_recipe'");
  }

  /**
   * Remove umbrella body node and taxonomy path aliases.
   */
  protected function prepareRemoveUmbrellaBodyUrlAliases() {
    $this->drupal7DatabaseQuery("DELETE FROM url_alias WHERE url_alias.alias LIKE 'accessni/%' OR url_alias.alias LIKE 'accessni-%'");
  }

  /**
   * Remove umbrella body node metatags.
   */
  protected function prepareRemoveUmbrellaBodyNodeMetatags() {
    $this->drupal7DatabaseQuery("DELETE m FROM metatag m INNER JOIN node n ON n.nid = m.entity_id WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body node revisions.
   */
  protected function prepareRemoveUmbrellaBodyNodeRevisions() {
    $this->drupal7DatabaseQuery("DELETE r FROM node_revision r INNER JOIN node n ON n.nid = r.nid WHERE n.type = 'nidirect_ub'");
  }

  /**
   * Remove umbrella body nodes.
   */
  protected function prepareRemoveUmbrellaBodyNodes() {
    $this->drupal7DatabaseQuery("DELETE FROM node WHERE node.type = 'nidirect_ub'");
  }

  /**
   * Fix issues with contact links.
   */
  protected function prepareUpdateContactLinks() {
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

    // Update 'Womens aid' URL to full path.
    $this->drupal7DatabaseQuery("UPDATE field_data_field_contact_additional_info
    SET field_contact_additional_info_value = REGEXP_REPLACE(field_contact_additional_info_value, 'womens-aid-federation-northern-ireland-18776.htm', 'https://www.nidirect.gov.uk/contacts/contacts-az/womens-aid-federation-northern-ireland-head-office')
    WHERE entity_id = 522");

    // Update 'Womens aid' revision URL to full path.
    $this->drupal7DatabaseQuery("UPDATE field_revision_field_contact_additional_info
    SET field_contact_additional_info_value = REGEXP_REPLACE(field_contact_additional_info_value, 'womens-aid-federation-northern-ireland-18776.htm', 'https://www.nidirect.gov.uk/contacts/contacts-az/womens-aid-federation-northern-ireland-head-office')
    WHERE entity_id = 522");
  }

}
