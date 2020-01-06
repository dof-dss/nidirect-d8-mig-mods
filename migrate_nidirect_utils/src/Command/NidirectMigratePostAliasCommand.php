<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NidirectMigratePostAliasCommand.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostAliasCommand extends ContainerAwareCommand {

  /**
   * Path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * Class constructor.
   */
  public function __construct(AliasManagerInterface $path_alias_manager) {
    $this->pathAliasManager = $path_alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('nidirect:migrate:post:alias')->setDescription(
      $this->trans('commands.nidirect.migrate.post.alias.description')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $conn_migrate = Database::getConnection('default', 'migrate');
    $conn_drupal8 = Database::getConnection('default', 'default');
    $this->getIo()->info('Started post migration alias processing.');

    // Verify Drupal 7 flag table exists.
    if (!$conn_migrate->schema()->tableExists('url_alias')) {
      return 3;
    }

    // Select aliases of interest from D8.
    $query = $conn_drupal8->query(
      "select path, alias from {path_alias} where alias like '%-to-%'  and alias not like '/articles%'"
    );
    $alias_list = $query->fetchAll();
    foreach ($alias_list as $thisalias) {
      $this_path = $thisresult->path;
      $full_alias = $thisresult->alias;
      // Derive shortened alias (with keyword removed).
      $short_alias = preg_replace('/-to-/', '-', $full_alias);
      $this->getIo()->info(
        'Alias before - ' . $full_alias
      );
      $this->getIo()->info(
        'Alias after - ' . $short_alias
      );

      // See if shortened alias existed on D7 for this item.
      $query = $conn_migrate->query(
        "select * from {url_alias} where alias = @short_alias and source = @this_path",
        ['@short_alias' => $short_alias, '@this_path' => $this_path] );
      $d7_check = $query->fetchAll();
      if (count($d7_check) == 0) {
        // Shortened alias did not exist on D7, no further action required.
        continue;
      }

      // See if shortened alias already exists on D8 for this item.
      $query = $conn_drupal8->query(
        "select * from {path_alias} where alias = @short_alias and path = @this_path",
        ['@short_alias' => $short_alias, '@this_path' => $this_path] );
      $d8_check = $query->fetchAll();
      if (count($d8_check) > 0) {
        // Shortened alias already exists on D8, no further action required.
        continue;
      }

      // If we get to here then we need to create the shortened alias in D8.
      //$aliasManager = $this->container->get('path_alias.manager');
      //Need to use the DI service for this !!
      //\Drupal::service('path.alias_storage')->save($this_path, $short_alias);
      $this->pathAliasManager->save($this_path, $short_alias);

      break;
    }
  }

}
