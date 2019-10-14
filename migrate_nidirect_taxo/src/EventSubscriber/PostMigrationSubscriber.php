<?php

namespace Drupal\migrate_nidirect_taxo\EventSubscriber;

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
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   */
  public function __construct(LoggerChannelFactory $logger) {
    $this->logger = $logger->get('migrate_nidirect_taxo');
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

    $this->logger->notice('Processing Published status for content type: @type', ['@type' => $event_id]);
    // Only process nodes, nothing else.
//    if (substr($event_id, 0, 5) == 'node_') {
//      $content_type = substr($event_id, 5);
//      $this->logger->notice('Processing Published status for content type: @type', ['@type' => $content_type]);
//      $this->nodeMigrationProcessors->PublishingStatus($content_type);
//    }
  }

}
