<?php

namespace Drupal\migrate_nidirect_taxo\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
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
   * Drupal\Core\Entity\Query\QueryInterface definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(LoggerChannelFactory $logger, EntityTypeManager $entity_type_manager) {
    $this->logger = $logger->get('migrate_nidirect_taxo');
    $this->entityTypeManager = $entity_type_manager;
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

    // Only process taxonomy terms, nothing else.
    if (substr($event_id, 0, 25) == 'upgrade_d7_taxonomy_term_') {
      $this->logger->notice('Temporarily disabling PathAuto patterns for taxonomies.');

      /*
       * Drupal taxonomy migrations don't play nice with hierarchies and
       * screw up the generation of path aliases in PathAuto. To combat this
       * we will disable the PathAuto taxonomy patterns, import, process
       * the parent tids, enable the patterns and generate the aliases.
       */

      // Fetch all the taxonomy pathauto patterns.
      $pathauto_storage = $this->entityTypeManager->getStorage('pathauto_pattern');
      $query = $pathauto_storage->getQuery();
      $query->condition('id', 'term', 'STARTS_WITH');
      $query->condition('status', 1);
      $ids = $query->execute();

      $patterns = $pathauto_storage->loadMultiple($ids);

      // Disable each pathauto pattern.
      foreach ($patterns as $pattern) {
        $pattern->disable();
        $pattern->save();
      }
    }
  }

}
