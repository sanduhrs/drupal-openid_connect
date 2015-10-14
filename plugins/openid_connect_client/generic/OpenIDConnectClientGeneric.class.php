<?php

/**
 * @file
 * Generic OpenID Connect client.
 *
 * Used primarily to login to Drupal sites powered by oauth2_server or PHP
 * sites powered by oauth2-server-php.
 */

class OpenIDConnectClientGeneric extends OpenIDConnectClientBase {

  /**
   * {@inheritdoc}
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

  /**
   * {@inheritdoc}
   */
  public function getEndpoints() {
    return array(
      'authorization' => $this->getSetting('authorization_endpoint'),
      'token' => $this->getSetting('token_endpoint'),
      'userinfo' => $this->getSetting('userinfo_endpoint'),
    );
  }
}
