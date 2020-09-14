<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\node\Entity\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEnd
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

    // Verify that the metatag module is enabled.
    $moduleHandler = \Drupal::service('module_handler');
    if (!$moduleHandler->moduleExists('metatag')) {
      return 1;
    }

    // Verify Drupal 7 metatag table exists.
    if (!$conn_migrate->schema()->tableExists('metatag')) {
      return 2;
    }

    $this->getIo()->info('Attempting to fix metatag issues.');

    // Get a list of custom metatags from NIDirect (D7)
    // (only take the latest revision).
    $query = $conn_migrate->query("select m1.entity_id, m1.data
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
          $meta = unserialize(($node->field_meta_tags->value));
          // Set the keyword/abstract.
          $meta[$key] = $value;
          // Save the node.
          $node->field_meta_tags->value = serialize($meta);
          $node->save();
          $updated++;
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

    $this->getIo()->info('Updated ' . $updated . ' of ' . count($results) . ' custom metatags.');

    if (count($results) == $updated) {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.metatag.messages.success'));
    }
    else {
      $this->getIo()->info('Failed to update metatag entities: ' . implode(',', $failed_updates));
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.metatag.messages.failure'));
      return -1;
    }

  }

}
