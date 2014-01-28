<?php

/**
 * @file
 * OpenID Connect client for Google.
 */

/**
 * Implements OpenID Connect Client plugin for Google.
 */
class OpenIDConnectClientGoogle implements OpenIDConnectClientInterface {

  /**
   * Implements OpenIDConnectClientInterface::sendAuthenticationRequest().
   */
  public static function sendAuthenticationRequest($client_id, $scope, $authentication_endpoint, $redirect_url, $state_token) {
    $url_options = array(
      'query' => array(
        'client_id' => $client_id,
        'response_type' => 'code',
        'scope' => $scope,
        'redirect_uri' => url($redirect_url, array('absolute' => TRUE)),
        'state' => $state_token,
      ),
    );
    $request_url = url($authentication_endpoint, $url_options);
    $response = drupal_http_request($request_url);
    if ($response->code == 200 && isset($response->redirect_url)) {
      drupal_goto($response->redirect_url);
    }
    else {
      // @todo Do a more granular error check and log the error.
      drupal_set_message('The Google sign in could not be completed due to an error.', 'error');
    }
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   */
  public function retrieveIDToken($authorization_code, $token_endpoint, $client_id, $client_secret, $redirect_url) {
    // Exchange `code` for access token and ID token.
    $post_data = array(
      'code' => $authorization_code,
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri' => $redirect_url,
      'grant_type' => 'authorization_code',
    );
    $request_options = array(
      'method' => 'POST',
      'data' => drupal_http_build_query($post_data),
      'timeout' => 15,
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    );
    $request_url = url($token_endpoint, array('external' => TRUE));
    $response = drupal_http_request($request_url, $request_options);
    // @todo Make sure request was successful.
    $response_data = drupal_json_decode($response->data);
    return $response_data['id_token'];
  }

  /**
   * Implements OpenIDConnectClientInterface::decodeIDToken().
   */
  public function decodeIDToken($id_token) {
    // Obtain user information from the ID token.
    // @todo Do this properly by retrieving Google’s public keys and performing
    // the validation locally.
    $url_options = array(
      'query' => array(
        'id_token' => $id_token,
      ),
      'external' => TRUE,
    );
    $request_url = url('https://www.googleapis.com/oauth2/v1/tokeninfo', $url_options);
    $response = drupal_http_request($request_url);
    // @todo Make sure request was successful.
    return drupal_json_decode($response->data);
  }

}
