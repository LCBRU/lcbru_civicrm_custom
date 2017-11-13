<?php

/**
 * Define additional databases
 */

$databases['ice_messaging'] = array (
    'default' =>
    array (
      'database' => 'genvasc-ice-test',
      'username' => 'briccs_admin',
      'password' => 'bR1cc5100',
      'host' => 'UHLSQLBRICCSDB\\UHLBRICCSDB',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    ),
  );

$databases['PmiDb'] = array (
    'default' =>
    array (
      'database' => 'PMIS_LIVE',
      'username' => 'pmis_live_briccs',
      'password' => 'un1v3r$ity',
      'host' => 'UHLSQLIEFRAME\REPOSITORY',
      'driver' => 'dblib',
      'port' => '',
      'prefix' => '',
    ),
  );

$databases['daps'] = array (
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
  );

$databases['reporting'] = array (
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