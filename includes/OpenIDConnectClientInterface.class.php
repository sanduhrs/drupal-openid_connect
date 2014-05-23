<?php

/**
 * @file
 * Interface to implement OpenID Connect clients.
 */

interface OpenIDConnectClientInterface {

  /**
   * Returns the value of a setting.
   */
  public function getSetting($key, $default = NULL);

  /**
   * Returns the settings form.
   *
   * The client settings form is embedded into the module's main settings form,
   * and the configured values are injected into the client class through the
   * constructor.
   */
  public function settingsForm();

  /**
   * Validates the settings form.
   *
   * @param $form
   *   The settings form.
   * @param $form_state
   *   An array containing the current state of the form. Contains only
   *   the 'values' key, since it is actually constructed on the fly by the
   *   parent form.
   * @param $error_element_base
   *   The base to prepend to field names when using form_set_error().
   */
  public function settingsFormValidate($form, &$form_state, $error_element_base);

  /**
   * Handles the submitted settings form.
   *
   * Note that there is no need to handle saving the options, that is done
   * by the parent form.
   *
   * @param $form
   *   The settings form.
   * @param $form_state
   *   An array containing the current state of the form. Contains only
   *   the 'values' key, since it is actually constructed on the fly by the
   *   parent form.
   */
  public function settingsFormSubmit($form, &$form_state);

  /**
   * Returns an array of endpoints.
   *
   * @return
   *   An array with the following keys:
   *   - authorization: The full url to the authorization endpoint.
   *   - token: The full url to the token endpoint.
   *   - userinfo: The full url to the userinfo endpoint.
   */
  public function getEndpoints();

  /**
   * Redirects the user to the authorization endpoint.
   *
   * The authorization endpoint authenticates the user and returns them
   * to the redirect_uri specified previously with an authorization code
   * that can be exchanged for an access token.
   *
   * @param string $scope
   *   Name of scope(s) that with user consent will provide access to otherwise
   *   restricted user data. Defaults to "openid email".
   */
  public function authorize($scope);

  /**
   * Retrieve access token and ID token.
   *
   * Exchanging the authorization code that is received as the result of the
   * authentication request for an access token and an ID token.
   *
   * The ID token is a cryptographically signed JSON object encoded in base64.
   * It contains identity information about the user.
   * The access token can be sent to the login provider to obtain user profile
   * information.
   *
   * @param string $authorization_code
   *   Authorization code received as a result of the the authorization request.
   *
   * @return array
   *   An associative array containing:
   *   - id_token: The ID token that holds user data.
   *   - access_token: Access token that can be used to obtain user profile
   *     information.
   *   - expire: Unix timestamp of the expiration date of the access token.
   */
  public function retrieveTokens($authorization_code);

  /**
   * Decodes ID token to access user data.
   *
   * @param string $id_token
   *   The encoded ID token containing the user data.
   *
   * @return array
   *   User identity information.
   */
  public function decodeIdToken($id_token);

  /**
   * Retrieves user info: additional user profile data.
   *
   * @param string $access_token
   *   Access token.
   *
   * @return array
   *   User profile information.
   */
  public function retrieveUserInfo($access_token);

}
