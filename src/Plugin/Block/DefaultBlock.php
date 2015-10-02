<?php

/**
 * @file
 * Contains Drupal\openid_connect\Plugin\Block\DefaultBlock.
 */

namespace Drupal\openid_connect\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'DefaultBlock' block.
 *
 * @Block(
 *  id = "default_block",
 *  admin_label = @Translation("Default block"),
 * )
 */
class DefaultBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['default_block']['#markup'] = 'Implement DefaultBlock.';

    $manager = \Drupal::service('plugin.manager.openid_connect_client.processor');
    $plugin_definitions = $manager->getDefinitions();
    //dpm($plugin_definitions, 'Plugin Definitions');

    $plugin_definition = $manager->getDefinition('generic');
    //dsm($plugin_definition);

    $plugin = $manager->createInstance('generic');
    dsm($plugin);

    return $build;
  }

}
