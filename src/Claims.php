<?php

/**
 * @file
 * Contains Drupal\openid_connect\Claims.
 */

namespace Drupal\openid_connect;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Claims.
 *
 * @package Drupal\openid_connect
 */
class Claims implements ContainerInjectionInterface {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The standard claims.
   *
   * @var array
   */
  protected $claims = array(
    'name' => array(
      'scope' => 'profile',
    ),
    'family_name' => array(
      'scope' => 'profile',
    ),
    'given_name' => array(
      'scope' => 'profile',
    ),
    'middle_name' => array(
      'scope' => 'profile',
    ),
    'nickname' => array(
      'scope' => 'profile',
    ),
    'preferred_username' => array(
      'scope' => 'profile',
    ),
    'profile' => array(
      'scope' => 'profile',
    ),
    'picture' => array(
      'scope' => 'profile',
    ),
    'website' => array(
      'scope' => 'profile',
    ),
    'gender' => array(
      'scope' => 'profile',
    ),
    'birthdate' => array(
      'scope' => 'profile',
    ),
    'zoneinfo' => array(
      'scope' => 'profile',
    ),
    'locale' => array(
      'scope' => 'profile',
    ),
    'updated_at' => array(
      'scope' => 'profile',
    ),
    'email' => array(
      'scope' => 'email',
    ),
    'email_verified' => array(
      'scope' => 'email',
    ),
    'address' => array(
      'scope' => 'address',
    ),
    'phone_number' => array(
      'scope' => 'phone',
    ),
    'phone_number_verified' => array(
      'scope' => 'phone',
    ),
  );

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactory $config_factory,
    ModuleHandler $module_handler
  ) {

    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * Returns OpenID Connect claims.
   *
   * Allows them to be extended via an alter hook.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
   * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
   *
   * @return array
   *   List of claims
   */
  public function getClaims() {
    $claims = $this->claims;
    $this->moduleHandler->alter('openid_connect_claims', $claims);
    return $claims;
  }

  /**
   * Returns OpenID Connect standard Claims as a Form API options array.
   *
   * @return array
   *   List of claims as options
   */
  public function getOptions() {
    $options = array();
    foreach ($this->getClaims() as $claim_name => $claim) {
      $options[$claim['scope']][$claim_name] = $claim_name;
    }
    return $options;
  }

  /**
   * Returns scopes that have to be requested based on the configured claims.
   *
   * @see http://openid.net/specs/openid-connect-core-1_0.html#ScopeClaims
   *
   * @return string
   *   Space delimited case sensitive list of ASCII scope values.
   */
  public function getScopes() {
    $claims = $this->configFactory
      ->getEditable('openid_connect.settings')
      ->get('userinfo_mappings');

    $scopes = array('openid', 'email');
    $claims_info = Claims::getClaims();
    foreach ($claims as $claim) {
      if (isset($claims_info[$claim]) && !isset($scopes[$claims_info[$claim]['scope']]) && $claim != 'email') {
        $scopes[$claims_info[$claim]['scope']] = $claims_info[$claim]['scope'];
      }
    }
    return implode(' ', $scopes);
  }

}
