<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostMetatagCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostMetatagCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:metatag')
      ->setDescription($this->trans('commands.nidirect.migrate.post.metatag.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $updated = 0;
    $failed_updates = [];
    $conn_drupal8 = Database::getConnection('default', 'default');
    $conn_migrate = Database::getConnection('default', 'migrate');

    $this->getIo()->info('Attempting to fix metatag issues.');

    $query = $conn_migrate->query("select m1.entity_id, m1.data 
        from {metatag} m1 
        join (select max(revision_id) as revision_id, entity_id  
              from {metatag} 
              where data like 'a:1:{s:8:\"keywords\";a:1:{s:5:\"value\";%' 
              and entity_type = 'node' group by entity_id) m2
        on m1.entity_id = m2.entity_id
        and m1.revision_id = m2.revision_id");
    $results = $query->fetchAllKeyed();

    // Lame method of bulk updating but allows logging of failed update ID's.
    foreach ($results as $entity_id => $data) {
      $x = 1;
      $this->getIo()->info('Entity ID - ' . $entity_id . ' ,  data - ' . $data);
      /*$result = $conn_drupal8->update('taxonomy_term__parent')
        ->fields(['parent_target_id' => $parent])
        ->condition('entity_id', $tid, '=')
        ->execute();
      $updated += $result;

      // If we didn't get an update log the entity associated with that failure.
      if ($result < 1) {
        $failed_updates[] = entity_id;
      }*/
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
