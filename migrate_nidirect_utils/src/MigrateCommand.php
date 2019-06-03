<?php

namespace Drupal\migrate_nidirect_utils;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;

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

    foreach ($migrate_tasks as $task) {
      call_user_func([$this, $task]);
    }
  }

}
