<?php

/**
 * @file
 * Contains Drupal\openid_connect\Form\LoginForm.
 */

namespace Drupal\openid_connect\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;

/**
 * Class LoginForm.
 *
 * @package Drupal\openid_connect\Form
 */
class LoginForm extends FormBase {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $plugin_manager;

  public function __construct(
    OpenIDConnectClientManager $plugin_manager
  ) {
    $this->plugin_manager = $plugin_manager;
  }

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
    $plugin_manager = $this->plugin_manager;
    $definitions = $plugin_manager->getDefinitions();
    foreach ($definitions as $client_id => $client) {
      if (!\Drupal::config('openid_connect.settings.' . $client_id)
        ->get('enabled')) {
        continue;
      }

      $form['openid_connect_client_' . $client_id . '_login'] = array(
        '#type' => 'submit',
        '#value' => t('!client_title', array(
          '!client_title' => $client['label'])
        ),
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

    $plugin_manager = $this->plugin_manager;
    $client = $plugin_manager->createInstance($client_name);
    $scopes = openid_connect_get_scopes();
    $_SESSION['openid_connect_op'] = 'login';
    $client->authorize($scopes);
  }

}
