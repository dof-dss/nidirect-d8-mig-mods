<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostAliasCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostAliasCommand extends ContainerAwareCommand {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityActionBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:alias')->setDescription(
      $this->trans('commands.nidirect.migrate.post.alias.description')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');
    $this->getIo()->info('Started post migration alias processing.');

    // Retrieve all aliases from D7.
    $query = $conn_migrate->query(
      "select source, alias from {url_alias}");
    $d7_aliases = $query->fetchAll();
    foreach ($d7_aliases as $d7_alias) {
      $d7_path = $d7_alias->source;
      $d7_alias = $d7_alias->alias;

      // On D8, aliases and paths are prefixed with '/'.
      $d8_alias = '/' . $d7_alias;
      $d8_path = '/' . $d7_path;

      // See if this alias exists on D8.
      $query = $conn_drupal8->query(
        "select * from {path_alias} where alias = :d8_alias",
        [':d8_alias' => $d8_alias]);
      $d8_check = $query->fetchAll();
      if (count($d8_check) > 0) {
        // Alias already exists on D8, no further action required.
        continue;
      }

      // If we get to here then we need to create the alias in D8.
      $path_alias = $this->entityTypeManager->getStorage('path_alias')->create([
        'path' => $d8_path,
        'alias' => $d8_alias,
      ]);
      $path_alias->save();
      $this->getIo()->info(
        '** Creating alias - ' . $d8_alias
      );

    }
    $this->getIo()->info('Completed post migration alias processing.');
  }

}
