<?php

namespace Drupal\openid_connect\Event;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * An event to alter the URL options in the authorize method.
 */
class AuthorizeUrlOptionsAlterEvent extends Event {

  /**
   * Name of the event fired when constructing the URL options when authorizing.
   *
   * @Event
   */
  const AUTHORIZE_URL_OPTIONS_ALTER = 'openid_connect.authorize.url_options_alter';

  /**
   * The URL options.
   *
   * @var array
   */
  protected $urlOptions;

  /**
   * The FormState.
   *
   * @var Drupal\Core\Form\FormStateInterface
   */
  protected $formState;

  /**
   * AuthorizeUrlOptionsAlterEvent contructor.
   *
   * @param array $url_options
   *   The URL options to alter.
   * @param FormStateInterface $form_state_interface
   *   Optional FormState.
   */
  public function __construct(array &$url_options, FormStateInterface $form_state_interface = NULL) {
    $this->urlOptions = &$url_options;
    $this->formState = $form_state_interface;
  }

  /**
   * Gets the URL options.
   *
   * @return array
   *   The URL options.
   */
  public function &getUrlOptions() {
    return $this->urlOptions;
  }

  /**
   * Get the FormState.
   *
   * @return FormStateInterface
   *   The FormState
   */
  public function getFormState() {
    return $this->formState;
  }

}
