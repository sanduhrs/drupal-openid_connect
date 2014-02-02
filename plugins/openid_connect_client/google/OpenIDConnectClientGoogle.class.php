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
    drupal_goto($authentication_endpoint, $url_options);
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   */
  public function retrieveTokens($authorization_code, $token_endpoint, $client_id, $client_secret, $redirect_url) {
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
    return array(
      'id_token' => $response_data['id_token'],
      'access_token' => $response_data['access_token'],
      'expire' => REQUEST_TIME + $response_data['expires_in'],
    );
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
