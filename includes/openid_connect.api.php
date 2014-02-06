<?php

/**
 * @file
 * Hooks provided by the OpenID Connect module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alters user profile information retrieved from a login provider.
 *
 * User profile information is fetched from the UserInfo endpoint of the login
 * provider. This data can be altered before saving it into a user account.
 *
 * @param array $userinfo
 *   User profile information returned by the login provider. Keys can be
 *   expected as standard claims.
 *
 * @see http://openid.net/specs/openid-connect-core-1_0.html#UserInfo
 * @see http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 */
function hook_openid_connect_LOGIN_PROVIDER_userinfo_alter($userinfo) {
  // For some reason Google returns the URI of the profile picture in a weird
  // format: "https:" appears twice in the beginning of the URI.
  // Using a regular expression matching for fixing it guarantees that things
  // won't break if Google changes the way the URI is returned.
  preg_match('/https:\/\/*.*/', $userinfo['picture'], $matches);
  $userinfo['picture'] = $matches[0];
}

/**
 * @} End of "addtogroup hooks".
 */
