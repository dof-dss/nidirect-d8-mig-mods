<?php

namespace Drupal\migrate_nidirect_node\EventSubscriber;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\node\Entity\Node;
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
   * A collection of featured content data.
   *
   * @var array
   */
  protected $featureContent;

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
    $this->featureContent = [];
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
      if (!empty(\Drupal::state()->get('migrate_nidirect_node_semaphore'))) {
        return;
      }

      // Set a semaphore value; this migrate event seems to be triggered multiple times
      // which can give unexpected results.
      \Drupal::state()->set('migrate_nidirect_node_semaphore', TRUE);
      $this->logger->notice('Set semaphore variable for migrate_nidirect_node');

      // Populate the denormalised table that tracks nodes and term ids
      // as this isn't handled by the migrate plugins.
      $this->processTaxonomyIndexTable();

      // Recreate feature/FCL nodes (D8 only) to avoid clash with high water mark on D7 content.
      $this->logger->notice('Handling FCL/feature nodes...');
      $this->recreateFeatureNodes();
      $this->recreateFeaturedContentListNodes();

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
    if ($event_id == 'node_revision_landing_page') {
      $this->landingPageUpdates();
    }
  }

  /**
   * Post-migrate recreate feature nodes.
   */
  protected function recreateFeatureNodes() {
    $this->featureContent[] = [
      'title' => 'Wear a face covering to help reduce spread of COVID-19',
      'teaser' => 'Wear a face covering to help reduce spread of COVID-19 - they are now mandatory in certain indoor settings',
      'uri' => 'internal:/node/13662',
      'media_id' => 8939,
    ];
    $this->featureContent[] = [
      'title' => 'Coronavirus (COVID-19)',
      'teaser' => 'Updates and advice about coronavirus (COVID-19), including information about government services',
      'uri' => 'internal:/node/13394',
      'media_id' => 8786,
    ];
    $this->featureContent[] = [
      'title' => 'Universal Credit',
      'teaser' => 'Find out all you need to need to know to make a Universal Credit claim',
      'uri' => 'internal:/node/12849',
      'media_id' => 7283,
    ];

    foreach ($this->featureContent as &$feature) {
      $node = Node::create([
        'type' => 'feature',
        'langcode' => 'en',
        'moderation_state' => 'published',
        'status' => 1,
        'uid' => 1,
        'title' => $feature['title'],
        'field_teaser' => $feature['teaser'],
        'field_link_url' => [
          'uri' => $feature['uri'],
        ],
        'field_photo' => [
          'target_id' => $feature['media_id'],
        ],
      ]);
      $node->save();
      $feature['nid'] = $node->id();

      $this->logger->notice("Created feature node with title '" . $feature['title'] . "'");
    }
  }

  /**
   * Post-migrate recreate feature nodes.
   */
  protected function recreateFeaturedContentListNodes() {
    $fcl_content[] = [
      'title' => 'Homepage: featured content',
      'features' => [
        ['target_id' => $this->getFeatureByTitle('Wear a face covering to help reduce spread of COVID-19')],
        ['target_id' => $this->getFeatureByTitle('Universal Credit')],
        ['target_id' => $this->getFeatureByTitle('Coronavirus (COVID-19)')],
      ],
      'tag' => 1338,
    ];

    foreach ($fcl_content as $fcl) {
      $node = Node::create([
        'type' => 'featured_content_list',
        'langcode' => 'en',
        'moderation_state' => 'published',
        'status' => 1,
        'uid' => 1,
        'title' => $fcl['title'],
        'field_featured_content' => $fcl['features'],
        'field_tags' => $fcl['tag'],
      ]);

      $node->save();
      $this->logger->notice("Created featured content list node with title '" . $fcl['title'] . "'");
    }
  }

  /**
   * Fetches the node id of a feature node from a given title.
   *
   * @param string $title
   *   Feature node title.
   *
   * @return int
   *   The node id.
   */
  protected function getFeatureByTitle(string $title) {
    foreach ($this->featureContent as $feature) {
      if ($title === $feature['title']) {
        return (int) $feature['nid'];
      }
    }

    return 0;
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

  /**
   * Function to repopulate the taxonomy_index table that
   * the migrate process plugins do not appear to do or trigger
   * code in core to do either. This denormalised table is required
   * for key queries around taxonomy term (with depth) filters in views.
   */
  protected function processTaxonomyIndexTable() {
    $this->logger->notice('Post migrate: Update taxonomy_index table with subtheme values.');

    $conn_drupal8 = Database::getConnection('default', 'default');
    // Truncate the taxonomy_index table so it's clean to repopulate.
    $conn_drupal8->query("TRUNCATE taxonomy_index")->execute();
    $this->logger->notice('... truncated the taxonomy_index table...');

    // Populate the table with node and term ids from the topics/themes data
    // we have already migrated. INSERT IGNORE is used due to an unusual key
    // clash that is only triggered when running from Drupal migrate; the end
    // result has the correct row count.
    $conn_drupal8->query("INSERT IGNORE INTO {taxonomy_index} (nid, tid, status, sticky, created)
      SELECT
      n.nid,
      ttd.tid,
      nfd.status,
      nfd.sticky,
      nfd.created
      FROM {node} n
      JOIN {node_field_data} nfd on n.nid = nfd.nid
      JOIN {node__field_subtheme} nfs on nfs.entity_id = n.nid
      JOIN {taxonomy_term_data} ttd on ttd.tid = nfs.field_subtheme_target_id
      WHERE ttd.vid = 'site_themes'")->execute();

    $this->logger->notice('... inserted node and term id values for theme data...');

    $this->logger->notice('... done.');
  }

  protected function processPathAliases() {
    $this->logger->notice('Post migrate: Removing duplicate path aliases.');

    $conn_drupal8 = Database::getConnection('default', 'default');
    $aliases = $conn_drupal8->query("SELECT alias, GROUP_CONCAT(id) as ids, COUNT(*) FROM path_alias GROUP BY alias HAVING Count(*) > 1");

    foreach ($aliases as $alias) {
      $ids = explode(',', $alias->ids);
    }

  }

}
