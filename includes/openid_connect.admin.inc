<?php

/**
 * @file
 * Administrative UI and functions for the OpenID Connect module.
 */

/**
 * Form builder: Main administrative form.
 */
function openid_connect_admin_form($form, &$form_state) {
  $client_plugins = openid_connect_get_plugins();
  if (!$client_plugins) {
    return array(array('#markup' => t('No OpenID Connect plugins were found.')));
  }

  $form['general_settings'] = array(
    '#title' => t('General settings'),
    '#type' => 'fieldset',
  );
  $options = array();
  foreach ($client_plugins as $client) {
    $options[$client['name']] = $client['title'];
  }
  $form['general_settings']['openid_connect_clients_enabled'] = array(
    '#title' => t('Enabled OpenID Connect clients'),
    '#description' => t('Choose enabled OpenID Connect clients.'),
    '#type' => 'checkboxes',
    '#options' => $options,
    '#default_value' => variable_get('openid_connect_clients_enabled', array()),
  );

  if ($client_plugins_enabled = openid_connect_get_plugins(TRUE)) {
    $form['clients'] = array(
      '#title' => t('OpenID Connect clients configuration'),
      '#type' => 'fieldset',
    );
    foreach ($client_plugins_enabled as $client) {
      $form['clients'][$client['name']] = array(
        '#title' => $client['title'],
        '#type' => 'fieldset',
      );
      $form['clients'][$client['name']]['openid_connect_client_' . $client['name'] . '_client_id'] = array(
        '#title' => t('Client ID'),
        '#type' => 'textfield',
        '#default_value' => variable_get('openid_connect_client_' . $client['name'] . '_client_id', ''),
      );
      $form['clients'][$client['name']]['openid_connect_client_' . $client['name'] . '_client_secret'] = array(
        '#title' => t('Client secret'),
        '#type' => 'textfield',
        '#default_value' => variable_get('openid_connect_client_' . $client['name'] . '_client_secret', ''),
      );
    }
  }

  return system_settings_form($form);
}
