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

    // Flag counts.
    $query = $conn_migrate->query("
      SELECT
        CONCAT('FLAG_ID__', fid) as flag_id,
        entity_type,
        entity_id,
        count,
        last_updated
      FROM {flag_counts}
    ");
    $flag_count_results = $query->fetchAll();

    // Flagging.
    $query = $conn_migrate->query("
      SELECT
        flagging_id as id,
        'FLAG_MACHINE_NAME' as flag_id,
        entity_type,
        entity_id,
        uid,
        sid as session_id,
        timestamp as created
      FROM {flagging}
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

  }

}
