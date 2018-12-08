# WP Session [![Build Status](https://travis-ci.org/wplibs/session.svg?branch=master)](https://travis-ci.org/wplibs/session)

The missing Session manager for WordPress

## Installation

```
composer require wplibs/session:^1.0
```

## Usage

```php
<?php

use WPLibs\Session\WP_Session;

$session = new WP_Session('my_plugin_session', [
    'lifetime'        => 1440,  // The session lifetime in minutes.
    'expire_on_close' => false, // If true, the session immediately expire on the browser closing.
    'cookie_name'     => null,
]);

// Must call before the `init` hook.
$session->hooks();

// In some where:
$session['user_address'] = 'Some where';

// Retrive
dump($session['user_address']);
```
