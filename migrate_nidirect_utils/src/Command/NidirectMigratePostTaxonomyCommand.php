<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostTaxonomyCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostTaxonomyCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:taxonomy')
      ->setDescription($this->trans('commands.nidirect.migrate.post.taxonomy.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $updated = 0;
    $failed_updates = [];
    $conn_drupal8 = Database::getConnection('default', 'default');
    $conn_migrate = Database::getConnection('default', 'migrate');

    $this->getIo()->info('Attempting to fix taxonomy issues.');

    $query = $conn_migrate->query("SELECT tid, parent FROM {taxonomy_term_hierarchy} WHERE parent > 0");
    $results = $query->fetchAllKeyed();

    // Lame method of bulk updating but allows logging of failed update ID's.
    foreach ($results as $tid => $parent) {
      $result = $conn_drupal8->update('taxonomy_term__parent')
        ->fields(['parent_target_id' => $parent])
        ->condition('entity_id', $tid, '=')
        ->execute();
      $updated += $result;

      // If we didn't get an update log the entity associated with that failure.
      if ($result < 1) {
        $failed_updates[] = entity_id;
      }
    }

    $this->getIo()->info('Updated ' . $updated . ' of ' . count($results) . ' parent term targets.');

    if (count($results) == $updated) {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.taxonomy.messages.success'));
    }
    else {
      $this->getIo()->info('Failed to update for term entities: ' . explode(',', $failed_updates));
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.taxonomy.messages.failure'));
    }

  }

}
