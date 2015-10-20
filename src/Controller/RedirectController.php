<?php

/**
 * @file
 * Contains Drupal\openid_connect\Controller\RedirectController.
 */

namespace Drupal\openid_connect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\openid_connect\Plugin\OpenIDConnectClientManager;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Class RedirectController.
 *
 * @package Drupal\openid_connect\Controller
 */
class RedirectController extends ControllerBase implements AccessInterface {

  /**
   * Drupal\openid_connect\Plugin\OpenIDConnectClientManager definition.
   *
   * @var Drupal\openid_connect\Plugin\OpenIDConnectClientManager
   */
  protected $plugin_manager;

  /**
   * {@inheritdoc}
   */
  public function __construct(OpenIDConnectClientManager $plugin_manager) {
    $this->plugin_manager = $plugin_manager;
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
   * Access callback: Redirect page.
   */
  public function access() {
    // Confirm anti-forgery state token. This round-trip verification helps to
    // ensure that the user, not a malicious script, is making the request.
    if (openid_connect_redirect_access()) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

  /**
   * Redirect.
   *
   * @return string
   *   Return Hello string.
   */
  public function authenticate($client_name) {
    // Delete the state token, since it's already been confirmed.
    unset($_SESSION['openid_connect_state']);

    // Get parameters from the session, and then clean up.
    $parameters = array(
      'destination' => 'user',
      'op' => 'login',
      'connect_uid' => NULL,
    );
    foreach ($parameters as $key => $default) {
      if (isset($_SESSION['openid_connect_' . $key])) {
        $parameters[$key] = $_SESSION['openid_connect_' . $key];
        unset($_SESSION['openid_connect_' . $key]);
      }
    }
    $destination = $parameters['destination'];

    $configuration = \Drupal::config('openid_connect.settings.' . $client_name)
      ->get('settings');
    $client = $this->plugin_manager->createInstance(
      $client_name,
      $configuration
    );
    if (!isset($_GET['error']) && (!$client || !isset($_GET['code']))) {
      // In case we don't have an error, but the client could not be loaded or
      // there is no state token specified, the URI is probably being visited
      // outside of the login flow.
      throw new NotFoundHttpException();
    }

    $provider_param = array('@provider' => $client->getLabel());

    if (isset($_GET['error'])) {
      if ($_GET['error'] == 'access_denied') {
        // If we have an "access denied" error, that means the user hasn't
        // granted the authorization for the claims.
        drupal_set_message(t('Logging in with @provider has been canceled.', $provider_param), 'warning');
      }
      else {
        // Any other error should be logged. E.g. invalid scope.
        $variables = array(
          '@error' => $_GET['error'],
          '@details' => $_GET['error_description'],
        );
        $message = 'Authorization failed: @error. Details: @details';
        \Drupal::logger('openid_connect_' . $client_name)->error($message, $variables);
      }
    }
    else {
      // Process the login or connect operations.
      $tokens = $client->retrieveTokens($_GET['code']);
      if ($tokens) {
        if ($parameters['op'] === 'login') {
          $success = openid_connect_complete_authorization($client, $tokens, $destination);
          if (!$success) {
            drupal_set_message(t('Logging in with @provider could not be completed due to an error.', $provider_param), 'error');
          }
        }
        elseif ($parameters['op'] === 'connect' && $parameters['connect_uid'] === $GLOBALS['user']->uid) {
          $success = openid_connect_connect_current_user($client, $tokens);
          if ($success) {
            drupal_set_message(t('Account successfully connected with @provider.', $provider_param));
          }
          else {
            drupal_set_message(t('Connecting with @provider could not be completed due to an error.', $provider_param), 'error');
          }
        }
      }
    }

    // It's possible to set 'options' in the redirect destination.
    if (is_array($destination)) {
      $redirect = Url::fromUri('internal:/' . ltrim($destination[0], '/'), $destination[1])->toString();
      $response = new RedirectResponse($redirect);
      return $response->send();
    }
    else {
      $redirect = Url::fromUri('internal:/' . ltrim($destination, '/'))->toString();
      $response = new RedirectResponse($redirect);
      return $response->send();
    }
  }

}
