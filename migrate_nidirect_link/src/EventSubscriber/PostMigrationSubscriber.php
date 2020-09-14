<?php

namespace Drupal\migrate_nidirect_link\EventSubscriber;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class PostMigrationSubscriber.
 *
 * Post Migrate processes.
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * List of aliases to skip.
   *
   * @var array
   */
  protected $skipItems;

  /**
   * PostMigrationSubscriber constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactory $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger->get('migrate_nidirect_link');

    // Clashing aliases from D7 with new nodes in D8. NB: this list should be
    // kept in sync with the skip_on_value plugin in
    // drupal8/web/modules/migrate/nidirect-migrations/migrate_nidirect_link/config/install/migrate_plus.migration.upgrade_d7_url_alias.yml.
    $this->skipItems = [13638, 13639, 13640, 13641, 13642];
  }

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
    return $events;
  }

  /**
   * Handle post import migration event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {
    $event_id = $event->getMigration()->getBaseId();

    if ($event_id == 'upgrade_d7_url_alias') {

      $this->logger->notice('Post migrate alias processing.');

      $conn_migrate = Database::getConnection('default', 'migrate');
      $conn_drupal8 = Database::getConnection('default', 'default');

      // Retrieve all aliases from D7.
      $query = $conn_migrate->query(
        "select source, alias from {url_alias} where substr(source,6,length(source)) not in (" . implode(',', $this->skipItems) . ")");
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
        try {
          $path_alias->save();
        }
        catch (EntityStorageException $e) {
          $this->logger->error(
            '** Failed to create alias - ' . $d8_alias . ' for path ' . $d8_path
          );
          continue;
        }

        $this->logger->notice(
          'Creating alias - ' . $d8_alias
        );

      }
      $this->logger->notice('Completed post migration alias processing.');

    }

  }

}
