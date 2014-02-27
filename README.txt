OpenID Connect
==============

The OpenID Connect module provides a pluggable client implementation for the
OpenID Connect protocol.

What is OpenID Connect?
=======================
http://openid.net/connect:

  OpenID Connect 1.0 is a simple identity layer on top of the OAuth 2.0
  protocol. It allows Clients to verify the identity of the End-User based on
  the authentication performed by an Authorization Server, as well as to obtain
  basic profile information about the End-User in an interoperable and REST-like
  manner.

What does the module do?
========================
The module allows you to use an external OpenID Connect login provider to
authenticate and log in users on your site. If a user signs in with a login
provider for the first time on the website, a new Drupal user will be created.

Google for instance uses this protocol for all its services. Check out the
OpenID Foundation's announcement of launching OpenID Connect
(http://openid.net/2014/02/26/the-openid-foundation-launches-the-openid-connect-standard).

Features
========

Supported login providers
-------------------------
Sign in with *Google* has been added as the first supported login provider.
There are plans to implement other services in the future, such as Amazon or
Paypal.

In order to use Google as a login provider, you must register your client site
at the Google Cloud Console: http://cloud.google.com/console.
  * Google has to be able to ping your client site's host when you are defining
    the URLs. If you don't have your site deployed yet, you can use any existing
    hostname (e.g. example.com) and point it to your local installation in your
    local webserver's hosts file.
  * Use _http://example.com/openid-connect/google_ as the authorized redirect
    URL (where example.com is your site's base path).
  * Enable the following two APIs: _Google+ API_ and _Identity Toolkit API_.
  * Use the client ID and client secret when configuring your client (covered
    later in this document)

Client configuration
--------------------
Configuration options are available at `admin/config/services/openid-connect`.
You can enable clients for login providers and configure their client ID and
client secret which are necessary credentials for authenticating with an OAuth2
server.

Fetching user profile information
---------------------------------
Basic user profile information stored by the login provider can be fetched upon
login. There are plans to extend this, so that fetching this information will be
triggered by other actions too.
The OpenID Connect specification defines a set of standard Claims
(http://openid.net/specs/openid-connect-core-1_0.html#StandardClaims). Requested
user profile information can be saved on the client site, mapping can be
configured with the configuration UI at `admin/config/services/openid-connect`.
Fields which have an instance attached to the user entity are exposed as well as
some of the user properties. You can choose a standard Claim for each field or
property, but there is no guarantee that the server supports the scope that is
needed for a particular Claim, or that the server has the claimed information.
Please note that in case the server doesn't support a requested scope (based on
a selected Claim), it will response with an error. Be sure to check which scopes
are supported by the login provider.
You can also decide whether the user's profile picture should be fetched from
the login provider. This option is available only if user pictures are enabled
on your site.

  Altering the fetched user profile information
  ---------------------------------------------
  Fields or user properties on the client site may require a different format
  than the data is in based on the OpenID Connect specification or in the
  unfortunate case when the login provider doesn't follow that specification.
  You can implement *hook_openid_connect_LOGIN_PROVIDER_userinfo_alter()* to
  alter the user profile information before saving it. Please check
  `openid_connect.api.php` for documentation.
  Hint: if you get an
  "EntityMetadataWrapperException: Invalid data value given.", you most likely
  need to look into what data is retrieved, what format your fields or
  properties expect, and implement this hook.

Sign in block
-------------
A standard Drupal block is available to sign in with the login providers for
which clients are enabled. A single button is shown for each login provider. You
can alter the form `openid_connect_login_form` with a regular form alter.

Plugin architecture
-------------------

A CTools plugin type is provided by the module, so adding a new client for a
login provider is straightforward.
Warning: Finalizing the client implementation API is in progress. Since this can
dramatically change, this document only provides basic directions, and you can
use the Google implementation as an example.
  * Implement hook_ctools_plugin_directory() in your module. The plugin type you
    need to define the directory for is called `openid_connect_client`.
    To follow the conventions you may want to use plugins/openid_connect_client.
  * Create an .inc file for your plugin in the defined directory. Alternatively
    you can put it in a folder (just like this module does).
  * For an example $plugin array, look at google.inc.
  * Create a new file in the same folder as your plugin .inc file is, and
    implement the OpenIDConnectClientInterface.
  * For an example implementation of the OpenIDConnectClientInterface, look at
    the Google client implementation's OpenIDConnectClientGoogle class.

Miscellaneous
-------------
If a user account is created by this module while authenticating with a login
provider, the password field for that user is hidden. The password in fact is
set to an empty string (which will never be accepted, since it's hashed first
before checked against the database).

Credits
=======
Development is sponsored by Commerce Guys (http://commerceguys.com). Thanks to
Bojan Zivanovic (http://drupal.org/user/86106) for helping to architect the
module.
