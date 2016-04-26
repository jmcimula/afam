<?php

    define(MAILGUN_API, "api.mailgun.net");
    function validate() {
        // Check for empty fields
        if (!empty($_POST['name'])           &&
                !empty($_POST['email'])      &&
                !empty($_POST['message'])    &&
                !empty($_POST['grecaptcha']) &&
                filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {

            // Send verify recapture
            $captcha_response = verify_recaptcha();
            if ($captcha_response !=1 ) {
                return false;
            }
            return true;
        } 
        return false;
    }

    function sanitize_input($data) {
        // Make sure all data are clean and safe
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function send_email($to = 'support@addhen.com') {
        if (validate()) {
            $name           =   sanitize_input($_POST['name']);
            $email_address  =   sanitize_input($_POST['email']);
            $message        =   sanitize_input($_POST['message']);

            // Create the email and send the message
            $email_subject  =   "Website Contact Form:  $name";
            $email_body     =   "You have received a new message from your website contact form.\n\n"."Here are the        
            details:\n\nName: $name\n\nEmail: $email_address\n\nPhone: $phone\n\nMessage:\n$message";
            $support_from        =   "$name <$email_address>\n";
            $to_customer_from = "Addhen Limited's support team <support@addhen.com>";
            $to_customer_subject        = "Thank You For Contacting Us";
            $to_customer_message = "Dear $name,\n\n This is a confirmation that your message has been received by our support team. Please expect a response within one business day.\n\n\n Kind regards,\nAddhen Support Team";
            // Send to addhen's support system.
            $status = send_email($to, $support_from, $subject, $email_body);
            if($status) {
                // Send confirmation email to sender
                return send_email($email_address, $to_customer_from, $to_customer_subject, $to_customer_message);
            }
            return $status;
        } else {
            // Invalid inputs
            return 'No arguments Provided!';
        }
    }

    /** Validates Google's recaptcha response to make sure user is  not a bot. */
    function verify_recaptcha() {
        $secret = "6LctIR4TAAAAAAtVNwdkI5_GN344uatmgIIxdp0A";
        $postdata = http_build_query(
            array(
                'secret' => $secret,
                'response' => $_POST["grecaptcha"]
            )
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

        $decoded_result = json_decode($result, true);
        return $decoded_result["success"];
    }

    /** Uses the mailgun API to send emails. It uses php-curl to interact with 
     * Mailgun's API.
     */
    function send_email($to, $from, $subject, $message) {
        $mailgun_api_key = "key-149101e346db487195c5ffebee34ddba";
        $mailgun = "api.mailgun.net";
        $domain = "mg.addhen.com";
        $version = "v3";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "api:$mailgun_api_key");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $plain = strip_tags(nl2br($message));

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_URL, "https://$mailgun/$version/$domain/messages");
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $message,
                'text' => $plain));

        $j = json_decode(curl_exec($ch));

        $info = curl_getinfo($ch);

        if($info['http_code'] != 200) {
            error("Failed to send email support@$domain");
        }
        curl_close($ch);
        return $j;
    }

    send_email();
?>