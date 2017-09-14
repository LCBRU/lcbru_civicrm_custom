<?php

/*
 * Database config.
 *
 */
$databases['default']['default'] = array(
    'driver'    => 'mysql',
    'database'  => 'drupaltest',
    'username'  => 'drupaltestuser',
    'password'  => 'd0lk5f4e',
    'host'      => 'uhlbriccsdbdev.xuhl-tr.nhs.uk',
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['ice_messaging']['default'] = array(
    'driver'    => 'dblib',
    'database'  => 'genvasc-ice-test',
    'username'  => 'briccs_admin',
    'password'  => 'bR1cc5100',
    'host'      => 'UHLSQLBRICCSDB\\UHLBRICCSDB',
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['civicrm']['default'] = array(
    'driver'    => 'mysql',
    'database'  => 'civicrmtest',
    'username'  => 'civicrmtestuser',
    'password'  => 'd0lk5f4e',
    'host'      => 'uhlbriccsdbdev.xuhl-tr.nhs.uk',
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['OnyxDB']['default'] = array(
    'database' => 'briccs',
    'username' => 'civi-importer',
    'password' => 'd3Hpceh33*ty',
    'host' => 'uhlbriccsdbdev.xuhl-tr.nhs.uk',
    'driver' => 'mysql',
  );

$databases['PmiDb']['default'] = array(
    'driver'    => 'dblib',
    'database'  => 'PMIS_TEST',
    'username'  => 'pmis_live_briccs',
    'password'  => 'un1v3r$ity',
    'host'      => 'UHLSQLTSDEV01',
    'prefix'    => '',
    'collation' => 'utf8_general_ci',
    );

$databases['daps']['default'] = array(
      'database' => 'dwpatmatch',
      'username' => 'BRICCSVIEW',
      'password' => 'br1cc5',
      'host' => 'uhldwh',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    );


$databases['reporting']['default'] = array (
      'database' => 'reporting',
      'username' => 'briccs_admin',
      'password' => 'bR1cc5100',
      'host' => 'UHLSQLBRICCSDB\\UHLBRICCSDB',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    );
 
/**
 * Salt for one-time login links, cancel links and form tokens, etc.
 *
 */
$drupal_hash_salt = 'randomorsomething';
