<?php

/**
 * @file
 * Provides an OpenID Connect client plugin for Google.
 */

$plugin = array(
  'title' => t('Google'),
  'class' => 'OpenIDConnectClientGoogle',
  'login_provider_endpoint' => array(
    'authentication' => 'https://accounts.google.com/o/oauth2/auth',
    'token' => 'https://accounts.google.com/o/oauth2/token',
    'userinfo' => 'https://www.googleapis.com/plus/v1/people/me/openIdConnect',
  ),
);
