<?php

namespace Drupal\migrate_nidirect_utils;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Base class for running migration tasks.
 */
class MigrateCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $class_methods = get_class_methods($this);
    $migrate_tasks = [];

    $migrate_tasks = array_filter($class_methods, function ($key) {
      return substr($key, 0, 5) === "task_";
    });

    if (($total_tasks = count($migrate_tasks)) > 1) {
      $this->getIo()->info("Executing $total_tasks task(s).");
      $progressBar = new ProgressBar($output, $total_tasks);
      $progressBar->start();

      foreach ($migrate_tasks as $task) {
        $progressBar->advance();
        $this->getIo()->info($this->prettyFunctionName($task));
        call_user_func([$this, $task]);
      }

      $this->getIo()->info('🏁 Finished!');
    }
    else {
      $this->getIo()->info('No tasks found.');
    }
  }

  /**
   * Makes function names more human readable.
   *
   * @param string $name
   *   Function name.
   * @return string
   *   Prettified function name.
   */
  private function prettyFunctionName($name) {
    return ucfirst(substr(str_replace('_', ' ', $name), 5));
  }

}
