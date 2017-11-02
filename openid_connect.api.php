<?php

/**
 * @file
 * Documentation for OpenID Connect module APIs.
 */

/**
 * Modify the list of claims.
 *
 * @param array $claims
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_claims_alter(&$claims) {
  $claims['custom_claim'] = [
    'scope' => 'profile',
    'title' => 'Custom Claim',
    'type' => 'string',
    'description' => 'A custom claim from provider',
  ];
}

/**
 * Alter hook to alter OpenID Connect client plugins.
 *
 * @param array $client_info
 *   An array of client information.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_openid_connect_client_info_alter(&$client_info) {
  $client_info['generic'] = [
    'id' => 'generic',
    'label' => [
      'string' => 'Generic',
      'translatableMarkup' => null,
      'options' => [],
      'stringTranslation' => null,
      'arguments' => [],
    ],
    'class' => 'Drupal\openid_connect\Plugin\OpenIDConnectClient\Generic',
    'provider' => 'openid_connect',
  ];
}

/**
 * Alter hook to alter the user properties to be skipped for mapping.
 *
 * @param $properties_to_skip
 *   An array of of properties to skip.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_user_properties_to_skip(&$properties_to_skip) {
  // Allow to map the username to a property from the provider.
  unset($properties_to_skip['name']);
}

/**
 * Post authorize hook that runs after the user logged in via OpenID Connect.
 *
 * @param array $tokens
 *   An array of tokens.
 * @param \Drupal\user\UserInterface $account
 *   A user account object.
 * @param array $userinfo
 *   An array of user information.
 * @param string $plugin_id
 *   The plugin identifier.
 *
 * @ingroup openid_connect_api
 */
function hook_openid_connect_post_authorize($tokens, $account, $userinfo, $plugin_id) {}
