<?php
namespace Drupal\migrate_nidirect_global\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Skip youtube videos.
 *
 * @MigrateProcessPlugin(
 *   id = "skip_youtube_files"
 * )
 */
class SkipYoutubeVideos extends ProcessPluginBase {

    /**
     * {@inheritdoc}
     */
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
        // Don't include videos if they are just references to remote Youtube videos.
        if ($row->getSourceProperty('type') == 'video') {
            //if (preg_match('/youtube/', $row->getSourceProperty('uri'))) {
                \Drupal::logger('migrate_nidirect_global')->error(t('Skipping video'));
                throw new MigrateSkipRowException();
            //}
        }
        return $value;
    }

}
