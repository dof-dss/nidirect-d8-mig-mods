<?php

namespace Drupal\migrate_nidirect_node_nidirect_contact\Plugin\migrate\source;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Plugin\migrate\source\d7\NodeRevision;
use Drupal\migrate\Row;
use Drupal\migrate_nidirect_utils\TelephonePlusUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Prepares NIDirect Contact nodes for telephone plus field.
 *
 * @MigrateSource(
 *   id = "nidirect_contact_node_revision_source",
 * )
 */
class NIDirectContactNodeRevisionSource extends NodeRevision implements ContainerFactoryPluginInterface {

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
    $vid = $ids['vid'];

    // Importing each revision value would be too time consuming.
    // In this case we are going to use the current node value for
    // the revision value.
    $query = $this->getDatabase()->query('
        SELECT nid 
        FROM {node_revision} 
        WHERE vid = :vid', [
        ':vid' => $vid,
      ]
    );

    $nid = $query->fetchField();

    // Check if we have a telephone lookup table entry for the node.
    $telephone = TelephonePlusUtils::lookup($nid);

    // If we don't have a lookup fetch the value for parsing.
    if (empty($telephone)) {
      $query = $this->getDatabase()->query('
        SELECT field_contact_phone_value 
        FROM {field_revision_field_contact_phone} 
        WHERE revision_id = :vid', [
          ':vid' => $vid,
        ]
      );

      $contact_details = $query->fetchField();

      $telephone = TelephonePlusUtils::parse($contact_details);

      // Fetch fax line number.
      $query = $this->getDatabase()->query('
      SELECT field_contact_fax_value
      FROM {field_revision_field_contact_fax}
      WHERE revision_id = :vid', [
          ':vid' => $vid,
        ]
      );

      $fax = $query->fetchField();

      if (!empty($fax)) {
        $telephone[] = [
          'telephone_title' => 'Fax',
          'telephone_number' => $fax,
          'telephone_extension' => '',
          'telephone_supplementary' => '',
          'country_code' => 'GB',
          'display_international_number' => '0',
        ];
      }

      // Fetch text/mobile phone number.
      $query = $this->getDatabase()->query('
      SELECT field_contact_sms_value
      FROM {field_revision_field_contact_sms}
      WHERE revision_id = :vid', [
          ':vid' => $vid,
        ]
      );

      $mobile = $query->fetchField();

      if (!empty($mobile)) {
        $telephone[] = [
          'telephone_title' => 'Text',
          'telephone_number' => $mobile,
          'telephone_extension' => '',
          'telephone_supplementary' => '',
          'country_code' => 'GB',
          'display_international_number' => '0',
        ];
      }
    }

    // Check if we have a node with no replacement telephone details.
    $empty_telephone = TRUE;
    foreach ($telephone as $entry) {
      if (!empty($entry['telephone_number'])) {
        $empty_telephone = FALSE;
      }
    }

    // Log any nodes with blank telephone info.
    if ($empty_telephone) {
      $this->logger->notice("Blank telephone details for NID: $nid and VID: $vid");
    }

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);
  }

}
