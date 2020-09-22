<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// @codingStandardsIgnoreEnd
/**
 * Recreates known feature and featured_content_list nodes after migration.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostFeatureNodesCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:feature_nodes')
      ->setDescription("Post migration: Recreates feature + featured_content_list nodes after migration.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->task_create_feature_nodes();
    $this->task_create_feature_content_list_nodes();

    $this->getIo()->info('DONE!');
  }

  /**
   * Re-create feature nodes from defined content.
   */
  // phpcs:disable
  protected function task_create_feature_nodes() {
  // phpcs:enable

  }

  /**
   * Re-create featured content list nodes from defined content.
   */
  // phpcs:disable
  protected function task_create_feature_content_list_nodes() {
    // phpcs:enable

  }

}
