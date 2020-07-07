<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NidirectMigratePostNodeLanguageCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostNodeLanguageCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:language')
      ->setDescription($this->trans('commands.nidirect.migrate.post.language.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $file_path = drupal_get_path('module', 'migrate_nidirect_utils') . '/data/node.langcodes.yml';
    $file_contents = file_get_contents($file_path);
    $ymldata = Yaml::parse($file_contents);
    var_dump($ymldata);
  }

}
