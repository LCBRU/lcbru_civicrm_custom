<?php

/**
 * This class provides utility functions to simplify sending an email.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   MailHelper::send('to@email.com', 'Test', 'This is a test email')
 *
 * }
 * </code>
 * 
 * Things to note are:
 *
*/

class MailHelper
{
    /**
     * Get objects.
     *
     * @param string $to the recipient email address
     * @param string $ubject the email subject
     * @param string $message the email message
     * @param string $from the senders email address
     *
     * @return boolean - whether the emailing has succeeded
     * @throws Exception if $to is empty.
     * @throws Exception if $subject is empty.
     * @throws Exception if $message is empty.
     */
    public static function send($to, $subject, $message, $from = NULL) {
        Guard::AssertString_NotEmpty('$to', $to);
        Guard::AssertString_NotEmpty('$subject', $subject);
        Guard::AssertString_NotEmpty('$message', $message);

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <lcbruit@xuhl-tr.nhs.uk>' . "\r\n";

        watchdog('send', "To: $to");
        watchdog('send', "Subject: $subject");
        watchdog('send', "Message: $message");
        $resp = mail($to,$subject,$message,$headers);
        watchdog('send', "Resp: $resp");
    }

    public static function sendMarkDown($to, $subject, $message, $from = NULL) {
        Guard::AssertString_NotEmpty('$to', $to);
        Guard::AssertString_NotEmpty('$subject', $subject);
        Guard::AssertString_NotEmpty('$message', $message);

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <lcbruit_commondev@xuhl-tr.nhs.uk>' . "\r\n";

        $css = '
            <style>
                p {
                    margin: 0;
                }
            </style>
        ';

        $Parsedown = new Parsedown();

        $html = $css . $Parsedown->text($message);

        mail($to,$subject,$html,$headers);
    }
}
