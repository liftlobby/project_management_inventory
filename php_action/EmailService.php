<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'security_utils.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private static function getMailer() {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SecurityUtils::getConfig('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = SecurityUtils::getConfig('SMTP_USERNAME');
            $mail->Password = SecurityUtils::getConfig('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SecurityUtils::getConfig('SMTP_PORT');
            $mail->setFrom(
                SecurityUtils::getConfig('SMTP_FROM_EMAIL'),
                SecurityUtils::getConfig('SMTP_FROM_NAME')
            );
            
            error_log("SMTP Configuration: " . json_encode([
                'Host' => $mail->Host,
                'Port' => $mail->Port,
                'Username' => $mail->Username,
                'FromEmail' => SecurityUtils::getConfig('SMTP_FROM_EMAIL'),
                'FromName' => SecurityUtils::getConfig('SMTP_FROM_NAME')
            ]));
            
            return $mail;
        } catch (Exception $e) {
            error_log("Failed to create mailer: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function sendMFACode($email, $code) {
        try {
            error_log("Attempting to send MFA code to: " . $email);
            
            $mail = self::getMailer();
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your MFA Code for Inventory System';
            $mail->Body = "
                <h2>Your Authentication Code</h2>
                <p>Here is your MFA code: <strong>{$code}</strong></p>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            ";
            
            $result = $mail->send();
            error_log("Email sent successfully");
            return $result;
        } catch (Exception $e) {
            error_log("Failed to send MFA email: " . $e->getMessage());
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
