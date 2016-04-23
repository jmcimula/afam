<?php
    $secret = "6LctIR4TAAAAAAtVNwdkI5_GN344uatmgIIxdp0A";
    $sitekey = "6LctIR4TAAAAAGn68rUkAH1N64o2cVrGV3YAhkzK";

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

    function validate() {
        // Check for empty fields
        print_r($opts);
        if (!empty($_POST['name'])           &&
                !empty($_POST['email'])      &&
                !empty($_POST['message'])    &&
                !empty($_POST['g-recaptcha-response']) &&
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
            $phone          =   sanitize_input($_POST['phone']);
            $message        =   sanitize_input($_POST['message']);

            // Create the email and send the message
            $email_subject  =   "Website Contact Form:  $name";
            $email_body     =   "You have received a new message from your website contact form.\n\n"."Here are the        
            details:\n\nName: $name\n\nEmail: $email_address\n\nPhone: $phone\n\nMessage:\n$message";
            $headers        =   "From: noreply@addhen.com\n";
            $headers        .= "Reply-To: $email_address";
            // Send true on successful send.
            // Send false if failed
            return (mail($to,$email_subject,wordwrap($email_body,80),$headers))? true: false;
        } else {
            // Invalid inputs
            return 'No arguments Provided!';
        }
    }

    function verify_recaptcha() {
        $context = stream_context_create($opts);
        $result = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

        $decoded_result = json_decode($result, true);
        return $decoded_result["success"];
    }

    send_email();
?>