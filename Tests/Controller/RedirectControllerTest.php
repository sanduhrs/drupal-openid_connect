<?php

/**
 * @file
 * Contains Drupal\openid_connect\Tests\RedirectController.
 */

namespace Drupal\openid_connect\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;

/**
 * Provides automated tests for the openid_connect module.
 */
class RedirectControllerTest extends WebTestBase {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $plugin_manager_openid_connect_client_processor;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "openid_connect RedirectController's controller functionality",
      'description' => 'Test Unit for module openid_connect and controller RedirectController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests openid_connect functionality.
   */
  public function testRedirectController() {
    // Check that the basic functions of module openid_connect.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
