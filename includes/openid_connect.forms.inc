<?php

/**
 * @file
 * Forms for OpenID Connect.
 */

/**
 * Form builder: Log in with an external account.
 */
function openid_connect_login_form($form, &$form_state) {
  form_load_include($form_state, 'inc', 'openid_connect', 'includes/openid_connect.forms');

  foreach (openid_connect_get_plugins(TRUE) as $client) {
    $form['openid_connect_client_' . $client['name'] . '_login'] = array(
      '#type' => 'submit',
      '#value' => t('Log in with !client_title', array('!client_title' => $client['title'])),
      '#name' => $client['name'],
    );
  }

  return $form;
}

/**
 * Form submit handler: Log in with an OpenID Connect login provider.
 */
function openid_connect_login_form_submit(&$form, &$form_state) {
  openid_connect_save_destination();

  $client_name = $form_state['triggering_element']['#name'];
  $client = openid_connect_get_client($client_name);
  $scopes = openid_connect_get_scopes();
  $_SESSION['openid_connect_op'] = 'login';
  $client->authorize($scopes);
}

/**
 * Form builder: Connect an external account to your existing Drupal account.
 */
function openid_connect_connect_form($form, &$form_state, $account) {
  form_load_include($form_state, 'inc', 'openid_connect', 'includes/openid_connect.forms');

  $form_state['account'] = $account;

  $clients = openid_connect_get_plugins(TRUE);

  $read_only = $GLOBALS['user']->uid != $account->uid;

  $form['help'] = array(
    '#prefix' => '<p class="description">',
    '#suffix' => '</p>',
  );

  if (empty($clients)) {
    $form['help']['#markup'] = t('No external account providers are available.');

    return $form;
  }
  elseif ($GLOBALS['user']->uid == $account->uid) {
    $form['help']['#markup'] = t('You can connect your account with these external providers.');
  }

  $connected_accounts = openid_connect_get_connected_accounts($account);

  foreach ($clients as $client) {
    $form[$client['name']] = array(
      '#type' => 'fieldset',
      '#title' => t('Provider: @title', array('@title' => $client['title'])),
    );
    $fieldset = &$form[$client['name']];
    $connected = isset($connected_accounts[$client['name']]);
    $fieldset['status'] = array(
      '#type' => 'item',
      '#title' => t('Status'),
      '#markup' => t('Not connected'),
    );
    if ($connected) {
      $fieldset['status']['#markup'] = t('Connected as %sub', array(
        '%sub' => $connected_accounts[$client['name']],
      ));
      $fieldset['openid_connect_client_' . $client['name'] . '_disconnect'] = array(
        '#type' => 'submit',
        '#value' => t('Disconnect from !client_title', array('!client_title' => $client['title'])),
        '#name' => 'disconnect__' . $client['name'],
        '#access' => !$read_only,
      );
    }
    else {
      $fieldset['status']['#markup'] = t('Not connected');
      $fieldset['openid_connect_client_' . $client['name'] . '_connect'] = array(
        '#type' => 'submit',
        '#value' => t('Connect with !client_title', array('!client_title' => $client['title'])),
        '#name' => 'connect__' . $client['name'],
        '#access' => !$read_only,
      );
    }
  }

  return $form;
}

/**
 * Submit callback for the connect account form.
 *
 * Connect the current user's Drupal account to an external provider.
 */
function openid_connect_connect_form_submit(&$form, &$form_state) {
  list($op, $client_name) = explode('__', $form_state['triggering_element']['#name'], 2);

  if ($op === 'disconnect') {
    openid_connect_disconnect_account($form_state['account'], $client_name);
    $client = openid_connect_get_client($client_name);
    drupal_set_message(t('Account successfully disconnected from @client.', array('@client' => $client->getLabel())));

    return;
  }

  global $user;

  if ($user->uid !== $form_state['account']->uid) {
    drupal_set_message(t("You cannot connect another user's account."), 'error');

    return;
  }

  openid_connect_save_destination();

  $client = openid_connect_get_client($client_name);
  $scopes = openid_connect_get_scopes();
  $_SESSION['openid_connect_op'] = $op;
  $_SESSION['openid_connect_connect_uid'] = $user->uid;
  $client->authorize($scopes);
}
