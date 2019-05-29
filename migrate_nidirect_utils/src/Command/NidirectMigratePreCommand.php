<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NidirectMigratePreCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePreCommand extends ContainerAwareCommand {

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
  protected function execute(InputInterface $input, OutputInterface $output) {
      
    // Remove the installed default admin shortcuts which trip up config sync import.
    $default_shortcuts = \Drupal::entityManager()->getStorage("shortcut_set")->load("default");

    if ($default_shortcuts) {
      $this->getIo()->info('Removing existing default shortcuts.');
      $default_shortcuts->delete();
    }

    global $config_directories;
    $site_config = Yaml::parse(file_get_contents($config_directories['sync'] . '/system.site.yml'));

    // Config imports will fail if the exported Site UUID doesn't match the current
    // Site UUID.
    if ($site_config) {
      $site_uuid_sync = $site_config['uuid'];
      $site_uuid_curr = \Drupal::config('system.site')->get('uuid');

      if ($site_uuid_sync != $site_uuid_curr) {
        $this->getIo()->info("Updating Site UUID to config/sync ID.");
        \Drupal::config('system.site')->set('uuid', $site_uuid);
      }
    }
  }
}
