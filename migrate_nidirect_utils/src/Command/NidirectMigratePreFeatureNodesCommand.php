<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// @codingStandardsIgnoreEnd
/**
 * Cleanup featured content.
 *
 * Removes existing feature + featured_content_list nodes ahead of
 * content import.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePreFeatureNodesCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:pre:feature_nodes')
      ->setDescription("Pre migration setup: removes feature and FCL nodes");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('Removing existing feature and featured_content_list nodes before migration.');
    $this->task_remove_feature_fcl_nodes();
    $this->getIo()->info('DONE!');
  }

  /**
   * Purge the D8 site of all feature and featured content list nodes.
   */
  // phpcs:disable
  protected function task_remove_feature_fcl_nodes() {
  // phpcs:enable
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    foreach (['featured_content_list', 'feature'] as $type) {
      $entities = $storage->loadByProperties(['type' => $type]);
      $storage->delete($entities);
    }
  }

}
