<?php

$databases = array (
  'default' => 
  array (
    'default' => 
    array (
      'database' => 'genvasc_drupal',
      'username' => 'genvasc_user',
      'password' => 'l4de3fe',
      'host' => 'briccsdb.xuhl-tr.nhs.uk',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
  
  'ice_messaging' =>
  array (
    'default' =>
    array (
      'database' => 'genvasc-ice',
      'username' => 'briccs_admin',
      'password' => 'bR1cc5100',
      'host' => 'UHLSQLBRICCSDB\\UHLBRICCSDB',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    ),
  ),

  'civicrm' =>
  array (
    'default' =>
    array (
      'database' => 'genvasc_civicrm',
      'username' => 'genvasc_user',
      'password' => 'l4de3fe',
      'host' => 'briccsdb.xuhl-tr.nhs.uk',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),

  'PmiDb' =>
  array (
    'default' =>
    array (
      'database' => 'PMIS_LIVE',
      'username' => 'pmis_live_briccs',
      'password' => 'un1v3r$ity',
      'host' => 'UHLSQLIEFRAME\REPOSITORY',
      'driver' => 'dblib',
      'collation' => 'utf8_general_ci',
    ),
  ),

  'OnyxDB' =>
  array (
    'default' =>
    array (
      'database' => 'briccs',
      'username' => 'civi-importer',
      'password' => 'pz24de51',
      'host' => 'briccsdb.xuhl-tr.nhs.uk',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),

  'daps' =>
  array (
    'default' =>
    array (
      'database' => 'dwpatmatch',
      'username' => 'BRICCSVIEW',
      'password' => 'br1cc5',
      'host' => 'uhldwh',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    ),
  ),

  'reporting' =>
  array (
    'default' =>
    array (
      'database' => 'reporting',
      'username' => 'briccs_admin',
      'password' => 'bR1cc5100',
      'host' => 'UHLSQLBRICCSDB\\UHLBRICCSDB',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    ),
  ),
);

$databases['redcap'] = array (
    'default' =>
    array (
      'database' => 'redcap6170_briccs',
      'username' => 'auditor',
      'password' => 'g3s4t6',
      'host' => 'uhlbriccsdb02',
      'driver' => 'mysql',
      'port' => '',
      'prefix' => '',
    ),
  );

$update_free_access = FALSE;

$drupal_hash_salt = '7brWoPlmaS_KMxREVLkaJmYMqOpBZ27Nch-4PCXvsrU';

ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

ini_set('session.gc_maxlifetime', 200000);

ini_set('session.cookie_lifetime', 2000000);

$conf['404_fast_paths_exclude'] = '/\/(?:styles)\//';
$conf['404_fast_paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$conf['404_fast_html'] = '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

