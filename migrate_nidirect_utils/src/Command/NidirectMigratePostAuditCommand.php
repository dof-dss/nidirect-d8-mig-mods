<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\node\Entity\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostAuditCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostAuditCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:audit')
      ->setDescription($this->trans('commands.nidirect.migrate.post.audit.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');

    $this->getIo()->info('Started post migration audit processing.');

    // Verify Drupal 7 flag table exists.
    if (!$conn_migrate->schema()->tableExists('flagging')) {
      return 3;
    }

    // Select content flagged with 'content_audit' from D7.
    $query = $conn_migrate->query("
      SELECT 
        f.entity_id 
      FROM flagging f
      JOIN node n
      ON f.entity_id = n.nid
      WHERE n.type in ('article', 'contact', 'page')
      AND f.fid = 1
    ");
    $flag_results = $query->fetchAll();

    // Update the 'next audit due' node in D8.
    $today = date('Y-m-d', \Drupal::time()->getCurrentTime());
    foreach ($flag_results as $i => $row) {
      $row = (array) $row;
      $node = Node::load($row['entity_id']);
      // Just set next audit date to today as will show in 'needs audit' report
      // if next audit date is today or earlier.
      $node->set('field_next_audit_due', $today);
      $node->save();
    }

    $this->getIo()->info('Updated next audit date on ' . count($flag_results) . ' nodes.');
  }

}
