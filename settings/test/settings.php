<?php

/*
 * Database config.
 *
 */

$databases['default']['default'] = array(
    'driver'    => 'mysql',
    'database'  => getenv("MYSQL_DRUPAL_DATABASE"),
    'username'  => getenv("MYSQL_DRUPAL_USERNAME"),
    'password'  => getenv("MYSQL_DRUPAL_PASSWORD"),
    'host'      => getenv("MYSQL_DRUPAL_HOST"),
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['civicrm']['default'] = array(
    'driver'    => 'mysql',
    'database'  => getenv("MYSQL_CIVICRM_DATABASE"),
    'username'  => getenv("MYSQL_CIVICRM_USERNAME"),
    'password'  => getenv("MYSQL_CIVICRM_PASSWORD"),
    'host'      => getenv("MYSQL_CIVICRM_HOST"),
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['ice_messaging']['default'] = array(
    'driver'    => 'dblib',
    'database'  => getenv("MYSQL_ICE_DATABASE"),
    'username'  => getenv("MYSQL_ICE_USERNAME"),
    'password'  => getenv("MYSQL_ICE_PASSWORD"),
    'host'      => getenv("MYSQL_ICE_HOST"),
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['OnyxDB']['default'] = array(
    'database' => getenv("MYSQL_ONYX_DATABASE"),
    'username' => getenv("MYSQL_ONYX_USERNAME"),
    'password' => getenv("MYSQL_ONYX_PASSWORD"),
    'host'     => getenv("MYSQL_ONYX_HOST"),
    'driver'   => 'mysql',
  );

$databases['PmiDb']['default'] = array(
    'driver'    => 'dblib',
    'database'  => getenv("MYSQL_PMI_DATABASE"),
    'username'  => getenv("MYSQL_PMI_USERNAME"),
    'password'  => getenv("MYSQL_PMI_PASSWORD"),
    'host'      => getenv("MYSQL_PMI_HOST"),
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['daps']['default'] = array(
      'database' => getenv("MYSQL_DAPS_DATABASE"),
      'username' => getenv("MYSQL_DAPS_USERNAME"),
      'password' => getenv("MYSQL_DAPS_PASSWORD"),
      'host'     => getenv("MYSQL_DAPS_HOST"),
      'driver'   => 'dblib',
      'port'     => '',
      'prefix'   => '',
    );


$databases['reporting']['default'] = array (
      'database' => getenv("MYSQL_REPORTING_DATABASE"),
      'username' => getenv("MYSQL_REPORTING_USERNAME"),
      'password' => getenv("MYSQL_REPORTING_PASSWORD"),
      'host'     => getenv("MYSQL_REPORTING_HOST"),
      'driver'   => 'dblib',
      'port'     => '',
      'prefix'   => '',
    );
 
$databases['redcap']['default'] = array (
        'database' => getenv("MYSQL_REDCAP_DATABASE"),
        'username' => getenv("MYSQL_REDCAP_USERNAME"),
        'password' => getenv("MYSQL_REDCAP_PASSWORD"),
        'host'     => getenv("MYSQL_REDCAP_HOST"),
        'driver'   => 'mysql',
      );
   
  /**
 * Salt for one-time login links, cancel links and form tokens, etc.
 *
 */
$drupal_hash_salt = getenv("DRUPAL_HASH_SALT");
