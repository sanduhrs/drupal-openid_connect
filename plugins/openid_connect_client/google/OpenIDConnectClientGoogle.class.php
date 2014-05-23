<?php

/**
 * @file
 * OpenID Connect client for Google.
 */

/**
 * Implements OpenID Connect Client plugin for Google.
 */
class OpenIDConnectClientGoogle extends OpenIDConnectClientBase {

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
}
