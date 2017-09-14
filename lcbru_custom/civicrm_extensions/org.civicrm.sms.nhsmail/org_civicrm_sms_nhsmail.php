<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * A LONG WAY FROM BEING READY - BASICALLY JUST A COPY OF CLICKATELL ONE FOR THE TIME BEING
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class org_civicrm_sms_nhsmail extends CRM_SMS_Provider {

  /**
   * api type to use to send a message
   * @var	string
   */
  protected $_apiType = 'smtp';

  /**
   * provider details
   * @var	string
   */
  protected $_providerInfo = array();

  /**
   * Temporary file resource id
   * @var	resource
   */
  protected $_command;

  public $_output = ""; // visible outside

  protected $_messageType = array(
    'SMS_TEXT',
  );

  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = array();

  /**
   * Constructor
   *
   * Create and submit an NHS mail gateway SMS 
   *
   * @return void
   */
  function __construct($provider = array( ), $skipAuth = TRUE) {  // clickatell defaults to FALSE, but pre-authentication is not relevant to the smtp gateway
    // initialize vars
    $this->_apiType = CRM_Utils_Array::value('api_type', $provider, 'smtp');
    $this->_providerInfo = $provider;

    if ($skipAuth) {
      return TRUE;
    }

  }

  /**
   * singleton function used to manage this object
   *
   * @return object
   * @static
   *
   */
  static function &singleton($providerParams = array(
    ), $force = FALSE) {
    $providerID = CRM_Utils_Array::value('provider_id', $providerParams);
    $skipAuth   = $providerID ? FALSE : TRUE;
    $cacheKey   = (int) $providerID;

    if (!isset(self::$_singleton[$cacheKey]) || $force) {
      $provider = array();
      if ($providerID) {
        $provider = CRM_SMS_BAO_Provider::getProviderInfo($providerID);
      }
      self::$_singleton[$cacheKey] = new org_civicrm_sms_nhsmail($provider, $skipAuth);
    }
    return self::$_singleton[$cacheKey];
  }

  /**
   * Send an SMS Message via the NHS net email -> SMS gateway
   *
   * @param array the message with a to/from/text
   *
   * @return mixed true on sucess or PEAR_Error object
   * @access public
   */
  function send($recipient, $header, $message, $jobID = NULL) {
    if ($this->_apiType = 'smtp') {

#      drupal_set_message("We have been passed: <br /> Recipient: <pre>" . print_r($recipient,1) . "</pre><br /> Header: <pre>" . print_r($header,1) . "</pre><br /> Message: <pre>" . print_r($message,1) . "</pre><br /> Job ID: <pre>" . print_r($jobID,1) . "</pre><br />That is all.");  // DEBUGGING
      
#      drupal_set_message("Now we have: <br /> THIS: <pre>" . print_r($this->_providerInfo,1) . "</pre><br /><br />That is all.");  // DEBUGGING

      $config = CRM_Core_Config::singleton();

#      drupal_set_message("And there is always: <br /> CONFIG: <pre>" . print_r($config,1) . "</pre><br /><br />That is all.");  // DEBUGGING

      $recipient = str_replace(" ","",$recipient);

      $mail_content_file = $config->uploadDir . "text.txt";
      $msg_handle = fopen($mail_content_file,"w"); // Put the message into a temporary text file for including in the command line.
      fwrite($msg_handle, "From: Leicester Cardiovascular BRU <lcbru@nhs.net>\r\n");
      fwrite($msg_handle, "Subject: " . $message . "\r\n\r\n.\r\n"); // The SMS must be the subject not the body of the email.
      fclose($msg_handle);

      $command = "/usr/bin/curl " . $this->_providerInfo['api_params']['0'] . " " . $this->_providerInfo['api_url'] . " --mail-from '" . $this->_providerInfo['username'] . "' --crlf  --ssl-reqd --mail-rcpt '$recipient@sms.nhs.net' -u " . $this->_providerInfo['username'] . ":" . $this->_providerInfo['password'] . " -T $mail_content_file 2> /dev/null"; //  Took out the '2> /dev/null' bit at the end for testing purposes, and added back in a -v.
      exec($command,$output);

      unlink($config->uploadDir . "text.txt");  // Useful not to delete this while we are making it work

#      drupal_set_message("We told cURL to " . print_r($command,1));  // DEBUGGING
#      drupal_set_message("cURL responded with " . print_r($output,1));  // DEBUGGING


    }
    else {
    // TODO: If the api type is not smtp then what are we supposed to do?
    
    }
    
    // TODO: Any post-processing? What about returns and responses?
  }


  /**
   * Perform curl stuff - there was a really nice cURL function here, but it has gone, we can't use it
   *
   * @param   string  URL to call
   * @param   string  HTTP Post Data
   *
   * @return  mixed   HTTP response body or PEAR Error Object
   * @access	private
   */
}
