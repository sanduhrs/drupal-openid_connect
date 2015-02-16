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
 * Perform an action after a successful authorization.
 *
 * @param array $tokens
 *   ID token and access token that we received as a result of the OpenID
 *   Connect flow.
 * @param string $destination
 *   Destination path that was set prior to the OpenID Connect flow.
 */
function hook_openid_connect_post_authorize($tokens, $destination) {
  drupal_set_message('Welcome back!');
}

/**
 * Alter the list of possible scopes and claims.
 *
 * @see openid_connect_claims
 *
 * @param array &$claims
 */
function hook_openid_connect_claims_alter(array &$claims) {
  $claims['my_custom_claim'] = array(
    'scope' => 'profile',
  );
}

/**
 * @} End of "addtogroup hooks".
 */
