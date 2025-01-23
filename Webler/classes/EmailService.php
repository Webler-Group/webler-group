<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Include the email configuration
require_once __DIR__ . '/../../config.php';

class EmailService {
    private $host;
    private $port;
    private $username;
    private $password;
    private $appName;
    private $outputCallback;

    public function __construct(callable $outputCallback = null) {
        global $CFG; // Access the global $CFG variable

        // Load the necessary configuration from global $CFG
        $this->host = $CFG->emailHost;
        $this->port = $CFG->emailPort;
        $this->username = $CFG->emailUser;
        $this->password = $CFG->emailPassword;
        $this->appName = $CFG->appName;

        // Set the output callback if provided
        $this->outputCallback = $outputCallback;
    }

    private function output($message) {
        if (is_callable($this->outputCallback)) {
            call_user_func($this->outputCallback, $message);
        }
    }

    public function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = 3;
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->port;

            // Recipients
            $mail->setFrom($this->username, $this->appName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            // Send the email
            $mail->send();
            $this->output("Message has been sent");
            return true;
        } catch (Exception $e) {
            $this->output("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}