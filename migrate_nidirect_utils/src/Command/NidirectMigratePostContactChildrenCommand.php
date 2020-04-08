<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\node\Entity\Node;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;

/**
 * Class NidirectMigratePostContactChildrenCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostContactChildrenCommand extends ContainerAwareCommand {
  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('nidirect:migrate:post:contact_children')
      ->setDescription($this->trans('commands.nidirect.migrate.post.contact_children.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('Setting up contact children');

    // Sort out 'Jobs & Benefits offices'.
    $node = Node::load(2739);


    $this->getIo()->info($this->trans('commands.nidirect.migrate.post.contact_children.messages.success'));
  }

}
