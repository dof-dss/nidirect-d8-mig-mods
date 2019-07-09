<?php

namespace Drupal\migrate_nidirect_file\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
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
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $pattern = '/\[\[(?<tag_info>.+?"type":"media".+?)\]\]/s';
    $replacement_template = <<<'TEMPLATE'
<drupal-entity
  data-embed-button="media"
  data-entity-embed-display="view_mode:media.%s"
  data-entity-type="media"
  data-entity-id="%s"></drupal-entity>
TEMPLATE;
    $messenger = $this->messenger();
    $nid = $row->getSourceProperty('nid');
    $value['value'] = preg_replace_callback($pattern, function ($matches) use ($replacement_template, $messenger, $nid) {
      $decoder = new JsonDecode(TRUE);
      try {
        $tag_info = $decoder->decode($matches['tag_info'], JsonEncoder::FORMAT);
        return sprintf($replacement_template, $tag_info['view_mode'], $tag_info['fid']);
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
