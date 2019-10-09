<?php

namespace Drupal\migrate_nidirect_node;

use Drupal\Core\Database\Database;

/**
 * Class NodeMigrationProcessors.
 *
 * @package Drupal\migrate_nidirect_node
 */
class NodeMigrationProcessors {

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
   * {@inheritdoc}
   */
  public function __construct() {
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
  }

  /**
   * Updates the publishing status of a given node type.
   *
   * @param string $node_type
   *   The node type to process.
   */
  public function publishingStatus($node_type) {
    // Find all out current node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} WHERE type = :node_type ORDER BY nid ASC", [':node_type' => $node_type]);
    $d8_nids = $query->fetchAllAssoc('nid');

    if (count($d8_nids) < 1) {
      return;
    }
    // Load source node publish status fields.
    $query = $this->dbConnMigrate->query("SELECT nid, status FROM {node} WHERE nid IN (:nids[]) ORDER BY nid ASC", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values with those from D7.
    // There are three tables that need an adjustment ranging
    // from node revisions to content moderation tracking tables.
    foreach ($migrate_nid_status as $row) {
      // Need to fetch the D8 revision ID for any node as it doesn't always
      // match the source db.
      $vid = $this->dbConnDrupal8->query(
        "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $row->nid]
      )->fetchField();

      // Run an update statement per item. Refinement might be to run a
      // cross-DB SELECT query to power an UPDATE using a JOIN.
      $query = $this->dbConnDrupal8->update('node_field_data')
        ->fields(['status' => $row->status])
        ->condition('nid', $row->nid)
        ->execute();

      $query = $this->dbConnDrupal8->update('node_field_revision')
        ->fields(['status' => $row->status])
        ->condition('nid', $row->nid)
        ->condition('vid', $vid)
        ->execute();

      $query = $this->dbConnDrupal8->update('content_moderation_state_field_data')
        ->fields(['moderation_state' => 'published'])
        ->condition('content_entity_id', $row->nid)
        ->condition('content_entity_revision_id', $vid)
        ->execute();
    }
    drupal_flush_all_caches();

  }

}
