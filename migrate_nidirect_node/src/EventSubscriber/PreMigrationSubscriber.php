<?php

namespace Drupal\migrate_nidirect_node\EventSubscriber;

use Drupal\Core\Extension\ModuleInstaller;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class PreMigrationSubscriber.
 *
 * Post Migrate processes.
 */
class PreMigrationSubscriber implements EventSubscriberInterface {

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
    $this->logger = $logger->get('migrate_nidirect_node');
  }

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_IMPORT][] = ['onMigratePreImport'];
    return $events;
  }

  /**
   * Handle post import migration event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePreImport(MigrateImportEvent $event) {
    $event_id = $event->getMigration()->getBaseId();

    // Only process nodes, nothing else.
    if (substr($event_id, 0, 5) == 'node_') {

      // Truncate the table for the 'WhatLinksHere' module
      //  during import to prevent duplicate SQL entry error.
      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('whatlinkshere')){
        \Drupal::database()->truncate('whatlinkshere')->execute();
        $this->logger->notice('Truncating \'WhatLinksHere\' table');
      }
    }
  }

}
