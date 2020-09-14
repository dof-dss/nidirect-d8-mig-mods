<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Core\Database\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
// @codingStandardsIgnoreEnd
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Post process migrated node language data.
 *
 * @DrupalCommand (
 *     extension="migrate_nidirect_utils",
 *     extensionType="module"
 * )
 */
class NidirectMigratePostNodeLanguageCommand extends ContainerAwareCommand {

  /**
   * Drupal 8 database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnDrupal8;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
    parent::__construct();
  }

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

    if (empty($file_contents)) {
      $this->getIo()->info($this->trans('commands.nidirect.migrate.post.language.messages.emptyFile'));
      return -1;
    }

    try {
      $language_data = Yaml::parse($file_contents);
    }
    catch (ParseException $exception) {
      $this->getIo()->info('Unable to parse the YAML string: %s', $exception->getMessage());
      return -1;
    }

    $updated = 0;

    // Iterate each language code and update the node and revision langcode.
    foreach ($language_data as $langcode => $nids) {
      $updated += $this->dbConnDrupal8->update('node')
        ->fields(['langcode' => $langcode])
        ->condition('nid', $nids, 'IN')
        ->execute();

      $updated += $this->dbConnDrupal8->update('node_revision')
        ->fields(['langcode' => $langcode])
        ->condition('nid', $nids, 'IN')
        ->execute();
    }

    $this->getIo()->info('Updated ' . $updated . ' node language values.');
  }

}
