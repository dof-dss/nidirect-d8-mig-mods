<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEnd
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
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs a NidirectMigratePostAuditCommand object.
   *
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue service.
   */
  public function __construct(QueueFactory $queue_factory) {
    parent::__construct();
    $this->queueFactory = $queue_factory;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:audit')->setDescription(
          $this->trans('commands.nidirect.migrate.post.audit.description')
      );
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
    $query = $conn_migrate->query(
          "SELECT
              f.entity_id
            FROM flagging f
            JOIN node n
            ON f.entity_id = n.nid
            WHERE n.type in ('article', 'contact', 'page')
            AND f.fid = 1"
      );
    $flag_results = $query->fetchAll();

    // Select nids already set for audit.
    $query = $conn_drupal8->query(
          "SELECT entity_id
            FROM node__field_next_audit_due
            WHERE field_next_audit_due_value is not null"
      );
    $already_set_results = $query->fetchAll();
    $already_set = [];
    foreach ($already_set_results as $thisresult) {
      $already_set[] = $thisresult->entity_id;
    }

    // Make sure audit update queue exists (there is no harm in
    // trying to recreate an existing queue).
    $this->queueFactory->get('audit_date_updates')->createQueue();
    $queue = $this->queueFactory->get('audit_date_updates');

    // Update the 'next audit due' node in D8.
    $n = $this->updateNodeAudit($flag_results, $already_set, $queue);

    $this->getIo()->info(
          'Queued audit date updates on ' . $n . ' nodes.'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function updateNodeAudit($flag_results, $already_set, $queue) {
    // Add these nids to the queue so that the 'audit due' date will
    // be set later by the cron task 'nidirect_common_cron'.
    $today = date('Y-m-d', \Drupal::time()->getCurrentTime());
    $nids = [];
    $n = 0;
    foreach ($flag_results as $i => $row) {
      // Don't bother to update nodes that have already been updated.
      if (!in_array($row->entity_id, $already_set)) {
        $nids[] = $row->entity_id;
        $n++;
        if ($n > 199) {
          // Add the nids to the queue in batches of 200.
          $this->addToQueue($nids, $queue);
          $n = 0;
          $nids = [];
        }
      }
    }
    if ($n > 0) {
      $this->addToQueue($nids, $queue);
    }
    return $n;
  }

  /**
   * {@inheritdoc}
   */
  protected function addToQueue($nids, $queue) {
    // Add the nids to the queue in batches of 200.
    $item = new \stdClass();
    $item->nids = implode(',', $nids);
    $queue->createItem($item);
  }

}
