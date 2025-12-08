<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Config\EnvLoader;

class MailService
{
    private function setupMailer(): PHPMailer
    {
        // Load env if not loaded (or rely on superglobal if loaded earlier)
         if (empty($_ENV['MAIL_HOST'])) {
             // Fallback or load
             $envPath = __DIR__ . '/../../.env';
             if (file_exists($envPath)) {
                 EnvLoader::load($envPath);
             }
         }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;
        $mail->setFrom($_ENV['MAIL_FROM'] ?? $_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME'] ?? 'Leilife Cafe');
        
        if (!empty($_ENV['MAIL_REPLYTO'])) {
            $mail->addReplyTo($_ENV['MAIL_REPLYTO']);
        }
        $mail->isHTML(true);
        return $mail;
    }

    public function sendVerificationEmail(string $toEmail, string $token): bool
    {
        try {
            $mail = $this->setupMailer();
            $mail->addAddress($toEmail);
            $mail->Subject = 'Verify your Leilife account';
            // Assuming this is running on the same domain, or we need a config for base URL.
            // Using logic from original file: $_SERVER['HTTP_ORIGIN']
            // If running in background, $_SERVER might not have HTTP_ORIGIN.
            // Better to use a config value for APP_URL.
            $baseUrl = $_ENV['APP_URL'] ?? ($_SERVER['HTTP_ORIGIN'] ?? 'http://localhost/Leilife');
            
            $verifyLink = $baseUrl . "/public/index.php?page=verify&token=" . urlencode($token);

            $mail->Body = "
                <p>Hello!</p>
                <p>Please click the button below to verify your email:</p>
                <p>
                    <a href='{$verifyLink}' target='_blank'
                       style='display:inline-block;padding:10px 20px;background:#28a745;
                              color:#fff;text-decoration:none;border-radius:5px;'>
                       Verify Email
                    </a>
                </p>
                <p>Thank you!</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Verification Mail Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendResetLink(string $toEmail, string $token, string $link): bool
    {
        try {
            $mail = $this->setupMailer();
            $mail->addAddress($toEmail);
            $mail->Subject = 'Reset your Leilife password';

            $mail->Body = "
                <p>Hello!</p>
                <p>Please click the button below to reset your password:</p>
                <p>
                    <a href='{$link}' target='_blank'
                       style='display:inline-block;padding:10px 20px;background:#007bff;
                              color:#fff;text-decoration:none;border-radius:5px;'>
                       Reset Password
                    </a>
                </p>
                <p>Thank you!</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Reset Mail Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendOTP(string $toEmail, string $otp): bool
    {
        try {
            $mail = $this->setupMailer();
            $mail->addAddress($toEmail);
            $mail->Subject = 'Your Leilife OTP Code';

            $mail->Body = "
                <p>Hello!</p>
                <p>Your OTP code for verification is:</p>
                <p style='font-size:24px;font-weight:bold;letter-spacing:5px;color:#08284f;'>{$otp}</p>
                <p>This code will expire in 5 minutes.</p>
                <p>Thank you!</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('OTP Mail Error: ' . $e->getMessage());
            return false;
        }
    }
}
