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
  $options = array();
  foreach ($client_plugins as $client_plugin) {
    $options[$client_plugin['name']] = $client_plugin['title'];
  }

  $form['#tree'] = TRUE;
  $form['openid_connect_clients_enabled'] = array(
    '#title' => t('Enabled OpenID Connect clients'),
    '#description' => t('Choose enabled OpenID Connect clients.'),
    '#type' => 'checkboxes',
    '#options' => $options,
    '#default_value' => variable_get('openid_connect_clients_enabled', array()),
  );
  foreach ($client_plugins as $client_plugin) {
    $client = openid_connect_get_client($client_plugin['name']);

    $element = 'openid_connect_clients_enabled[' . $client_plugin['name'] . ']';
    $form['clients'][$client_plugin['name']] = array(
      '#title' => $client_plugin['title'],
      '#type' => 'fieldset',
      '#states' => array(
        'visible' => array(
          ':input[name="' . $element . '"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['clients'][$client_plugin['name']] += $client->settingsForm();
  }

  $form['userinfo_mapping'] = array(
    '#title' => t('User info mapping'),
    '#type' => 'fieldset',
  );
  if (variable_get('user_pictures')) {
    $form['userinfo_mapping']['openid_connect_user_pictures'] = array(
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
    // Always map the timezone.
    $default_value = 0;
    if ($property_name == 'timezone') {
      $default_value = 'zoneinfo';
    }

    $form['userinfo_mapping']['openid_connect_userinfo_mapping_property_' . $property_name] = array(
      '#type' => 'select',
      '#title' => $property['label'],
      '#description' => $property['description'],
      '#options' => $claims,
      '#empty_value' => 0,
      '#empty_option' => t('- No mapping -'),
      '#default_value' => variable_get('openid_connect_userinfo_mapping_property_' . $property_name, $default_value),
    );
  }

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  return $form;
}

/**
 * Validation handler for openid_connect_admin_form.
 */
function openid_connect_admin_form_validate($form, &$form_state) {
  $enabled_clients = $form_state['values']['openid_connect_clients_enabled'];
  $enabled_clients = array_filter($enabled_clients);
  foreach ($enabled_clients as $client_name) {
    // Give the client a chance to validate the submitted values.
    $client = openid_connect_get_client($client_name);
    $fake_form = $form['clients'][$client_name];
    $fake_form_state = array('values' => $form_state['values']['clients'][$client_name]);
    $error_element_base = 'clients][' . $client_name . '][';
    $client->settingsFormValidate($fake_form, $fake_form_state, $error_element_base);
  }
}

/**
 * Submit handler for openid_connect_admin_form.
 */
function openid_connect_admin_form_submit($form, &$form_state) {
  $enabled_clients = $form_state['values']['openid_connect_clients_enabled'];
  $enabled_clients = array_filter($enabled_clients);
  variable_set('openid_connect_clients_enabled', $enabled_clients);

  foreach ($enabled_clients as $client_name) {
    // Give the client a chance to modify the submitted values.
    $client = openid_connect_get_client($client_name);
    $fake_form = $form['clients'][$client_name];
    $fake_form_state = array('values' => $form_state['values']['clients'][$client_name]);
    $client->settingsFormSubmit($fake_form, $fake_form_state);

    // Save the settings.
    variable_set('openid_connect_client_' . $client_name, $fake_form_state['values']);
  }

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
