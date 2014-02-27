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
  $form['general_settings']['userinfo_mapping'] = array(
    '#title' => t('User info mapping'),
    '#type' => 'fieldset',
    '#tree' => TRUE,
  );

  if (variable_get('user_pictures')) {
    $form['general_settings']['userinfo_mapping']['openid_connect_user_pictures'] = array(
      '#title' => t('Fetch user profile picture from login provider'),
      '#description' => t('Whether the user profile picture from the login provider should be fetched and saved locally.'),
      '#type' => 'checkbox',
      '#default_value' => variable_get('openid_connect_user_pictures', TRUE),
    );
  }

  $user_entity_wrapper = entity_metadata_wrapper('user');

  $claims = openid_connect_claims_options();
  $properties = $user_entity_wrapper->getPropertyInfo();
  $properties_skip = _openid_connect_user_properties_to_skip();
  foreach ($properties as $property_name => $property) {
    if (isset($properties_skip[$property_name])) {
      continue;
    }
    $form['general_settings']['userinfo_mapping']['openid_connect_userinfo_mapping_property_' . $property_name] = array(
      '#type' => 'select',
      '#title' => $property['label'],
      '#description' => $property['description'],
      '#options' => $claims,
      '#empty_value' => 0,
      '#empty_option' => t('- No mapping -'),
      '#default_value' => variable_get('openid_connect_userinfo_mapping_property_' . $property_name, 0),
    );
  }

  if ($client_plugins_enabled = openid_connect_get_plugins(TRUE)) {
    $form['clients'] = array(
      '#title' => t('OpenID Connect client configuration'),
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

  $form['#submit'][] = 'openid_connect_admin_form_save_claims';
  return system_settings_form($form);
}

/**
 * Form submit handler; Saves claims into a list from mapped user info.
 */
function openid_connect_admin_form_save_claims($form, &$form_state) {
  $claims = array();
  foreach ($form_state['values']['userinfo_mapping'] as $variable => $claim) {
    if (!empty($claim)) {
      $claims[] = $claim;
      variable_set($variable, $claim);
    }
    else {
      variable_del($variable);
    }
  }
  if (!empty($claims)) {
    variable_set('openid_connect_userinfo_mapping_claims', $claims);
  }
  else {
    variable_del('openid_connect_userinfo_mapping_claims');
  }
}
