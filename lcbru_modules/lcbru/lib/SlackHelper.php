<?php

/**
 * This class provides utility functions for working with Slack.
 * 
 * Example usage:
 *
 * <code>
 *
 * function module_function() {
 *
 *   SlackHelper::sendtoHashData($message);
 *
 * }
 * </code>
 * 
*/

class SlackHelper
{
    const DATA_CHANNEL = '#Data';

    private static function getChannelUrl($channel) {
        Guard::AssertString_NotEmpty('$channel', $channel);

        $urls = [];
        $urls[SlackHelper::DATA_CHANNEL] = "https://hooks.slack.com/services/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";

        return $urls[$channel];
    }

    /**
     * @param string $message the message to send to slack
     */
    public static function send($channel, $message, $attachments = NULL) {
        Guard::AssertString_NotEmpty('$message', $message);
        Guard::AssertString_NotEmpty('$channel', $channel);

        $data = 'payload=' . json_encode(array(
                'text' => $message,
                "mrkdwn"=> true,
                'attachments' => $attachments
            ));
	
        $ch = curl_init(SlackHelper::getChannelUrl($channel));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
    }

    /**
     * @param string $message the message to send to slack
     */
    public static function sendWithAttachment($channel, $message, $attachment) {
        Guard::AssertString_NotEmpty('$channel', $channel);
        Guard::AssertString_NotEmpty('$message', $message);
        Guard::AssertString_NotEmpty('$attachment', $attachment);

        SlackHelper::send($channel, $message, array(array(
                'text' => str_replace("\r\n\r\n", "\r\n", str_replace('**', '*', $attachment)),
                'mrkdwn_in'=> array('text')
            ))); 

    }
}
