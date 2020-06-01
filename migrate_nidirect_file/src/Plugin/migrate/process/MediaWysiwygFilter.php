<?php

namespace Drupal\migrate_nidirect_file\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Drupal\Core\Database\Connection;

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
<drupal-media
  data-align="center"
  data-entity-type="media"
  data-entity-uuid="2fdf6f0c-ac41-4d24-a491-06417a2a6c80"
  data-view-mode="landscape_float">
</drupal-media>
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
class MediaWysiwygFilter extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a UpdateFileToDocument process plugin instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
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

    $messenger = $this->messenger();
    $nid = $row->getSourceProperty('nid');
    $value['value'] = preg_replace_callback($pattern, function ($matches) use ($replacement_template, $messenger, $nid) {
      $decoder = new JsonDecode(TRUE);
      try {

        // Extract the D7 embedded media data.
        $tag_info = $decoder->decode($matches['tag_info'], JsonEncoder::FORMAT);

        $query = $this->connection->select('file_managed', 'f');
        $query->condition('f.fid', $tag_info['fid'], '=');
        $query->fields('f', ['uuid', 'filename', 'filemime', 'uri']);
        $query->range(0, 1);
        $file = $query->execute()->fetchAssoc();

        if (!empty($file)) {
          // Determine the media file type to handle.
          switch ($file['filemime']) {
            case 'image/png':
            case 'image/jpeg':
            case 'image/gif':
              return $this->imageMediaEmbed($tag_info);
            default:
              break;
          }
        }
      }
      catch (NotEncodableValueException $e) {
        // There was an error decoding the JSON. Remove code.
        $messenger->addWarning(sprintf('The following media_wysiwyg token in node %d does not have valid JSON: %s',
          $nid, $matches[0]));
        return NULL;
      }
      return NULL;
    }, $value['value']);

    return $value;
  }

  protected function genericMediaEmbed() {

    $replacement_template = <<<'TEMPLATE'
        <drupal-media
            data-align="center"
            data-entity-type="media"
            data-entity-uuid="%s">
        </drupal-media>
    TEMPLATE;


  }

  protected function imageMediaEmbed($tag_info) {

    $replacement_template = <<<'TEMPLATE'
        <drupal-media
            data-align="center"
            data-entity-type="media"
            data-entity-uuid="%s"
            data-view-mode="%s">
        </drupal-media>
       TEMPLATE;

    // Extract the base media entity uuid.
    $query = $this->connection->select('media', 'm');
    $query->fields('m', ['uuid']);
    $query->addField('i', 'entity_id');
    $query->addField('i', 'field_media_image_width', 'width');
    $query->addField('i', 'field_media_image_height', 'height');

    $query->join('media__field_media_image', 'i', 'i.entity_id = m.mid');
    $query->condition('i.field_media_image_target_id', $tag_info['fid'], '=');
    $query->range(0, 1);
    $media = $query->execute()->fetchAssoc();

    // Updated image formats when converting from D7 to D8 site.
    $style_map = [
      'landscape' => [
        'inline' => 'landscape_float',
        'inline-expandable' => 'landscape_float_xp',
        'inline_xl' => 'landscape_full_xp',
      ],
      'portrait' => [
        'inline' => 'portrait_float',
        'inline-expandable' => 'portrait_float_xp',
        'inline_xl' => 'portrait_full',
      ],
    ];

    // Select the appropriate display orientation based on the
    // image dimensions.
    $orientation = ($media['width'] > $media['height']) ? 'landscape' : 'portrait';

    // Assign the image style to the embedded image.
    if (array_key_exists($tag_info['attributes']['data-picture-mapping'], $style_map)) {
      $image_style = $style_map[$orientation][$tag_info['attributes']['data-picture-mapping']];
    } else {
      $image_style = $style_map[$orientation][array_key_first($style_map)];
    }

    // Update drupal-media template values.
    return sprintf($replacement_template, $media['uuid'], $image_style);
  }

}

