<?php

namespace Drupal\migrate_nidirect_file\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
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
    $messenger = $this->messenger();
    $nid = $row->getSourceProperty('nid');
    $value['value'] = preg_replace_callback($pattern, function ($matches) use ($messenger, $nid) {
      $decoder = new JsonDecode(TRUE);
      try {
        $tag_info = $decoder->decode($matches['tag_info'], JsonEncoder::FORMAT);

        // Perform lookup for managed files matching the D7 fid.
        $database = \Drupal::database();
        $query = $database->select('file_managed', 'f');

        $query->condition('f.fid', $tag_info['fid'], '=');
        $query->fields('f', ['filename', 'filemime', 'uri']);
        $query->range(0, 1);
        $file = $query->execute()->fetchAssoc();
        
      }
      catch (NotEncodableValueException $e) {
        $messenger->addWarning('Unable to extract JSON');
        return '';
      }
    }, $value['value']);

    return $value;
  }

}
