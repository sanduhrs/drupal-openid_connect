<?php

/**
 * @file
 * Contains Drupal\openid_connect\Form\LoginForm.
 */

namespace Drupal\openid_connect\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoginForm.
 *
 * @package Drupal\openid_connect\Form
 */
class LoginForm extends FormBase implements ContainerInjectionInterface {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $pluginManager;

  /**
   * The constructor.
   *
   * @param \Drupal\openid_connect\Plugin\OpenIDConnectClientManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(OpenIDConnectClientManager $plugin_manager) {
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.openid_connect_client.processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openid_connect_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $plugin_manager = $this->pluginManager;
    $definitions = $plugin_manager->getDefinitions();
    foreach ($definitions as $client_id => $client) {
      if (!$this->config('openid_connect.settings.' . $client_id)
        ->get('enabled')) {
        continue;
      }

      $form['openid_connect_client_' . $client_id . '_login'] = array(
        '#type' => 'submit',
        '#value' => t('!client_title', array(
          '!client_title' => $client['label'],
        )),
        '#name' => $client_id,
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    openid_connect_save_destination();
    $client_name = $form_state->getTriggeringElement()['#name'];

    $plugin_manager = $this->pluginManager;
    $configuration = $this->config('openid_connect.settings.' . $client_name)
      ->get('settings');
    $client = $plugin_manager->createInstance(
      $client_name,
      $configuration
    );
    $scopes = openid_connect_get_scopes();
    $_SESSION['openid_connect_op'] = 'login';
    $response = $client->authorize($scopes, $form_state);
    $form_state->setResponse($response);
  }

}
