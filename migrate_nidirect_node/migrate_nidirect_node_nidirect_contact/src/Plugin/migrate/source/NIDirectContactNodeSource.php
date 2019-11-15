<?php

namespace Drupal\migrate_nidirect_node_nidirect_contact\Plugin\migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, LoggerChannelFactory $logger) {
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

      $contact_details = $query->fetchField();

      $telephone = TelephonePlusUtils::parse($contact_details);
    }

    // Fetch fax line number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_fax_value
      FROM {field_data_field_contact_fax}
      WHERE entity_id = :nid', [
        ':nid' => $nid,
      ]
    );

    $fax = TelephonePlusUtils::parse($query->fetchField());

    if (!empty($fax)) {
      $telephone[] = $fax;
    }

    // Log any nodes with blank telephone info.
    if ($empty_telephone) {
      $this->logger->notice("Blank telephone details for NID: $nid");
    }

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
