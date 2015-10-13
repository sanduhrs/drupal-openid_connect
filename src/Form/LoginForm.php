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
  protected $plugin_manager_openid_connect_client_processor;

  public function __construct(
    OpenIDConnectClientManager $plugin_manager_openid_connect_client_processor
  ) {
    $this->plugin_manager_openid_connect_client_processor = $plugin_manager_openid_connect_client_processor;
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
    $plugin_manager = $this->plugin_manager_openid_connect_client_processor;
    $definitions = $plugin_manager->getDefinitions();
    foreach ($definitions as $client) {
      $form['openid_connect_client_' . $client['id'] . '_login'] = array(
        '#type' => 'submit',
        '#value' => t('Log in with !client_title', array('!client_title' => $client['id'])),
        '#name' => $client['id'],
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
    $client_name = $form_state->getTriggeringElement();

    $plugin_manager = $this->plugin_manager_openid_connect_client_processor;
    $client = $plugin_manager->createInstance($client_name)['#name'];
    $scopes = openid_connect_get_scopes();
    $_SESSION['openid_connect_op'] = 'login';
    $client->authorize($scopes);
  }

}
