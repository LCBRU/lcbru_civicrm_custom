<?php
class HscicLogger implements IHscicLogger
{
    private $messages = array();
    private $subject;

    public function __construct($subject) {
        $this->subject = $subject;
    }

    public function log($message) {
        $this->messages[] = $message;
    }

    private function getSummary() {
        return implode(PHP_EOL, $this->messages);
    }

    public function output() {
        $to = variable_get('lcbru_import_except_recipient_email', LCBRU_DEFAULT_EMAIL_RECIPIENT);
        MailHelper::send($to, $this->subject, $this->getSummary());
    }

}