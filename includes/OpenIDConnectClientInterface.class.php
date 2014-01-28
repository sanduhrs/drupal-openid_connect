<?php

/**
 * @file
 * Interface to implement OpenID Connect clients.
 */

interface OpenIDConnectClientInterface {

  /**
   * Sends an authentication request towards the login provider.
   *
   * @param string $client_id
   *   ID that you obtain when you register your app at the login provider.
   * @param string $scope
   *   Name of scope(s) that with user consent will provide access to otherwise
   *   restricted user data.
   * @param string $authentication_endpoint
   *   URI of the endpoint whereto send the authentication request.
   * @param string $redirect_url
   *   URI of the client-side (your Drupal installation) endpoint that will
   *   receive the response.
   * @param string $state_token
   *   The state token that is later used for validation.
   */
  public static function sendAuthenticationRequest($client_id, $scope, $authentication_endpoint, $redirect_url, $state_token);

  /**
   * Retrieve access token and ID token.
   *
   * An ID token, which is a cryptographically signed JSON object encoded in
   * base64. It contains the user data.
   *
   * Exchanging the authorization code that is received as the result of the
   * authentication request for an access token and an ID token.
   *
   * @param string $authorization_code
   *   Authorization code received as a result of the the authorization request.
   * @param string $token_endpoint
   *   URI of the endpoint whereto send the access token and ID token request.
   * @param string $client_id
   *   ID that you obtain when you register your app at the login provider.
   * @param string $client_secret
   *   Client secret that you obtain when you register your app at the login
   *   provider.
   * @param string $redirect_url
   *   URI of the client-side (your Drupal installation) endpoint that receive
   *   the response for the authentication request.
   *
   * @return string
   *   An ID token containing the user data.
   */
  public function retrieveIDToken($authorization_code, $token_endpoint, $client_id, $client_secret, $redirect_url);

  /**
   * Decodes ID token to access user data.
   *
   * @param string $id_token
   *   The encoded ID token containing the user data.
   *
   * @return array
   *   User data.
   */
  public function decodeIDToken($id_token);

}
