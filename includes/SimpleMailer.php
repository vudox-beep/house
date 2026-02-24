<?php
class SimpleMailer {
    public function send($to, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . SITE_NAME . ' <no-reply@luxestay.com>' . "\r\n";

        // Use PHP's built-in mail() function
        // Note: This requires a configured SMTP server in php.ini (e.g., Sendmail, Postfix, or MailHog)
        // For local development (XAMPP), mail() might fail without configuration.
        // But since we can't install PHPMailer via Composer here, this is the standard fallback.
        
        return mail($to, $subject, $message, $headers);
    }
}
?>