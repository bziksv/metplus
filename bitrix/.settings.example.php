<?php

return array (
  'session' => array (
    'value' => array ('mode' => 'default'),
    'readonly' => true,
  ),
  'utf_mode' => array (
    'value' => true,
    'readonly' => true,
  ),
  'cache_flags' => array (
    'value' => array (
      'config_options' => 3600.0,
      'site_domain' => 3600.0,
    ),
    'readonly' => false,
  ),
  'cookies' => array (
    'value' => array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),
  'exception_handling' => array (
    'value' => array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => NULL,
    ),
    'readonly' => false,
  ),
  'connections' => array (
    'value' => array (
      'default' => array (
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
        'host' => 'localhost',
        'database' => 'DB_NAME',
        'login' => 'DB_USER',
        'password' => 'DB_PASSWORD',
        'options' => 2.0,
      ),
    ),
    'readonly' => true,
  ),
  'crypto' => array (
    'value' => array (
      'crypto_key' => 'GENERATE_ON_SERVER',
    ),
    'readonly' => true,
  ),
);
