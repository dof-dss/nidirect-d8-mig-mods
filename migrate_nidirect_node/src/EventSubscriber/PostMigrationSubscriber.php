<?php

namespace Drupal\migrate_nidirect_node\EventSubscriber;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Stores the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   * @param \Drupal\migrate_nidirect_utils\MigrationProcessors $migration_processors
   *   Migration processors.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactory $logger,
                              MigrationProcessors $migration_processors) {
    $this->logger = $logger->get('migrate_nidirect_node');
    $this->migrationProcessors = $migration_processors;
    $this->entityTypeManager = $entity_type_manager;
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

    // Only process nodes, nothing else.
    if (substr($event_id, 0, 5) == 'node_') {
      $content_type = substr($event_id, 5);
      $this->logger->notice($this->migrationProcessors->publishingStatus($content_type));
      if (preg_match('/revision_/', $content_type)) {
        $this->logger->notice($this->migrationProcessors->revisionStatus($content_type));
      }
      $this->logger->notice($this->migrationProcessors->flags($content_type));
      $this->logger->notice($this->migrationProcessors->metatags());

      // Process audit flags for listed content types.
      if (in_array($content_type, ['article', 'contact', 'page'])) {
        $this->logger->notice('Processing audit data for ' . $content_type);
        $this->logger->notice($this->migrationProcessors->audit($content_type));
      }
    }

    // One off landing page updates.
    if ($event_id == 'node_landing_page') {
      $this->landingPageUpdates();
    }
  }

  /**
   * Post migrate updates for Landing Pages.
   */
  public function landingPageUpdates() {
    $this->logger->notice('Post migrate landing page processing.');

    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');

    // Retrieve all landing pages from D7.
    $query = $conn_migrate->query(
      "select * from {node} where type = 'landing_page' and status = 1");
    $d7_landing_pages = $query->fetchAll();
    foreach ($d7_landing_pages as $d7_landing_page) {
      $nid = $d7_landing_page->nid;

      // Now look to see if there is a redirect to this node
      // from a taxonomy term.
      $query2 = $conn_migrate->query(
        "select source from {redirect} where redirect = 'node/" . $nid . "' and source like 'taxonomy/term/%'");
      $source = $query2->fetchField();
      if ($source) {
        // Extract the term tid from the source.
        $tid = str_replace('taxonomy/term/', '', $source);
        $term = $this->entityTypeManager->getStorage("taxonomy_term")->load($tid);
        if ($term) {
          // Load the landing page node.
          $entity = $this->entityTypeManager->getStorage("node")->load($nid);
          if ($entity) {
            // Now set the landing page subtheme to this tid
            // (as the method for replacing taxonomy terms in
            // lists has been changed in the D8 site).
            if ($entity->get('field_subtheme')->target_id != $tid) {
              $entity->set('field_subtheme', ['target_id' => $tid]);
              $entity->set('moderation_state', 'published');
              $entity->save();
            }
          }
        }
      }
    }
    $this->logger->notice('Post migrate landing page processing completed.');
  }

}
