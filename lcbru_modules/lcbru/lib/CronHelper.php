<?php

/**
 * This class provides utility functions for Drupal cron tasks.
 * 
 * Example usage:
 *
 * <code>
 *
 * // Implementation of Drupal cron hook
 * function module_cron() {
 *
 *   $helper = new CronHelper('JobName');
 *   $helper->runCron(function() {
 *           // Do stuff here
 *       });
 * }
 * </code>
 * 
 * Things to note are:
 *
 *   + The jobName parameter cannot be NULL or empty.
 *   + The runCron function takes either an anonymous function or a
 *     function name as it's argument.
*/

class CronHelper
{
/**
 * Frequencies that the cron job can run
 */
    const FREQUENCY_EVERY_CRON = 'every cron';
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    private $originalUser;
    private $jobName;
    private $jobTitle;
    private $lastRunVariableName = NULL;
    private $defaultEnabled;
    private $defaultFrequency;

    /**
     * Constructor.
     *
     * @param string $jobName name for the job being run.
     *        used to make name for variable that stores the last run date.
     * @param boolean $defaultEnabled optional default value for enabled setting
     * @param boolean $defaultFrequency optional default value for frequency setting
     */
     public function __construct($jobName, $defaultEnabled = false, $defaultFrequency = Self::FREQUENCY_WEEKLY) {
        if (!isset($jobName) || trim($jobName)==='') {
            throw new InvalidArgumentException("CronHelper cannot be instantiated without a job name.");
        }

        $this->jobName = str_replace(' ', '_', $jobName); # Fields do not work if name has spaces in it
        $this->jobTitle = str_replace('_', ' ', $jobName);
        $this->defaultEnabled = $defaultEnabled;
        $this->defaultFrequency = $defaultFrequency;
        $this->lastRunVariableName = "CRON_HELPER_LAST_RUN_{$jobName}";
    }

    /**
     * Function that should be called before all processing of the cron
     * job occurs.  It initialises CiviCRM and switches the current user
     * to be the Cron System user.
     *
     * @return void
     * @throws Exception if the cronsystem user cannot be found.
     */
    public function start() {
        if (!is_null($this->originalUser)) {
            return;
        }

        civicrm_initialize();

        $cronSystemUser = $this->getCronUser();

        $this->originalUser = $GLOBALS['user'];
        $GLOBALS['user'] = $cronSystemUser;
    }

    /**
     * Function that should be called after all processing of the cron
     * job has occurred.  It saves the date the job was run and switches
     * the current user back to the one being used before the job started.
     *
     * @return void
     */
    public function end() {
        if (!is_null($this->lastRunVariableName)) {
            variable_set($this->lastRunVariableName, date_create("now")->format('d-M-Y H:i:s'));
        }

        if (is_null($this->originalUser)) {
            return;
        }

        $GLOBALS['user'] = $this->originalUser;
        $this->originalUser = null;
    }

    /**
     * Destructor
     *
     * @return void
     * @throws Exception if end() hasn't been called after a start().
     */
    public function __destruct() {
        if (!is_null($this->originalUser)) {
            throw new Exception("CronHelper->end() not called", 1);    
        }
    }

    /**
     * Returns whether the cron job is due to run again.  This function
     * is dependent upon the jobName and the frequency.  If these were
     * not passed to the constructor, the function always returns false.
     *
     * @return boolean
     */
    public function is_due() {

        if (is_null($this->lastRunVariableName)) {
            return false;
        }

        $runDue = new DateTime(variable_get($this->lastRunVariableName, '01-Jan-1900'));

        switch ($this->getFrequency()) {
            case Self::FREQUENCY_HOURLY:
                $runDue->add(new DateInterval('PT1H'));
                break;
            case Self::FREQUENCY_DAILY:
                $runDue->add(new DateInterval('P1D'));
                break;
            case Self::FREQUENCY_WEEKLY:
                $runDue->add(new DateInterval('P7D'));
                break;
            case Self::FREQUENCY_MONTHLY:
                $runDue->add(new DateInterval('P1M'));
                break;
            case Self::FREQUENCY_EVERY_CRON:
                return true;
                break;
        }

        return $runDue < date_create("now");
    }

    /**
     * Get the name of the enabled variable
     *
     * @return string
     */
    public function getEnabledVariableName() {
        return "CRON_HELPER_ENABLED_{$this->jobName}";
    }

    /**
     * Get the job enabled status
     *
     * @return string
     */
    public function getEnabled() {
        return variable_get($this->getEnabledVariableName(), $this->defaultEnabled);
    }

    /**
     * Get the name of the frequncy variable
     *
     * @return string
     */
    public function getFrequencyVariableName() {
        return "CRON_HELPER_FREQUENCY_{$this->jobName}";
    }

    /**
     * Get the job frequency
     *
     * @return string
     */
    public function getFrequency() {
        return variable_get($this->getFrequencyVariableName(), $this->defaultFrequency);
    }

    /**
     * Utility function that returns an associative map of frequencies to be
     * used as the options for a frequency select control of a config screen.
     *
     * @return array
     */
    public function frequencyOptions() {
        return drupal_map_assoc(
            array(
                Self::FREQUENCY_EVERY_CRON,
                Self::FREQUENCY_HOURLY,
                Self::FREQUENCY_DAILY,
                Self::FREQUENCY_WEEKLY,
                Self::FREQUENCY_MONTHLY
                )
            );
    }

    /**
     * Utility function to add settings to a config form
     *
     * @param form $form Drupal config form.
     *
     */
    public function addSettingsToForm(&$form) {
        $form[$this->getEnabledVariableName()] = array(
            '#type' => 'checkbox',
            '#title' => t("Cron process for {$this->jobTitle} enabled"),
            '#default_value' => $this->getEnabled(),
        );

        $form[$this->getFrequencyVariableName()] = array(
            '#type' => 'select',
            '#title' => t("Frequency of cron process for {$this->jobTitle}."),
            '#default_value' => $this->getFrequency(),
            '#options' => $this->frequencyOptions(),
        );

    }

    /**
     * Utility function to run an anonymous function as a cron job
     *
     * @param Closure $cronFunction anonymous function to run as cron.
     *
     */
    public function runCron($cronFunction) {
        if (!((is_string($cronFunction) && function_exists($cronFunction)) || (is_object($cronFunction) && ($cronFunction instanceof Closure)))) {
            throw new InvalidArgumentException("CronHelper->runCron must be passed a function.");
        }

        if (!$this->getEnabled()) {
            watchdog($this->jobTitle, 'Not enabled');
            return;
        }

        if (!$this->is_due()) {
            watchdog($this->jobTitle, 'Not due');
            return;
        }

        watchdog($this->jobTitle, 'Started');

        $this->start();
        
        try {

            $cronFunction();
            
        } catch (Exception $ex) {
            throw $ex;
        } finally {
            $this->end();
        }
        
        watchdog($this->jobTitle, 'Completed');
    }

    public function getCronUser() {
        $result = user_load_by_name('cronsystem');

        if (empty($result)) {
            throw new Exception("cronsystem user not found", 1);
        }

        return $result;
    }

    public function getCronUserContactId() {
        $ch = new ContactHelper();

        $cronSystemUser = $this->getCronUser();
        return $ch->getContactIdForDrupalUserId($cronSystemUser->uid);
    }
}
