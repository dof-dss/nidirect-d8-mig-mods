<?php

namespace Drupal\migrate_nidirect_utils;

use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * Node Storage definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

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
  public function __construct(ModuleHandler $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->moduleHandler = $module_handler;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
  }

  /**
   * Updates the revisions for a given node type.
   *
   * @param string $node_type
   *   The node type to process.
   *
   * @return string
   *   Information/results of on the process.
   */
  public function RevisionStatus(string $node_type) {
    // Find all out current node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    $node_type = preg_replace('/revision_/', '', $node_type);
    $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} WHERE type = :node_type ORDER BY nid ASC", [':node_type' => $node_type]);
    $d8_nids = $query->fetchAllAssoc('nid');

    if (count($d8_nids) < 1) {
      return 'No revision entities found for processing.';
    }
    // Load source node publish status fields.
    $query = $this->dbConnMigrate->query("SELECT nid, status FROM {node} WHERE nid IN (:nids[]) ORDER BY nid ASC", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values with those from D7.
    // There are three tables that need an adjustment ranging
    // from node revisions to content moderation tracking tables.
    foreach ($migrate_nid_status as $row) {
      // Get the D7 revision id.
      $vid = $this->dbConnMigrate->query(
        "SELECT vid FROM {node} WHERE nid = :nid", [':nid' => $row->nid]
      )->fetchField();

      // Need to fetch the D8 revision ID for any node as it doesn't always
      // match the source db.
      $d8_vid = $this->dbConnDrupal8->query(
        "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $row->nid]
      )->fetchField();

      if ($vid != $d8_vid) {
        $vid = $this->updateCurrentRevision($row->nid, $vid, $d8_vid);
      }

      // The 'revision_translation_affected' field is poorly documented (and
      // understood) in Drupal core, and is sometimes set to NULL after migrating
      // content from Drupal 7. There is much discussion at
      // https://www.drupal.org/project/drupal/issues/2746541 but after testing
      // and investigation I am yet to find a case where it should not be set to '1'.
      // Hence, we set it to '1' across the board to solve the problem of revisions
      // not appearing on the revisions tab.
      $query = $this->dbConnDrupal8->update('node_field_revision')
        ->fields(['revision_translation_affected' => 1])
        ->condition('nid', $row->nid)
        ->execute();

      // Make sure that we have a 'published' revision.
      $query = $this->dbConnDrupal8->update('content_moderation_state_field_data')
        ->fields(['moderation_state' => 'published'])
        ->condition('content_entity_id', $row->nid)
        ->condition('content_entity_revision_id', $vid)
        ->execute();

      $query = $this->dbConnDrupal8->update('content_moderation_state_field_revision')
        ->fields(['moderation_state' => 'published'])
        ->condition('content_entity_id', $row->nid)
        ->condition('content_entity_revision_id', $vid)
        ->execute();
    }

    return 'Updated revisions for ' . count($migrate_nid_status) . ' nodes.';
  }

  /**
   * Updates the current revision for the given node.
   *
   * @param int $nid
   *   The node id.
   * @param int $vid
   *   The target revision id (from D7).
   * @param int $nid
   *   The current D8 revision id.
   *
   * @return string
   *   Current revision id.
   */
  private function updateCurrentRevision(int $nid, int $vid, int $d8_vid) {
    // Does this revision exist in D8 ?
    $check_vid = $this->dbConnDrupal8->query(
      "SELECT vid FROM {node_field_revision} WHERE nid = :nid AND vid = :vid", [':nid' => $nid, ':vid' => $vid]
    )->fetchField();
    if (!empty($check_vid)) {
      // Does the current D8 revision exist in D7 ?
      $check_d7_vid = $this->dbConnMigrate->query(
        "SELECT vid FROM {node_revision} WHERE nid = :nid and vid = :vid", [':nid' => $nid, ':vid' => $d8_vid]
      )->fetchField();
      if (!empty($check_d7_vid)) {
        // Make the D7 revision the current revision in D8.
        // N.B. This will only work in the 'one hit' migration scenario, it may cause problems
        // if the migration runs again and in the meantime the editors have reverted to an older
        // revision that also came from D7.
        $query = $this->dbConnDrupal8->update('node')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();
        $query = $this->dbConnDrupal8->update('node_field_data')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();
      }
      return $vid;
    } else {
      return $d8_vid;
    }
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
  public function publishingStatus(string $node_type) {
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

    $output = '';

    // Map the old D7 node types to the new D8 types.
    // We need to use the new node name to look up existing flag data.
    $node_type_map = [
      'nidirect_contact' => 'contact',
      'nidirect_driving_instructor' => 'driving_instructor',
      'nidirect_gp_practice' => 'gp_practice',
    ];

    if (array_key_exists($entity_type, $node_type_map)) {
      $entity_type = $node_type_map[$entity_type];
    }

    // Map the migration id's to the D7 vocabularies.
    $migration_vocabulary_ids = [
      'ni_postcodes' => 'ni_postcodes',
      'hc_symptoms' => 'hc_symptoms',
      'hc_info_sources' => 'hc_info_sources',
      'hc_condition_type' => 'hc_condition_type',
      'hc_body_system' => 'hc_body_system',
      'hc_body_location' => 'hc_body_location',
      'drive_instr_categories' => 'di_categories',
      'districts_in_northern_ireland' => 'districts_in_northern_ireland',
      'contact_categories' => 'nidirect_contact_categories',
      'site_themes' => 'site_topics',
    ];

    // If it's a vocabulary, assign the correct machine name.
    if (array_key_exists($entity_type, $migration_vocabulary_ids)) {
      $entity_base = 'taxonomy';
      $entity_type = $migration_vocabulary_ids[$entity_type];
    }

    // Map the Flag id to the machine name.
    $flags = [
      '2' => 'featured_content',
      '4' => 'hide_content',
      '5' => 'hide_theme',
      '6' => 'show_listing',
      '7' => 'promote_to_all_pages',
    ];

    $flag_id_expression = "CASE fid
      WHEN 2 THEN 'featured_content'
      WHEN 4 THEN 'hide_content'
      WHEN 5 THEN 'hide_theme'
      WHEN 6 THEN 'show_listing'
      WHEN 7 THEN 'promote_to_all_pages'
     END";

    // Process each flag type individually.
    foreach ($flags as $flag_id => $flag_name) {
      // Extract existing Flag data in the D8 database.
      $query = $this->dbConnDrupal8->select('flagging', 'f');
      if ($entity_base == 'taxonomy') {
        $query->join('taxonomy_term_data', 't', 'f.entity_id = t.tid');
        $query->fields('f', ['entity_id', 'flag_id']);
        $query->condition('t.vid', $entity_type);
      }
      else {
        $query->join('node', 'n', 'f.entity_id = n.nid');
        $query->fields('f', ['entity_id', 'flag_id']);
        $query->condition('n.type', $entity_type);
      }
      $query->condition('f.flag_id', $flag_name);

      $existing_flagging = $query->execute()->fetchAllAssoc('entity_id');

      // Fetch existing flag counts for the vocabulary or node type.
      $query = $this->dbConnMigrate->select('flag_counts', 'f');
      if ($entity_base == 'taxonomy') {
        $query->join('taxonomy_term_data', 't', 'f.entity_id = t.tid');
        $query->join('taxonomy_vocabulary', 'v', 't.vid = v.vid');
      }
      else {
        $query->join('node', 'n', 'f.entity_id = n.nid');
      }
      $query->addExpression($flag_id_expression, 'flag_id');
      $query->fields('f', ['entity_type', 'entity_id', 'count', 'last_updated']);
      $query->condition('f.fid', $flag_id);
      if ($entity_base == 'taxonomy') {
        $query->condition('v.machine_name', $entity_type);
      }
      else {
        $query->condition('n.type', $entity_type);
      }
      $flag_count_results = $query->execute()->fetchAllAssoc('entity_id');

      // Fetch existing flagging results for the vocabulary or node type.
      $query = $this->dbConnMigrate->select('flagging', 'f');
      if ($entity_base == 'taxonomy') {
        $query->join('taxonomy_term_data', 't', 'f.entity_id = t.tid');
        $query->join('taxonomy_vocabulary', 'v', 't.vid = v.vid');
      }
      else {
        $query->join('node', 'n', 'f.entity_id = n.nid');
      }
      $query->addExpression($flag_id_expression, 'flag_id');
      $query->fields('f', ['entity_type', 'entity_id', 'uid']);
      $query->addField('f', 'flagging_id', 'id');
      $query->addField('f', 'sid', 'session_id');
      $query->addField('f', 'timestamp', 'created');
      $query->condition('f.fid', $flag_id);
      if ($entity_base == 'taxonomy') {
        $query->condition('v.machine_name', $entity_type);
      }
      else {
        $query->condition('n.type', $entity_type);
      }
      $flagging_results = $query->execute()->fetchAllAssoc('entity_id');

      // Remove any existing D8 flags from the arrays to be processed.
      foreach ($flag_count_results as $id => $flag_count_result) {
        if (array_key_exists($id, $existing_flagging)) {
          unset($flag_count_results[$id]);
          unset($flagging_results[$id]);
        }
      }

      // Begin processing result set arrays before inserting into D8 db.
      $flag_count_data = [];
      $flagging_data = [];

      // Process Flag Counts data.
      if (count($flag_count_results) > 0) {
        foreach ($flag_count_results as $i => $row) {
          $flag_count_data[] = (array) $row;
        }

        if (count($flag_count_data) > 0) {
          // Fetch an array of existing flag_counts data to avoid
          // primary key integrity key errors on future inserts.
          $query = $this->dbConnDrupal8->query('SELECT * FROM {flag_counts}');
          $d8_flag_counts = $query->fetchAllAssoc('entity_id');

          // Populate the flag_counts table.
          $query = $this->dbConnDrupal8->insert('flag_counts')->fields([
            'flag_id',
            'entity_type',
            'entity_id',
            'count',
            'last_updated',
          ]);
          foreach ($flag_count_data as $row) {
            // Check we haven't already got this present in the destination table.
            if (array_key_exists($row['entity_id'], $d8_flag_counts) == FALSE) {
              $query->values($row);
            }
          }
          $query->execute();
        }
      }

      // Process Flagging data.
      if (count($flagging_results) > 0) {
        foreach ($flagging_results as $i => $row) {
          $row = (array) $row;
          // Create UUID and global signifier to store in the D8 schema.
          $row['uuid'] = \Drupal::service('uuid')->generate();
          $row['global'] = TRUE;
          $flagging_data[] = $row;
        }

        // Fetch an array of existing flagging data to avoid
        // primary key integrity key errors on future inserts.
        $query = $this->dbConnDrupal8->query('SELECT * FROM {flagging}');
        $d8_flagging = $query->fetchAllAssoc('entity_id');

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
          // Check we haven't already got this present in the destination table.
          if (array_key_exists($row['entity_id'], $d8_flagging) == FALSE) {
            $query->values($row);
          }
        }
        $query->execute();
      }

      $output .= "Processed " . count($flagging_results) . " $flag_name flags for $entity_type \r\n";
    }

    return $output;
  }

  /**
   * Import audit data.
   *
   * @param string $entity_type
   *   The entity type to process.
   *
   * @return string
   *   Information/results of on the process.
   */
  public function audit($entity_type) {

    // Only process the entity types listed in the array.
    if (!in_array($entity_type, ['article', 'contact', 'page'])) {
      return "Audit processing for " . $entity_type . " is not enabled.";
    }

    $d7_audit_nids = $this->dbConnMigrate->query("
      SELECT f.entity_id
      FROM flagging f
      JOIN node n
      ON f.entity_id = n.nid
      WHERE n.type = '$entity_type'
      AND f.fid = 1
    ")->fetchCol(0);

    $today = date('Y-m-d', \Drupal::time()->getCurrentTime());

    // Fetch current or future audit dates.
    $excluded_audit_nids = $this->dbConnDrupal8->query("
      SELECT a.entity_id
      FROM node__field_next_audit_due AS a
      JOIN node n
      ON a.entity_id = n.nid
      WHERE n.type = '$entity_type'
    ")->fetchCol(0);

    // Create an array based on D7 nids but with excluded nids removed.
    $nids_to_update = array_diff($d7_audit_nids, $excluded_audit_nids);

    $error_nids = [];

    foreach ($nids_to_update as $id => $nid) {
      $node = $this->nodeStorage->load($nid);
      if ($node instanceof Node) {
        if ($node->hasField('field_next_audit_due')) {
          // Just set next audit date to today as will show in 'needs audit'
          // report if next audit date is today or earlier.
          // Avoid creating a new revision here by updating the existing revision directly.
          $vid = $this->dbConnDrupal8->query(
            "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $nid]
          )->fetchField();
          $langcode = $this->dbConnDrupal8->query(
            "SELECT langcode FROM {node} WHERE nid = :nid", [':nid' => $nid]
          )->fetchField();
          if (!empty($vid) && !empty($langcode)) {
            $query = $this->dbConnDrupal8->insert('node__field_next_audit_due')
              ->fields(['bundle' => $entity_type,
                'deleted' => 0,
                'entity_id' => $nid,
                'revision_id' => $vid,
                'langcode' => $langcode,
                'delta' => 0,
                'field_next_audit_due_value' => $today])
              ->execute();
          }
        }
      }
      else {
        $error_nids[] = $nid;
      }
    }

    if (count($nids_to_update) > 0) {
      // Must flush all caches or the SQL insertion above will not take effect.
      drupal_flush_all_caches();
    }

    if (count($error_nids) > 0) {
      return "Unable to process audit for nids: " . implode(',', $error_nids);
    }
    else {
      return "Processed audit for " . count($nids_to_update) . " nodes";
    }
  }

}
