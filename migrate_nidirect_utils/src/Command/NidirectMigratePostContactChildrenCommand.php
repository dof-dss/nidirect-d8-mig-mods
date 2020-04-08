<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Generator\GeneratorInterface;

/**
 * Class NidirectMigratePostContactChildrenCommand.
 *
 * Drupal\Console\Annotations\DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostContactChildrenCommand extends Command {

  /**
   * Drupal\Console\Core\Generator\GeneratorInterface definition.
   *
   * @var \Drupal\Console\Core\Generator\GeneratorInterface
   */
  protected $generator;


  /**
   * Constructs a new NidirectMigratePostContactChildrenCommand object.
   */
  public function __construct(GeneratorInterface $migrate_nidirect_utils_nidirect_migrate_post_contact_children_generator) {
    $this->generator = $migrate_nidirect_utils_nidirect_migrate_post_contact_children_generator;
    parent::__construct();
  }

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
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);
    $this->getIo()->info('initialize');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->getIo()->info('execute');
    $this->getIo()->info($this->trans('commands.nidirect.migrate.post.contact_children.messages.success'));
    $this->generator->generate([]);
  }

}
