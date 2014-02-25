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
  public static function retrieveTokens($authorization_code, $token_endpoint, $client_id, $client_secret, $redirect_url) {
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
    if (!isset($response->error) && $response->code == 200) {
      $response_data = drupal_json_decode($response->data);
      return array(
        'id_token' => $response_data['id_token'],
        'access_token' => $response_data['access_token'],
        'expire' => REQUEST_TIME + $response_data['expires_in'],
      );
    }
    else {
      openid_connect_log_request_error(__FUNCTION__, 'google', $response->error, $response->code, $response->data);
      return FALSE;
    }
  }

  /**
   * Implements OpenIDConnectClientInterface::decodeIDToken().
   */
  public static function decodeIDToken($id_token) {
    list($headerb64, $claims64, $signatureb64) = explode('.', $id_token);
    $claims64 = str_replace(array('-', '_'), array('+', '/'), $claims64);
    $claims64 = base64_decode($claims64);
    return drupal_json_decode($claims64);
  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveUserInfo().
   */
  public static function retrieveUserInfo($access_token, $userinfo_endpoint) {
    $request_options = array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $access_token,
      ),
    );
    $request_url = url($userinfo_endpoint);
    $response = drupal_http_request($request_url, $request_options);
    if (!isset($response->error) && $response->code == 200) {
      return drupal_json_decode($response->data);
    }
    else {
      openid_connect_log_request_error(__FUNCTION__, 'google', $response->error, $response->code, $response->data);
      return FALSE;
    }
  }

}
