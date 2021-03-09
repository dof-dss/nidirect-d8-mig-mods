<?php

namespace Drupal\migrate_nidirect_node\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContentLinkAliasesFilter' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "content_link_aliases_filter"
 * )
 */
class ContentLinkAliasesFilter extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;


  /**
   * Class constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\path_alias\AliasManager $alias_manager
   *   Path alias manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    preg_match_all('/href="(\/node\/\d+)"/m', $value['value'], $node_urls, PREG_SET_ORDER, 0);

    foreach ($node_urls as $url) {
      $alias = $this->aliasManager->getAliasByPath($url[1]);
      if (!empty($alias)) {
        $value['value'] = str_replace($url[1], $alias, $value['value']);
      }
    }

    return $value;
  }

}
