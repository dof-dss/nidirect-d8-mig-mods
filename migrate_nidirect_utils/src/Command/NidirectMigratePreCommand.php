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
  protected function configure()
  {
    $this->setName('nidirect:migrate:pre')
      ->setDescription("Pre migration setup");
  }

  /**
   * {@inheritdoc}
   */
  public function __construct()
  {
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
  private function drupal7DatabaseQuery($query)
  {
    $conn_query = $this->connMigrate->query($query);
    return $conn_query->execute();
  }

  /**
   * Removes shortcuts from the default shortcut set to prevent errors
   * during configuration import.
   */
  // phpcs:disable
  public function task_remove_default_shortcuts() {
    // phpcs:enable
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
  // phpcs:disable
  protected function task_update_site_uuid()
  {
    // phpcs:enable
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
   * Update to lowercase traffic light rating values to match the option keys on the 8.x widget.
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

  /**
   * Prepare telephone numbers for new multiple value telephone plus field.
   */
  protected function task_prepare_phone_field_data() {
    $phone_numbers = $this->connMigrate->query('SELECT revision_id, field_contact_phone_value FROM field_data_field_contact_phone WHERE bundle = \'nidirect_contact\'');

    $counter = 0;
    $updates = [];

    foreach ($phone_numbers as $phone_number) {
      /* Pattern for:
       *
       * 028 6634 3165 / 028 6634 3144
       * 01234 269110 / 01234 269609
       * 028 3751 8569 or 0751 168 6433
       */
      $regex = '/^((\d+\s){2,3})(or|\/)((\s\d+){2,3})$/m';
      preg_match_all($regex, $phone_number->field_contact_phone_value, $matches, PREG_SET_ORDER, 0);
      if (count($matches) > 0) {
        $value = '[' . trim($matches[0][1]) . '][' . trim($matches[0][4]) . ']';
        $updates[$phone_number->revision_id] = $value;
        $counter++;
        continue;
      }

      /* Pattern for
       *
       * 028 9023 8152 (Monday to Friday 8.45 am to 5.00 pm)
       */
      $regex = '/^((\d+\s){2,3})\(([^\/)]*)\)/m';
      preg_match_all($regex, $phone_number->field_contact_phone_value, $matches, PREG_SET_ORDER, 0);
      if (count($matches) > 0) {
        $value = '[' . trim($matches[0][1]) . '|' . $matches[0][3] . ']';
        $updates[$phone_number->revision_id] = $value;
        $counter++;
        continue;
      }

      /* Pattern for:
       *
       * 020 7089 5050 / Parents Helpline - 0808 802 5544
       * 01476 581111 / Northern Ireland Office - 028 9127 5787
       */
      $regex = '/^((\d+\s){2,3})\/\s(.+)[:-]\s((\d+\s?){2,3})$/m';
      preg_match_all($regex, $phone_number->field_contact_phone_value, $matches, PREG_SET_ORDER, 0);
      if (count($matches) > 0) {
        $value = '[' . trim($matches[0][1]) . '][' . $matches[0][4] . '|' . trim($matches[0][3]) . ']';
        $updates[$phone_number->revision_id] = $value;
        $counter++;
        continue;
      }
    }

    $this->getIo()->info('Processed ' . $counter . ' phone fields.');
  }

}
