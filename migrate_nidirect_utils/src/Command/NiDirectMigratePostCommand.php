<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class NiDirectMigratePostCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NiDirectMigratePostCommand extends ContainerAwareCommand {

  /**
   * Array of post migration commands to run.
   *
   * @var array
   */
  protected $commands = [
    'nidirect:migrate:post:flag',
    'nidirect:migrate:post:metatag',
    'nidirect:migrate:post:taxonomy',
    'nidirect:migrate:post:article',
    'nidirect:migrate:post:publish_status',
    'nidirect:migrate:post:audit',
  ];

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('nidirect:migrate:post')
      ->setDescription('Executes post migration commands.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $issues = [];
    $this->getIo()->info('Attempting to execute ' . count($this->commands) . ' post migration commands.');

    foreach ($this->commands as $command) {
      $cmd = $this->getApplication()->find($command);
      $args = ['command' => $command];
      $result = $cmd->run(new ArrayInput($args), $output);
      if ($result <> 0) {
        $issues[] = $command . ' : ' . $result;
      }
    }

    if (count($issues) > 0) {
      $this->getIo()->caution($this->trans('commands.nidirect.migrate.post.messages.failure'));
      foreach ($issues as $issue) {
        $this->getIo()->caution($issue);
      }
    }
    else {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.messages.success'));
    }
  }

}
