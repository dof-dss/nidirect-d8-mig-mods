<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\node\Entity\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostFlagCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostFlagCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:flag')
      ->setDescription($this->trans('commands.nidirect.migrate.post.flag.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');
    $this->getIo()->info('Truncated flag_counts and flagging tables.');

    // Verify that the flag module is enabled.
    $moduleHandler = \Drupal::service('module_handler');
    if (!$moduleHandler->moduleExists('flag')) {
      return 1;
    }

    // Verify Drupal 8 flag tables exists.
    if (!$conn_drupal8->schema()->tableExists('flag_counts') || !$conn_drupal8->schema()->tableExists('flagging')) {
      return 2;
    }

    // Verify Drupal 7 flag tables exists.
    if (!$conn_migrate->schema()->tableExists('flag_counts') || !$conn_migrate->schema()->tableExists('flagging')) {
      return 3;
    }

    // Clean out the flag_counts and flagging tables before we begin.
    $query = $conn_drupal8->delete('flag_counts')->execute();
    $query = $conn_drupal8->delete('flagging')->execute();

    // Flag counts.
    // (Exclude 'content_audit' flag as auditing has been implemented
    // without a flag in Drupal 8)
    $query = $conn_migrate->query("
      SELECT
        CASE fid
          WHEN 1 THEN 'content_audit'
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
      WHERE fid in (2,4,5,6,7)
    ");
    $flag_count_results = $query->fetchAll();

    // Flagging.
    // (Exclude 'content_audit' flag as auditing has been implemented
    // without a flag in Drupal 8)
    $query = $conn_migrate->query("
      SELECT
        flagging_id as id,
        CASE fid
          WHEN 1 THEN 'content_audit'
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
      WHERE fid in (2,4,5,6,7)
    ");
    $flagging_results = $query->fetchAll();

    // Begin converting/altering result set arrays to then upsert into destination db.
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
    $query = $conn_drupal8->insert('flag_counts')->fields([
      'flag_id',
      'entity_type',
      'entity_id',
      'count',
      'last_updated'
    ]);
    foreach ($flag_count_data as $row) {
      $query->values($row);
    }
    $query->execute();

    $this->getIo()->info('Inserted ' . count($flag_count_data) . ' records into flag_counts table.');

    // Populate the flagging table.
    $query = $conn_drupal8->insert('flagging')->fields([
      'id',
      'flag_id',
      'uuid',
      'entity_type',
      'entity_id',
      'global',
      'uid',
      'session_id',
      'created'
    ]);
    foreach ($flagging_data as $row) {
      $query->values($row);
    }
    $query->execute();

    $this->getIo()->info('Inserted ' . count($flagging_data) . ' records into flagging table.');
  }

}
