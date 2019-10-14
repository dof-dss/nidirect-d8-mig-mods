<?php

namespace Drupal\migrate_nidirect_taxo\EventSubscriber;

use Drupal\Core\Database\Database;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class PostMigrationSubscriber.
 *
 * Post Migrate processes.
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Migration database connection (Drupal 7).
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnMigrate;
  /**
   * Drupal 8 database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnDrupal8;

  /**
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   */
  public function __construct(LoggerChannelFactory $logger) {
    $this->logger = $logger->get('migrate_nidirect_taxo');
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
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

    // Only process taxonomy terms, nothing else.
    if (substr($event_id, 0, 25) == 'upgrade_d7_taxonomy_term_') {
      $content_type = substr($event_id, 25);
      $this->logger->notice('Processing parent terms for taxonomy vocabulary: @type', ['@type' => $content_type]);

      $updated = 0;
      $failed_updates = [];

      // Verify Drupal 8 taxonomy table exists.
      if (!$this->dbConnDrupal8->schema()->tableExists('taxonomy_term__parent')) {
        $this->logger->notice('taxonomy_term__parent table missing.');
      }

      // Verify Drupal 7 taxonomy table exists.
      if (!$this->dbConnMigrate->schema()->tableExists('taxonomy_term_hierarchy')) {
        $this->logger->notice('taxonomy_term_hierarchy table missing.');
      }

      $query = $this->dbConnMigrate->query("SELECT tid, parent FROM {taxonomy_term_hierarchy} WHERE parent > 0");
      $results = $query->fetchAllKeyed();

      // Lame method of bulk updating but allows logging of failed update ID's.
      foreach ($results as $tid => $parent) {
        $result = $this->dbConnDrupal8->update('taxonomy_term__parent')
          ->fields(['parent_target_id' => $parent])
          ->condition('entity_id', $tid, '=')
          ->execute();
        $updated += $result;

        // If we didn't get an update, log the failed entity ID.
        if ($result < 1) {
          $failed_updates[] = $tid;
        }
      }

      $this->logger->notice('Updated @updated of @parents parent term targets.', ['@updated' => $updated, '@parents' => count($results)]);

      if (count($results) != $updated) {
        $this->logger->notice('Failed to update for term entities: @failures', ['@failures' => implode(',', $failed_updates)]);
      }

    }
  }

}
