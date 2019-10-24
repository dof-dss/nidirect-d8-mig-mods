<?php

namespace Drupal\migrate_nidirect_node\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\migrate_nidirect_utils\MigrationProcessors;

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
   * NodeMigrationProcessors definition.
   *
   * @var \Drupal\migrate_nidirect_utils\MigrationProcessors
   */
  protected $migrationProcessors;

  /**
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   * @param \Drupal\migrate_nidirect_utils\MigrationProcessors $migration_processors
   *   Migration processors.
   */
  public function __construct(LoggerChannelFactory $logger, MigrationProcessors $migration_processors) {
    $this->logger = $logger->get('migrate_nidirect_node');
    $this->migrationProcessors = $migration_processors;
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
    var_dump($event_id);
    // Only process nodes, nothing else.
    if (substr($event_id, 0, 5) == 'node_') {
      $content_type = substr($event_id, 5);
      $this->logger->notice($this->migrationProcessors->PublishingStatus($content_type));
      $this->logger->notice($this->migrationProcessors->nodeFlags($content_type));
      $this->logger->notice($this->migrationProcessors->metatags());
    }
  }

}
