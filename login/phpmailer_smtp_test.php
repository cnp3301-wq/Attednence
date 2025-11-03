<?php
// PHPMailer SMTP debug test
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

function smtpTest($to) {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // verbose debug output
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'PHPMailer SMTP Test';
        $mail->Body    = '<p>This is a test email from PHPMailer SMTP test script.</p>';

        $mail->send();
        echo "Mail sent successfully to $to\n";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
    }
}

if (php_sapi_name() === 'cli') {
    $recipient = $argv[1] ?? SMTP_USERNAME;
    smtpTest($recipient);
} else {
    $to = $_GET['to'] ?? SMTP_USERNAME;
    echo '<pre>';
    smtpTest($to);
    echo '</pre>';
}
?>
