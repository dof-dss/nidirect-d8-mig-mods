<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate_drupal\Plugin\MigrateFieldPluginManagerInterface;
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
class NidirectMigratePostAuditCommand extends ContainerAwareCommand
{

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
    public function __construct(QueueFactory $queue_factory)
    {
        parent::__construct();
        $this->queueFactory = $queue_factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('nidirect:migrate:post:audit')->setDescription(
            $this->trans('commands.nidirect.migrate.post.audit.description')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn_migrate = Database::getConnection('default', 'migrate');
        $conn_drupal8 = Database::getConnection('default', 'default');
        $this->getIo()->info('Started post migration audit processing.');
        // Verify Drupal 7 flag table exists.
        if (!$conn_migrate->schema()->tableExists('flagging')) {
            return 3;
        }
        // Select content flagged with 'content_audit' from D7.
        $query = $conn_migrate->query(
            "
      SELECT 
        f.entity_id 
      FROM flagging f
      JOIN node n
      ON f.entity_id = n.nid
      WHERE n.type in ('article', 'contact', 'page')
      AND f.fid = 1
    "
        );
        $flag_results = $query->fetchAll();
        // Select nids already set for audit.
        $query = $conn_drupal8->query(
            "select entity_id 
            from node__field_next_audit_due 
            where field_next_audit_due_value is not null"
        );
        $already_set_results = $query->fetchAll();
        $already_set = [];
        foreach ($already_set_results as $thisresult) {
            $already_set[] = $thisresult->entity_id;
        }

        // Make sure audit update queue exists. There is no harm in
        // trying to recreate an existing queue.
        $this->queueFactory->get('audit_date_updates')->createQueue();
        $queue = $this->queueFactory->get('audit_date_updates');
        $this->getIo()->info(
            'After creation, ' .
            $queue->numberOfItems() . ' items in queue.'
        );

        // Update the 'next audit due' node in D8.
        $today = date('Y-m-d', \Drupal::time()->getCurrentTime());
        $nids = [];
        $n = 0;
        foreach ($flag_results as $i => $row) {
            if (!in_array($row->entity_id, $already_set)) {
                $nids[] = $row->entity_id;
                $n++;
                if ($n > 199) {
                    $this->updateNodeAudit($nids, $queue);
                    $n = 0;
                    $nids = [];
                }
            }
        }
        if ($n > 0) {
            $this->updateNodeAudit($nids, $queue);
        }
        $this->getIo()->info(
            'Items in queue ' .
            $queue->numberOfItems() . ' items.'
        );
        $this->getIo()->info(
            'Updated next audit date on ' .
            count($flag_results) . ' nodes.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function updateNodeAudit($nids, $queue)
    {
        $item = new \stdClass();
        $item->nids = implode(',', $nids);
        $queue->createItem($item);
    }

}
