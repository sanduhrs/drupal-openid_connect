<?php

/**
 * @file
 * Contains \Drupal\openid_connect\Plugin\OpenIDConnectClient\OpenIDConnectClientGoogle.
 */

namespace Drupal\openid_connect\Plugin\OpenIDConnectClient;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\openid_connect\StateToken;

/**
 * OpenID Connect client for Google.
 *
 * Implements OpenID Connect Client plugin for Google.
 *
 * @OpenIDConnectClient(
 *   id = "google",
 *   label = @Translation("Google")
 * )
 */
class OpenIDConnectClientGoogle extends OpenIDConnectClientBase {

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['client_hd'] = array(
      '#title' => t('Hosted domain'),
      '#type' => 'textfield',
      '#description' => t('Limit sign-ins to the hosted domain name for the user\'s Google Apps account. For instance, example.com.'),
      '#default_value' => $this->configuration['client_hd'],
    );

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   */
  public function getEndpoints() {
    return array(
      'authorization' => 'https://accounts.google.com/o/oauth2/auth',
      'token' => 'https://accounts.google.com/o/oauth2/token',
      'userinfo' => 'https://www.googleapis.com/plus/v1/people/me/openIdConnect',
    );
  }

  /**
   * Overrides OpenIDConnectClientBase::retrieveUserInfo().
   */
  public function retrieveUserInfo($access_token) {
    $userinfo = parent::retrieveUserInfo($access_token);
    if ($userinfo) {
      // For some reason Google returns the URI of the profile picture in a
      // weird format: "https:" appears twice in the beginning of the URI.
      // Using a regular expression matching for fixing it guarantees that
      // things won't break if Google changes the way the URI is returned.
      preg_match('/https:\/\/*.*/', $userinfo['picture'], $matches);
      $userinfo['picture'] = $matches[0];
    }

    return $userinfo;
  }

  /**
   * Overrides OpenIDConnectClientBase::authorize().
   */
  public function authorize($scope = 'openid email') {
    $redirect_uri = Url::fromRoute(
      'openid_connect.redirect_controller_redirect',
      array('client_name' => $this->pluginId), array('absolute' => TRUE)
    )->toString();

    $url_options = array(
      'query' => array(
        'client_id' => $this->configuration['client_id'],
        'response_type' => 'code',
        'scope' => $scope,
        'redirect_uri' => $redirect_uri,
        'state' => StateToken::create(),
        'hd' => $this->configuration['client_hd'],
      ),
    );

    $endpoints = $this->getEndpoints();
    // Clear _GET['destination'] because we need to override it.
    $this->requestStack->getCurrentRequest()->query->remove('destination');
    $authorization_endpoint = Url::fromUri($endpoints['authorization'], $url_options)->toString();

    $response = new TrustedRedirectResponse($authorization_endpoint);
    return $response;
  }

}
