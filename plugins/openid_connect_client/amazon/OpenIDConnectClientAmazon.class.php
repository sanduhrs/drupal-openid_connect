<?php

/**
 * @file
 * OpenID Connect client for Amazon.
 */

/**
 * Implements OpenID Connect Client plugin for Amazon.
 */
class OpenIDConnectClientAmazon implements OpenIDConnectClientInterface {

  /**
   * Implements OpenIDConnectClientInterface::sendAuthenticationRequest().
   */
  public static function sendAuthenticationRequest($client_id, $scope, $authentication_endpoint, $redirect_url, $state_token) {

  }

  /**
   * Implements OpenIDConnectClientInterface::retrieveIDToken().
   */
  public function retrieveTokens($authorization_code, $token_endpoint, $client_id, $client_secret, $redirect_url) {

  }

  /**
   * Implements OpenIDConnectClientInterface::decodeIDToken().
   */
  public function decodeIDToken($id_token) {

  }

}
