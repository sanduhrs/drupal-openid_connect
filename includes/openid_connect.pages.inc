<?php

/**
 * @file
 * Page callbacks for OpenID Connect.
 */

/**
 * Page callback: Page whereto OpenID Connect login provider redirects.
 */
function openid_connect_redirect_page($client_name) {
  // Delete the state token, since it's already been confirmed.
  unset($_SESSION['openid_connect_state']);

  // Ensure some defaults.
  $_SESSION += array(
    'openid_connect_destination' => 'user',
    'openid_connect_op' => 'login',
    'openid_connect_connect_uid' => NULL,
  );

  // Get the destination from the session (than clean up after that, too), so
  // that we can redirect the user back from where they initiated the login.
  $destination = ltrim($_SESSION['openid_connect_destination'], '/');
  unset($_SESSION['openid_connect_destination']);

  $client = openid_connect_get_client($client_name);
  if (!isset($_GET['error']) && (!$client || !isset($_GET['code']))) {
    // In case we don't have an error, but the client could not be loaded or
    // there is no state token specified, the URI is probably being visited
    // outside of the login flow.
    return MENU_NOT_FOUND;
  }

  if (isset($_GET['error'])) {
    if ($_GET['error'] == 'access_denied') {
      // If we have an "access denied" error, that means the user hasn't granted
      // the authorization for the claims.
      drupal_set_message(t('Sign in with @provider has been canceled.', array('@provider' => $client->getLabel())), 'warning');
    }
    else {
      // Any other error should be logged. E.g. invalid scope.
      $variables = array(
        '@error' => $_GET['error'],
        '@details' => $_GET['error_description'],
      );
      watchdog('openid_connect_' . $client_name, 'Authorization failed: @error. Details: @details', $variables, WATCHDOG_ERROR);
    }
  }
  else {
    // Process the login or connect operations.
    $tokens = $client->retrieveTokens($_GET['code']);
    if ($tokens) {
      if ($_SESSION['openid_connect_op'] ===  'login') {
        $success = openid_connect_complete_authorization($client, $tokens);
        if (!$success) {
          drupal_set_message(t('Sign in with @provider could not be completed due to an error.', array('@provider' => $client->getLabel())), 'error');
        }
      }
      elseif ($_SESSION['openid_connect_op'] === 'connect' && $_SESSION['openid_connect_connect_uid'] === $GLOBALS['user']->uid) {
        $success = openid_connect_connect_current_user($client, $tokens);
        if ($success) {
          drupal_set_message(t('Account successfully connected with @provider.', array('@provider' => $client->getLabel())));
        }
        else {
          drupal_set_message(t('Connecting with @provider could not be completed due to an error.', array('@provider' => $client->getLabel())), 'error');
        }
      }
    }
  }

  drupal_goto($destination);
}
