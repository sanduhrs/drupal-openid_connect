<?php

/**
 * @file
 * Contains Drupal\openid_connect\Form\SettingsForm.
 */

namespace Drupal\openid_connect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\openid_connect\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openid_connect.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openid_connect_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('openid_connect.settings');

    $plugin_manager = \Drupal::service('plugin.manager.openid_connect_client.processor');
    $client_plugins = $plugin_manager->getDefinitions();

    $options = array();
    foreach ($client_plugins as $client_plugin) {
      $options[$client_plugin['id']] = $client_plugin['label'];
    }

    $clients_enabled = array();
    foreach ($client_plugins as $client_plugin) {
      $enabled = \Drupal::configFactory()
        ->getEditable('openid_connect.settings.' . $client_plugin['id'])
        ->get('enabled');
      $clients_enabled[$client_plugin['id']] = (bool) $enabled ? $client_plugin['id'] : 0;
    }

    $form['#tree'] = TRUE;
    $form['clients_enabled'] = array(
      '#title' => t('Enabled OpenID Connect clients'),
      '#description' => t('Choose enabled OpenID Connect clients.'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $clients_enabled,
    );
    foreach ($client_plugins as $client_name => $client_plugin) {
      $configuration = \Drupal::config('openid_connect.settings.' . $client_name)
        ->get('settings');
      $client = $plugin_manager->createInstance(
        $client_name,
        $configuration
      );

      $element = 'clients_enabled[' . $client_plugin['id'] . ']';
      $form['clients'][$client_plugin['id']] = array(
        '#title' => $client_plugin['label'],
        '#type' => 'fieldset',
        '#tree' => TRUE,
        '#states' => array(
          'visible' => array(
            ':input[name="' . $element . '"]' => array('checked' => TRUE),
          ),
        ),
      );
      $form['clients'][$client_plugin['id']]['settings'] = array();
      $form['clients'][$client_plugin['id']]['settings'] += $client->settingsForm();
    }

    $form['always_save_userinfo'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Save user claims on every login'),
      '#description' => $this->t('If disabled, user claims will only be saved when the account is first created.'),
      '#default_value' => $config->get('always_save_userinfo'),
    );
    $form['user_pictures'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Fetch user profile picture from login provider'),
      '#description' => $this->t('Whether the user profile picture from the login provider should be fetched and saved locally.'),
      '#default_value' => $config->get('user_pictures'),
    );

    $form['userinfo_mappings'] = array(
      '#title' => t('User claims mapping'),
      '#type' => 'fieldset',
    );

    $properties = \Drupal::entityManager()->getFieldDefinitions('user', 'user');
    $properties_skip = _openid_connect_user_properties_to_skip();
    $claims = openid_connect_claims_options();
    $mappings = $always_save_userinfo = $config->get('userinfo_mappings');
    foreach ($properties as $property_name => $property) {
      if (isset($properties_skip[$property_name])) {
        continue;
      }
      // Always map the timezone.
      $default_value = 0;
      if ($property_name == 'timezone') {
        $default_value = 'zoneinfo';
      }

      $form['userinfo_mappings'][$property_name] = array(
        '#type' => 'select',
        '#title' => $property->getLabel(),
        '#description' => $property->getDescription(),
        '#options' => (array) $claims,
        '#empty_value' => 0,
        '#empty_option' => t('- No mapping -'),
        '#default_value' => isset($mappings[$property_name]) ? $mappings[$property_name] : $default_value,
      );
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('openid_connect.settings')
      ->set('always_save_userinfo', $form_state->getValue('always_save_userinfo'))
      ->set('user_pictures', $form_state->getValue('user_pictures'))
      ->set('userinfo_mappings', $form_state->getValue('userinfo_mappings'))
      ->save();
    $clients_enabled = $form_state->getValue('clients_enabled');
    foreach ($clients_enabled as $client_name => $status) {
      \Drupal::configFactory()
        ->getEditable('openid_connect.settings.' . $client_name)
        ->set('enabled', $status)
        ->save();
      if ((bool) $status) {
        \Drupal::configFactory()
          ->getEditable('openid_connect.settings.' . $client_name)
          ->set('settings', $form_state->getValue(array(
            'clients', $client_name, 'settings',
          )))
          ->save();
      }
    }

  }

}
