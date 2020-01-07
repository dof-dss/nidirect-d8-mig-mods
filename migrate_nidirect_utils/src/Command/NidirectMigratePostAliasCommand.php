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
    $strings_removed = ['a', 'an', 'as', 'at', 'before', 'but', 'for', 'from', 'is',
      'in', 'into', 'like', 'of', 'off', 'on', 'onto', 'per', 'since', 'than', 'the',
      'this', 'that', 'to', 'up', 'via', 'with'];
    foreach ($alias_list as $thisalias) {
      $this_path = $thisalias->path;
      $this_path_d7 = substr($this_path, 1);
      $full_alias = $thisalias->alias;
      // Derive shortened alias (with keywords removed).
      $short_alias = $full_alias;
      foreach ($strings_removed as $keyword) {
        $short_alias = preg_replace('/-' . $keyword . '-/', '-', $short_alias);
      }
      if ($short_alias == $full_alias) {
        continue;
      }
      $short_alias_d7 = substr($short_alias, 1);
      $this->getIo()->info(
        'Looking for alias on D7 - ' . $short_alias_d7 . ', and source - ' . $this_path_d7
      );

      // See if shortened alias existed on D7 for this item.
      $query = $conn_migrate->query(
        "select * from {url_alias} where alias = :short_alias and source = :this_path",
        [':short_alias' => $short_alias_d7, ':this_path' => $this_path_d7]);
      $d7_check = $query->fetchAll();
      if (count($d7_check) == 0) {
        // Shortened alias did not exist on D7, no further action required.
        $this->getIo()->info(
          'does not exist on D7'
        );
        continue;
      }

      // See if shortened alias already exists on D8 for this item.
      $query = $conn_drupal8->query(
        "select * from {path_alias} where alias = :short_alias and path = :this_path",
        [':short_alias' => $short_alias, ':this_path' => $this_path]);
      $d8_check = $query->fetchAll();
      if (count($d8_check) > 0) {
        // Shortened alias already exists on D8, no further action required.
        $this->getIo()->info(
          'already exists on D8'
        );
        continue;
      }

      // If we get to here then we need to create the shortened alias in D8.
      //$aliasManager = $this->container->get('path_alias.manager');
      //Need to use the DI service for this !!
      \Drupal::service('path.alias_storage')->save($this_path, $short_alias);
      $this->getIo()->info(
        '** Creating alias - ' . $short_alias
      );
      //$this->pathAliasManager->save($this_path, $short_alias);
    }
  }

}
