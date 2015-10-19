<?php

/**
 * @file
 * Contains Drupal\openid_connect\Plugin\Block\LoginBlock.
 */

namespace Drupal\openid_connect\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;

/**
 * Provides a 'OpenID Connect login' block.
 *
 * @Block(
 *  id = "openid_connect_login",
 *  admin_label = @Translation("OpenID Connect login"),
 * )
 */
class LoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $plugin_manager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        OpenIDConnectClientManager $plugin_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->plugin_manager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.openid_connect_client.processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\openid_connect\Form\LoginForm');
  }

}
