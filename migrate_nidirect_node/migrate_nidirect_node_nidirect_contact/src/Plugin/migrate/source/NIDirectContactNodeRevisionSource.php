<?php

namespace Drupal\migrate_nidirect_node_nidirect_contact\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
 *   source_module = "node"
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

      $telephone = [];

      $query = $this->getDatabase()->query('
        SELECT field_contact_phone_value
        FROM {field_revision_field_contact_phone}
        WHERE revision_id = :vid', [
          ':vid' => $vid,
        ]
      );

      $contact_value = $query->fetchField();

      if (!empty($contact_value)) {
        $contact_telephone = TelephonePlusUtils::parse($contact_value);

        if ($contact_telephone === FALSE) {
          $this->logger->notice('Unable to process telephone data for nid: ' . $nid);
        }
        else {
          $telephone = $contact_telephone;
        }
      }
    }

    // Fetch fax line number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_fax_value
      FROM {field_revision_field_contact_fax}
      WHERE revision_id = :vid', [
        ':vid' => $vid,
      ]
    );

    $fax_value = $query->fetchField();

    if (!empty($fax_value)) {
      $fax = TelephonePlusUtils::parse($fax_value);

      if ($fax === FALSE) {
        $this->logger->notice('Unable to process fax data for nid: ' . $nid);
      }
      else {
        // Add the entry if we have at least one number.
        if (!empty($fax[0]['telephone_number'])) {
          // Ensure we always have a title for the entry.
          if (empty($fax[0]['telephone_title'])) {
            $fax[0]['telephone_title'] = 'Fax';
          }
          $telephone = array_merge($telephone, $fax);
        }
      }
    }

    // Fetch text/mobile phone number.
    $query = $this->getDatabase()->query('
      SELECT field_contact_sms_value
      FROM {field_revision_field_contact_sms}
      WHERE revision_id = :vid', [
        ':vid' => $vid,
      ]
    );

    $mobile_value = $query->fetchField();

    if (!empty($mobile_value)) {
      $mobile = TelephonePlusUtils::parse($mobile_value);

      if ($mobile === FALSE) {
        $this->logger->notice('Unable to process fax data for nid: ' . $nid);
      }
      else {
        // Add the entry if we have at least one number.
        if (!empty($mobile[0]['telephone_number'])) {
          // Ensure we always have a title for the entry.
          if (empty($mobile[0]['telephone_title'])) {
            $mobile[0]['telephone_title'] = 'Text number';
          }
          $telephone = array_merge($telephone, $mobile);
        }
      }
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
        $this->logger->notice("Unable to process any telephone details for NID: $nid");
      }
    }

    $row->setSourceProperty('telephone_number', $telephone);
    return parent::prepareRow($row);

  }

}
