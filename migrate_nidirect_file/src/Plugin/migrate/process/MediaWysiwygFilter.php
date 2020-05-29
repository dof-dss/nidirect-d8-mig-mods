<?php

namespace Drupal\migrate_nidirect_file\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Processes [[{"type":"media","fid":"1234",...}]] tokens in content.
 *
 * These style tokens come from media_wysiwyg module. The regex it uses to match
 * them for reference is:
 *
 * /\[\[.+?"type":"media".+?\]\]/s
 *
 * @code
 * # From this
 * [[{"type":"media","fid":"1234",...}]]
 *
 * # To this
 * <drupal-entity
 *   data-embed-button="media"
 *   data-entity-embed-display="view_mode:media.full"
 *   data-entity-type="media"
 *   data-entity-id="1234"></drupal-entity>
 * @endcode
 *
 * Usage:
 *
 * @endcode
 * process:
 *   bar:
 *     plugin: media_wysiwyg_filter
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "media_wysiwyg_filter"
 * )
 */
class MediaWysiwygFilter extends ProcessPluginBase {

  /**
   * The block_content entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dbConnection;

  /**
   * Constructs a BlockPluginId object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The block content storage object.
   * @param \Drupal\migrate\MigrateLookupInterface $migrate_lookup
   *   The migrate lookup service.
   */
// @codingStandardsIgnoreLine
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, $db_connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dbConnection = $db_connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $pattern = '/\[\[(?<tag_info>.+?"type":"media".+?)\]\]/s';
    $replacement_template = <<<'TEMPLATE'
<drupal-media
    data-align="center"
    data-entity-type="media"
    data-entity-uuid="%s"
    data-view-mode="%s">
</drupal-media>
TEMPLATE;
    $messenger = $this->messenger();
    $nid = $row->getSourceProperty('nid');
    $value['value'] = preg_replace_callback($pattern, function ($matches) use ($replacement_template, $messenger, $nid) {
      $decoder = new JsonDecode(TRUE);
      try {
        $tag_info = $decoder->decode($matches['tag_info'], JsonEncoder::FORMAT);
        $database = \Drupal::database();
        $query = $database->select('file_managed', 'f');

        $query->condition('f.fid', $tag_info['fid'], '=');
        $query->fields('f', ['uuid', 'filename', 'filemime', 'uri']);
        $query->range(0, 1);
        $file = $query->execute()->fetchAssoc();

        if ($media_table == 'media__field_media_') {
          return;
        }

        if (!empty($file)) {
          // Media table name prefix.
          $media_table = 'media__field_media_';

          // Determine the media file type to handle.
          switch ($file['filemime']) {
            case 'image/png' :
            case 'image/jpeg' :
            case 'image/gif' :
              $media_table .= 'image';
              $field_target_id = 'i.field_media_image_target_id';
              break;
            default:
              break;
          }

          if ($media_table == 'media__field_media_') {
            return;
          }

          // Extract the base media entity uuid.
          $query = \Drupal::database()->select('media', 'm');
          $query->fields('m', ['uuid']);
          $query->addField('i', 'entity_id');
          $query->join($media_table, 'i', 'i.entity_id = m.mid');
          $query->condition($field_target_id, $tag_info['fid'], '=');
          $query->range(0, 1);
          $media = $query->execute()->fetchAssoc();

          $style_map = [
            'inline' => 'landscape_float',
            'inline-expandable' => 'landscape_float_xp',
            'inline_xl' => 'landscape_full',
            'inline_xl_expandable' => 'landscape_full_xp',
          ];

          if (array_key_exists($tag_info['attributes']['data-picture-mapping'], $style_map)) {
            $image_style = $style_map[$tag_info['attributes']['data-picture-mapping']] ;
          } else {
            $image_style = 'landscape_float';
          }

          return sprintf($replacement_template, $media['uuid'], $image_style);
        }


      }
      catch (NotEncodableValueException $e) {
        // There was an error decoding the JSON. Remove code.
        $messenger->addWarning(sprintf('The following media_wysiwyg token in node %d does not have valid JSON: %s',
          $nid, $matches[0]));
        return '';
      }
    }, $value['value']);

    return $value;
  }

}
