<?php

namespace Drupal\migrate_nidirect_utils;

use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\node\Entity\Node;

/**
 * Class MigrationProcessors.
 *
 * @package Drupal\migrate_nidirect_utils
 */
class MigrationProcessors {

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

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
  public function __construct(ModuleHandler $module_handler) {
    $this->moduleHandler = $module_handler;
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
  }

  /**
   * Updates the publishing status of a given node type.
   *
   * @param string $node_type
   *   The node type to process.
   *
   * @return string
   *   Information/results of on the process.
   */
  public function publishingStatus($node_type) {
    // Find all out current node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} WHERE type = :node_type ORDER BY nid ASC", [':node_type' => $node_type]);
    $d8_nids = $query->fetchAllAssoc('nid');

    if (count($d8_nids) < 1) {
      return 'No entities found for processing.';
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

    return 'Updated ' . count($migrate_nid_status) . ' records in node_field_data table.';
  }

  /**
   * Import custom metatags.
   *
   * Inserts latest custom metatag revision into the
   * matching migrated node entity.
   * Note: No requirement to migrate by entity type due to
   * the very low number of custom tags.
   */
  public function metatags() {
    $output = '';
    $updated = 0;
    $failed_updates = [];

    // Verify that the metatag module is enabled.
    if (!$this->moduleHandler->moduleExists('metatag')) {
      return 'Skipping metatag processing as module is not enabled.';
    }

    // Verify Drupal 7 metatag table exists.
    if (!$this->dbConnMigrate->schema()->tableExists('metatag')) {
      return 'Skipping metatag processing as metatag table missing from migration database.';
    }

    // Get a list of custom metatags from NIDirect (D7)
    // (only take the latest revision).
    $query = $this->dbConnMigrate->query("select m1.entity_id, m1.data 
        from {metatag} m1
        join (select max(revision_id) as revision_id, entity_id
              from {metatag}
              where data like 'a:1:_s:8:%'
              and entity_type = 'node' group by entity_id) m2
        on m1.entity_id = m2.entity_id
        and m1.revision_id = m2.revision_id");
    $results = $query->fetchAllKeyed();

    // Loop through and update nodes in NIDirect (D8).
    foreach ($results as $entity_id => $data) {
      $new_data = unserialize($data);
      if (isset($new_data['keywords']) || isset($new_data['abstract'])) {
        $key = 'keywords';
        if (isset($new_data['abstract'])) {
          $key = 'abstract';
        }
        $value = $new_data[$key]['value'];
        // Load the node in D8.
        $node = Node::load($entity_id);
        if ($node) {
          // Retrieve the existing metatags.
          $metatags = unserialize(($node->field_meta_tags->value));
          // Set the keyword/abstract.
          $metatags[$key] = $value;

          $field_meta_tags_value = serialize($metatags);

          // Save to the node of the value doesn't exist.
          if ($field_meta_tags_value != $node->field_meta_tags->value) {
            $node->field_meta_tags->value = $field_meta_tags_value;
            $node->save();
            $updated++;
          }
        }
        else {
          $failed_updates[] = $entity_id;
        }
      }
      else {
        // If it isn't 'abstract' or 'keyword' then fail it.
        $failed_updates[] = $entity_id;
      }
    }

    if ($updated > 0) {
      $output .= 'Imported ' . $updated . ' custom metatag definition(s).';
    }
    else {
      $output .= 'No custom metatag definitions imported.';
    }

    if (count($failed_updates) > 0) {
      $output .= 'Failed to update metatag entities: ' . implode(',', $failed_updates);
    }

    return $output;
  }

  /**
   * Import Flag module data.
   *
   * @param string $entity_type
   *   The entity type to process.
   *
   * @return string
   *   Information/results of on the process.
   */
  public function flags($entity_type) {

    // Verify that the flag module is enabled.
    $moduleHandler = \Drupal::service('module_handler');
    if (!$moduleHandler->moduleExists('flag')) {
      return 'Flag module not enabled.';
    }

    // Verify Drupal 8 flag tables exists.
    if (!$this->dbConnDrupal8->schema()->tableExists('flag_counts') || !$this->dbConnDrupal8->schema()->tableExists('flagging')) {
      return 'Missing flag_counts table in Drupal 8.';
    }

    // Verify Drupal 7 flag tables exists.
    if (!$this->dbConnMigrate->schema()->tableExists('flag_counts') || !$this->dbConnMigrate->schema()->tableExists('flagging')) {
      return 'Missing flag_counts table in Drupal 7.';
    }

    // Fetch existing flags.
    $existing_flagging = $this->dbConnDrupal8->query("
      SELECT entity_id 
      FROM {flagging} AS f 
      INNER JOIN {node} AS n 
      ON f.entity_id = n.nid 
      WHERE n.type = '$entity_type'")->fetchAllAssoc('entity_id');;

    // Flag counts.
    // (Exclude 'content_audit' flag as auditing has been implemented
    // without a flag in Drupal 8)
    $flag_count_results = $this->dbConnMigrate->query("
      SELECT
        CASE fid
          WHEN 2 THEN 'featured_content'
          WHEN 4 THEN 'hide_content'
          WHEN 5 THEN 'hide_theme'
          WHEN 6 THEN 'show_listing'
          WHEN 7 THEN 'promote_to_all_pages'
        END as flag_id,
        entity_type,
        entity_id,
        count,
        last_updated
      FROM {flag_counts}
      INNER JOIN {node}
      ON entity_id = node.nid
      WHERE fid in (2,4,5,6,7)
      AND node.type = '$entity_type'
    ")->fetchAllAssoc('entity_id');

    // Flagging.
    // (Exclude 'content_audit' flag as auditing has been implemented
    // without a flag in Drupal 8)
    $flagging_results = $this->dbConnMigrate->query("
      SELECT
        flagging_id as id,
        CASE fid
          WHEN 2 THEN 'featured_content'
          WHEN 4 THEN 'hide_content'
          WHEN 5 THEN 'hide_theme'
          WHEN 6 THEN 'show_listing'
          WHEN 7 THEN 'promote_to_all_pages'
        END as flag_id,
        entity_type,
        entity_id,
        uid,
        sid as session_id,
        timestamp as created
      FROM {flagging}
      INNER JOIN {node}
      ON entity_id = node.nid
      WHERE fid in (2,4,5,6,7)
      AND node.type = '$entity_type'
    ")->fetchAllAssoc('entity_id');

    // If the flag count result entity id already exists in the D8 db,
    // remove it from the arrays to prevent re-processing.
    foreach ($flag_count_results as $id => $flag_count_result) {
      if (array_key_exists($id, $existing_flagging)) {
        unset($flag_count_results[$id]);
        unset($flagging_results[$id]);
      }
    }

    // Begin processing result set arrays before inserting into D8 db.
    $flag_count_data = [];
    $flagging_data = [];

    foreach ($flag_count_results as $i => $row) {
      $flag_count_data[] = (array) $row;
    }

    // Glue in a generated UUID + global signifier to store in the D8 schema.
    foreach ($flagging_results as $i => $row) {
      $row = (array) $row;
      $row['uuid'] = \Drupal::service('uuid')->generate();
      $row['global'] = TRUE;

      $flagging_data[] = $row;
    }

    // Populate the flag_counts table.
    $query = $this->dbConnDrupal8->insert('flag_counts')->fields([
      'flag_id',
      'entity_type',
      'entity_id',
      'count',
      'last_updated',
    ]);
    foreach ($flag_count_data as $row) {
      $query->values($row);
    }
    $query->execute();

    // Populate the flagging table.
    $query = $this->dbConnDrupal8->insert('flagging')->fields([
      'id',
      'flag_id',
      'uuid',
      'entity_type',
      'entity_id',
      'global',
      'uid',
      'session_id',
      'created',
    ]);
    foreach ($flagging_data as $row) {
      $query->values($row);
    }
    $query->execute();

    return 'Processed ' . count($flagging_data) . ' flag(s) for ' . $entity_type;
  }

}
