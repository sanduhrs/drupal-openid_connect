<?php

/**
 * @file
 * Contains Drupal\openid_connect\Plugin\OpenIDConnectClientBase.
 */

namespace Drupal\openid_connect\Plugin;

use Exception;
use Drupal\Core\Url;
use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Base class for OpenID Connect client plugins.
 */
abstract class OpenIDConnectClientBase extends PluginBase implements OpenIDConnectClientInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    //FIXME: The config should be available in the object
    if (empty($this->configuration)) {
      $this->configuration = \Drupal::config('openid_connect.settings.' . $this->pluginId)->get('settings');
      dsm('config loaded for ' . $this->pluginId, __METHOD__);
    }
  }

  /**
   * Implements OpenIDConnectClientInterface::getLabel().
   */
  public function getLabel() {
    return $this->pluginId;
  }

  /**
   * Implements OpenIDConnectClientInterface::getName().
   */
  public function getName() {
    return $this->pluginId;
  }

  /**
   * Implements OpenIDConnectClientInterface::getSetting().
   */
  public function getSetting($key) {
    return $this->configuration[$key];
  }

  /**
   * Implements OpenIDConnectClientInterface::settingsForm().
   */
  public function settingsForm() {
    $form['client_id'] = array(
      '#title' => t('Client ID'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('client_id'),
    );
    $form['client_secret'] = array(
      '#title' => t('Client secret'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('client_secret'),
    );

    return $form;
  }

  /**
   * Implements OpenIDConnectClientInterface::settingsFormValidate().
   */
  public function settingsFormValidate($form, &$form_state, $error_element_base) {
    // No need to do anything, but make the function have a body anyway
    // so that it's callable by overriding methods.
  }

  /**
   * Implements OpenIDConnectClientInterface::settingsFormSubmit().
   */
  public function settingsFormSubmit($form, &$form_state) {
    // No need to do anything, but make the function have a body anyway
    // so that it's callable by overriding methods.
  }

  /**
   * Implements OpenIDConnectClientInterface::getEndpoints().
   */
  public function getEndpoints() {
    throw new Exception('Unimplemented method getEndpoints().');
  }

  /**
   * Implements OpenIDConnectClientInterface::authorize().
   */
  public function authorize($scope = 'openid email') {
    $redirect_uri = Url::fromRoute(
      'openid_connect.redirect_controller_redirect',
      array('client_name' => $this->pluginId), array('absolute' => TRUE)
    )->toString();

    $url_options = array(
      'query' => array(
        'client_id' => $this->getSetting('client_id'),
        'response_type' => 'code',
        'scope' => $scope,
        'redirect_uri' => $redirect_uri,
        'state' => openid_connect_create_state_token(),
      ),
    );

    $endpoints = $this->getEndpoints();
    // Clear $_GET['destination'] because we need to override it.
    unset($_GET['destination']);
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)->toString();

    $response = new RedirectResponse($authorization_endpoint);
    return $response->send();
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   */
  public function retrieveTokens($authorization_code) {
    // Exchange `code` for access token and ID token.
    $redirect_uri = OPENID_CONNECT_REDIRECT_PATH_BASE . '/' . $this->name;
    $post_data = array(
      'code' => $authorization_code,
      'client_id' => $this->getSetting('client_id'),
      'client_secret' => $this->getSetting('client_secret'),
      'redirect_uri' => url($redirect_uri, array('absolute' => TRUE)),
      'grant_type' => 'authorization_code',
    );
    $request_options = array(
      'method' => 'POST',
      'data' => drupal_http_build_query($post_data),
      'timeout' => 15,
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    );
    $endpoints = $this->getEndpoints();
    $response = drupal_http_request($endpoints['token'], $request_options);
    if (!isset($response->error) && $response->code == 200) {
      $response_data = drupal_json_decode($response->data);
      return array(
        'id_token' => $response_data['id_token'],
        'access_token' => $response_data['access_token'],
        'expire' => REQUEST_TIME + $response_data['expires_in'],
      );
    }
    else {
      openid_connect_log_request_error(__FUNCTION__, $this->name, $response);
      return FALSE;
    }
  }

  /**
   * Implements OpenIDConnectClientInterface::decodeIdToken().
   */
  public function decodeIdToken($id_token) {
    list($headerb64, $claims64, $signatureb64) = explode('.', $id_token);
    $claims64 = str_replace(array('-', '_'), array('+', '/'), $claims64);
    $claims64 = base64_decode($claims64);
    return drupal_json_decode($claims64);
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveUserInfo().
   */
  public function retrieveUserInfo($access_token) {
    $request_options = array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $access_token,
      ),
    );
    $endpoints = $this->getEndpoints();
    $response = drupal_http_request($endpoints['userinfo'], $request_options);
    if (!isset($response->error) && $response->code == 200) {
      return drupal_json_decode($response->data);
    }
    else {
      openid_connect_log_request_error(__FUNCTION__, $this->name, $response);
      return FALSE;
    }
  }

}
