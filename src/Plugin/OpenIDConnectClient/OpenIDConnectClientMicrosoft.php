<?php

namespace Drupal\openid_connect\Plugin\OpenIDConnectClient;

use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;

/**
 * OpenID Connect client for Microsoft.
 *
 * Implements OpenID Connect Client plugin for Microsoft services, including
 * Microsoft Account, Azure Active Directory, and Office 365.
 *
 * @OpenIDConnectClient(
 *   id = "microsoft",
 *   label = @Translation("Microsoft")
 * )
 */
class OpenIDConnectClientMicrosoft extends OpenIDConnectClientBase {

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   */
  public function getEndpoints() {
    return array(
      'authorization' => 'https://login.microsoftonline.com/common/oauth2/authorize',
      'token' => 'https://login.microsoftonline.com/common/oauth2/token',
      'userinfo' => 'https://login.microsoftonline.com/common/openid/userinfo',
    );
  }

  /**
   * Overrides OpenIDConnectClientBase::retrieveUserInfo().
   */
  public function retrieveUserInfo($access_token) {
    $userinfo = parent::retrieveUserInfo($access_token);
    if ($userinfo) {
      // Azure AD returns email address in the upn field.
      $userinfo['email'] = $userinfo['upn'];
    }

    return $userinfo;
  }

}
