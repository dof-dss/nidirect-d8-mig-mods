<?php

namespace Drupal\migrate_nidirect_node_nidirect_contact\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;
use Drupal\migrate_nidirect_utils\TelephonePlusUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Prepares NIDirect Contact nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "nidirect_contact_node_source",
 * )
 */
class NIDirectContactNodeSource extends Node implements ContainerFactoryPluginInterface {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, LoggerChannelFactory $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_manager, $module_handler);
    $this->logger = $logger->get('NIDirectContactNodeSource');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $ids = $row->getSourceIdValues();
    $nid = $ids['nid'];

    // Check if we have a telephone lookup table entry for the node.
    $telephone = TelephonePlusUtils::lookup($nid);

    // If we don't have a lookup fetch the value for parsing.
    if (empty($telephone)) {
      $query = $this->getDatabase()->query('
        SELECT field_contact_phone_value
        FROM {field_data_field_contact_phone}
        WHERE entity_id = :nid', [
          ':nid' => $nid,
        ]
      );

      $contact_value = $query->fetchField();
      $telephone = TelephonePlusUtils::parse($contact_value);
    }

    // Fetch fax line number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_fax_value
      FROM {field_data_field_contact_fax}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $fax_value = $query->fetchField();
    $fax = TelephonePlusUtils::parse($fax_value);

    // Add the entry if we have at least one number.
    if (!empty($fax[0]['telephone_number'])) {
      // Ensure we always have a title for the entry.
      if (empty($fax[0]['telephone_title'])) {
        $fax[0]['telephone_title'] = 'Fax';
      }

      $telephone = array_merge($telephone, $fax);
    }

    // Fetch text/mobile phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_sms_value
      FROM {field_data_field_contact_sms}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $mobile_value = $query->fetchField();
    $mobile = TelephonePlusUtils::parse($mobile_value);

    // Add the entry if we have at least one number.
    if (!empty($mobile[0]['telephone_number'])) {
      // Ensure we always have a title for the entry.
      if (empty($mobile[0]['telephone_title'])) {
        $mobile[0]['telephone_title'] = 'Text number';
      }

      $telephone = array_merge($telephone, $mobile);
    }

    // Check if we have any data from the existing fields and determine if
    // we weren't able to process the numbers.
    if (strlen($contact_value . $fax_value . $mobile_value) > 0) {
      $telephone_processed = TRUE;
      foreach ($telephone as $entry) {
        if (empty($entry['telephone_number'])) {
          $telephone_processed = FALSE;
        }
      }

      // Log any nodes with blank telephone info.
      if (!$telephone_processed) {
        $this->logger->notice("Unable to process telephone details for NID: $nid");
      }
    }

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
