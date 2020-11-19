<?php

namespace Drupal\migrate_nidirect_utils\Command;

use Drupal\Console\Core\Command\ContainerAwareCommand;
// @codingStandardsIgnoreStart
use Drupal\Console\Annotations\DrupalCommand;
use Drupal\node\Entity\Node;
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
   * A collection of featured content data.
   *
   * @var array
   */
  protected $featureContent = [];

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
    $this->featureContent[] = [
      'title' => 'Wear a face covering to help reduce spread of COVID-19',
      'teaser' => 'Wear a face covering to help reduce spread of COVID-19 - they are now mandatory in certain indoor settings',
      'uri' => 'internal:/node/13662',
      'media_id' => 8939,
    ];
    $this->featureContent[] = [
      'title' => 'Coronavirus (COVID-19)',
      'teaser' => 'Updates and advice about coronavirus (COVID-19), including information about government services',
      'uri' => 'internal:/node/13394',
      'media_id' => 8786,
    ];
    $this->featureContent[] = [
      'title' => 'Universal Credit',
      'teaser' => 'Find out all you need to need to know to make a Universal Credit claim',
      'uri' => 'internal:/node/12849',
      'media_id' => 7283,
    ];

    foreach ($this->featureContent as &$feature) {
      $node = Node::create([
        'type' => 'feature',
        'langcode' => 'en',
        'moderation_state' => 'published',
        'status' => 1,
        'uid' => 1,
        'title' => $feature['title'],
        'field_teaser' => $feature['teaser'],
        'field_link_url' => [
          'uri' => $feature['uri'],
        ],
        'field_photo' => [
          'target_id' => $feature['media_id'],
        ],
      ]);
      $node->save();
      $feature['nid'] = $node->id();

      $this->getIo()->info("Created feature node with title '" . $feature['title'] . "'");
    }
  }

  /**
   * Re-create featured content list nodes from defined content.
   */
  // phpcs:disable
  protected function task_create_feature_content_list_nodes() {
  // phpcs:enable
    $fcl_content[] = [
      'title' => 'News: featured content',
      'features' => [
        7366,
        7479,
      ],
      'tag' => 1344,
    ];
    $fcl_content[] = [
      'title' => 'Homepage: featured content',
      'features' => [
        ['target_id' => $this->getFeatureByTitle('Wear a face covering to help reduce spread of COVID-19')],
        ['target_id' => $this->getFeatureByTitle('Universal Credit')],
        ['target_id' => $this->getFeatureByTitle('Coronavirus (COVID-19)')],
      ],
      'tag' => 1338,
    ];

    foreach ($fcl_content as $fcl) {
      $node = Node::create([
        'type' => 'featured_content_list',
        'langcode' => 'en',
        'moderation_state' => 'published',
        'status' => 1,
        'uid' => 1,
        'title' => $fcl['title'],
        'field_featured_content' => $fcl['features'],
        'field_tags' => $fcl['tag'],
      ]);

      $node->save();
      $this->getIo()->info("Created featured content list node with title '" . $fcl['title'] . "'");
    }
  }

  /**
   * Fetches the node id of a feature node from a given title.
   *
   * @param string $title
   *   Feature node title.
   *
   * @return int
   *   The node id.
   */
  private function getFeatureByTitle(string $title) {
    foreach ($this->featureContent as $feature) {
      if ($title === $feature['title']) {
        return (int) $feature['nid'];
      }
    }

    return 0;
  }

}
