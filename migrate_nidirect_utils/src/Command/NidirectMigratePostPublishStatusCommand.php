<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\node\Entity\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostPublishStatusCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostPublishStatusCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:publish_status')
      ->setDescription($this->trans('commands.nidirect.migrate.post.publish_status.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');
    $this->getIo()->info('Sync node publish status values after migration');

    // Find all out current node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    $query = $conn_drupal8->query("SELECT nid FROM {node} ORDER BY nid ASC");
    $d8_nids = $query->fetchAllAssoc('nid');

    // Load source node publish status fields.
    $query = $conn_migrate->query("
      SELECT nid, status FROM {node}
      WHERE nid IN (:nids[])
      ORDER BY nid ASC
    ", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values with those from D7.
    // There are three tables that need an adjustment ranging
    // from node revisions to content moderation tracking tables.
    foreach ($migrate_nid_status as $row) {
      // Need to fetch the D8 revision ID for any node as it doesn't always match the source db.
      $vid = $conn_drupal8->query(
        "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $row->nid]
        )->fetchField();

      // Run an update statement per item. Refinement might be to run a cross-DB SELECT query to power an UPDATE using a JOIN.
      $query = $conn_drupal8->update('node_field_data')
        ->fields(['status' => $row->status])
        ->condition('nid', $row->nid)
        ->execute();

      $query = $conn_drupal8->update('node_field_revision')
        ->fields(['status' => $row->status])
        ->condition('nid', $row->nid)
        ->condition('vid', $vid)
        ->execute();

      $query = $conn_drupal8->update('content_moderation_state_field_data')
        ->fields(['moderation_state' => 'published'])
        ->condition('content_entity_id', $row->nid)
        ->condition('content_entity_revision_id', $vid)
        ->execute();
    }

    $this->getIo()->info('Updated ' . count($migrate_nid_status) . ' records in node_field_data table.');
    $this->getIo()->info('Clearing all caches...');
    drupal_flush_all_caches();
  }

}
