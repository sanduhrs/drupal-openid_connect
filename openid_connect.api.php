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
 * Perform alterations before user profile information is saved.
 *
 * User profile information is fetched from the login provider and saved based
 * on the configured mapping. This hook can perform alterations before saving
 * the data.
 * Fields or user properties on the client site may require a different format
 * than the data is in based on the OpenID Connect specification or in the
 * unfortunate case when the login provider doesn't follow that specification.
 *
 * @see http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 */
function hook_openid_connect_LOGIN_PROVIDER_userinfo_alter(&$userinfo) {
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
