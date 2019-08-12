<?php

namespace Drupal\migrate_nidirect_global;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds to Migrate events to switch workflows
 * config on/off to avoid imported content being
 * unpublished by default.
 */
class WorkflowStateToggle implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MigrateEvents::PRE_IMPORT => 'preImport',
      MigrateEvents::POST_IMPORT => 'postImport',
    ];
  }

  /**
   * Turn off part of workflow state before importing new content.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function preImport(MigrateImportEvent $event) {
    $migration_config = $event->getMigration();

    if (!preg_match('/^node_/', $migration_config->id())) {
      // Stop early if it's not a node migration.
      return;
    }

    $config = \Drupal::service('config.factory')->getEditable('workflows.workflow.editorial');
    $config->set('type_settings.entity_types.node', []);
    $config->save();
  }

  /**
   * Turn on part of workflow state before importing new content.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function postImport(MigrateImportEvent $event) {
    $migration_config = $event->getMigration();

    if (!preg_match('/^node_/', $migration_config->id())) {
      // Stop early if it's not a node migration.
      return;
    }

    $config = \Drupal::service('config.factory')->getEditable('workflows.workflow.editorial');
    $config->set('type_settings.entity_types.node', [
      'application',
      'article',
      'contact',
      'driving_instructor',
      'embargoed_publication',
      'external_link',
      'gp_practice',
      'health_condition',
      'landing_page',
      'news',
      'page',
      'publication',
      'recipe',
      'umbrella_body',
    ]);
    $config->save();
  }

}
