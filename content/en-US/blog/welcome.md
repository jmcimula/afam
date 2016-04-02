---
date: 2016-02-19T20:35:29Z
draft: true
title: welcome
author: henry addo
---

Are you a Web developer? Get involved in our volunteer efforts by starting to use Firefox Developer Edition today!
Follow us

Social media: Follow us on Twitter, Facebook or Google+.

Feed: Subscribe the Atom feed of this site.

It is important to find and fix Firefox regressions before Firefox users visit your broken site. Mozilla is offering Firefox Developer Edition, formerly known as Aurora, so please use this alpha version on a daily basis to develop and test your site or Web app.
Report issues

Please report any regressions you encountered through one of the following ways:

Social media: Tweet us the URL of your problematic site. You can also send us a direct message via Twitter or Facebook.
GitHub: If you have a GitHub account, it might be easy to file a new issue on our repository.
Bugzilla: If you have Mozilla’s Bugzilla account and can recognize an appropriate component for your issue, file a new bug directly.
<pre class="prettyprint linenums">
/**
 * Gets the messages(SMSs) sent by SMSsync as a POST request.
 *
 */
function get_message()
{
    $error = NULL;
    // Set success to false as the default success status
    $success = false;
    /**
     *  Get the phone number that sent the SMS.
     */
    if (isset($_POST['from']))
    {
        $from = $_POST['from'];
    }
    else
    {
        $error = 'The from variable was not set';
    }
    /**
     * Get the SMS aka the message sent.
     */
    if (isset($_POST['message']))
    {
        $message = $_POST['message'];
    }
    else
    {
        $error = 'The message variable was not set';
    }
    /**
     * Get the secret key set on SMSsync side
     * for matching on the server side.
     */
    if (isset($_POST['secret']))
    {
        $secret = $_POST['secret'];
    }
    /**
     * Get the timestamp of the SMS
     */
    if(isset($_POST['sent_timestamp']))
    {
        $sent_timestamp = $_POST['sent_timestamp'];
    }
    /**
     * Get the phone number of the device SMSsync is
     * installed on.
     */
    if (isset($_POST['sent_to']))
    {
        $sent_to = $_POST['sent_to'];
    }
    /**
     * Get the unique message id
     */
    if (isset($_POST['message_id']))
    {
        $message_id = $_POST['message_id'];
    }
    /**
     * Get device ID
     */
    if (isset($_POST['device_id']))
    {
        $device_id = $_POST['device_id'];
    }
    /**
     * Now we have retrieved the data sent over by SMSsync
     * via HTTP. Next thing to do is to do something with
     * the data. Either echo it or write it to a file or even
     * store it in a database. This is entirely up to you.
     * After, return a JSON string back to SMSsync to know
     * if the web service received the message successfully or not.
     *
     * In this demo, we are just going to save the data
     * received into a text file.
     *
     */
    if ((strlen($from) > 0) AND (strlen($message) > 0) AND
        (strlen($sent_timestamp) > 0 )
        AND (strlen($message_id) > 0))
    {
        /* The screte key set here is 123456. Make sure you enter
         * that on SMSsync.
         */
        if ( ( $secret == '123456'))
        {
            $success = true;
        } else
        {
            $error = "The secret value sent from the device does not match the one on the server";
        }
        // now let's write the info sent by SMSsync
        //to a file called test.txt
        $string = "From: ".$from."\n";
        $string .= "Message: ".$message."\n";
        $string .= "Timestamp: ".$sent_timestamp."\n";
        $string .= "Messages Id:" .$message_id."\n";
        $string .= "Sent to: ".$sent_to."\n";
        $string .= "Device ID: ".$device_id."\n\n\n";
        write_message_to_file($string);
    }
    /**
     * Comment the code below out if you want to send an instant
     * reply as SMS to the user.
     *
     * This feature requires the "Get reply from server" checked on SMSsync.
     */
     send_instant_message($from);
    /**
      * Now send a JSON formatted string to SMSsync to
      * acknowledge that the web service received the message
      */
     $response = json_encode([
        "payload"=> [
            "success"=>$success,
                "error" => $error
            ]
        ]);
     //send_response($response);
}
</pre>