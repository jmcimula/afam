<?php
function validate()
{
    // Check for empty fields
    if(empty($_POST['name'])            ||
            empty($_POST['email'])      ||
            empty($_POST['phone'])      ||
            empty($_POST['message'])    ||
            !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)) {

            return false;
       } else {
            return true;
       }
}

function send_email($to = 'support@addhen.com')
    {
        if(Validate() == true) {
            $name           =   $_POST['name'];
            $email_address  =   $_POST['email'];
            $phone          =   $_POST['phone'];
            $message        =   $_POST['message'];

            // Create the email and send the message
            $email_subject  =   "Website Contact Form:  $name";
            $email_body     =   "You have received a new message from your website contact form.\n\n"."Here are the        
            details:\n\nName: $name\n\nEmail: $email_address\n\nPhone: $phone\n\nMessage:\n$message";
            $headers        =   "From: noreply@addhen.com\n";
            $headers        .= "Reply-To: $email_address";
            // Send true on successful send.
            // Send false if failed
            return (mail($to,$email_subject,$email_body,$headers))? true: false;
        } else {
            // Invalid inputs
            return 'No arguments Provided!';
        }
}

send_email();
?>