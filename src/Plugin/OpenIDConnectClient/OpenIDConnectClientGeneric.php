<?php

/**
 * @file
 * Contains \Drupal\openid_connect\Plugin\OpenIDConnectClient\OpenIDConnectClientGeneric.
 */

namespace Drupal\openid_connect\Plugin\OpenIDConnectClient;

use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;

/**
 * Generic OpenID Connect client.
 *
 * Used primarily to login to Drupal sites powered by oauth2_server or PHP
 * sites powered by oauth2-server-php.
 *
 * @OpenIDConnectClient(
 *   id = "generic",
 *   label = @Translation("Generic OpenID Connect client")
 * )
 */
class OpenIDConnectClientGeneric extends OpenIDConnectClientBase {

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   */
  public function getEndpoints() {
    //FIXME: The config should be available in the object
    $client_config = \Drupal::config('openid_connect.settings.' . $this->pluginId)->get('settings');

    return array(
      'authorization' => $client_config['authorization_endpoint'],
      'token' => $client_config['token_endpoint'],
      'userinfo' => $client_config['userinfo_endpoint'],
    );
  }

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $default_site = 'https://example.com/oauth2';
    $form['authorization_endpoint'] = array(
      '#title' => t('Authorization endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('authorization_endpoint', $default_site . '/authorize'),
    );
    $form['token_endpoint'] = array(
      '#title' => t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('token_endpoint', $default_site . '/token'),
    );
    $form['userinfo_endpoint'] = array(
      '#title' => t('UserInfo endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('userinfo_endpoint', $default_site . '/UserInfo'),
    );

    return $form;
  }
}