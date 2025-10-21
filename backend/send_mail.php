<?php
// backend/send_mail.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require __DIR__ . '/../phpmailer-master/src/Exception.php';
require __DIR__ . '/../phpmailer-master/src/PHPMailer.php';
require __DIR__ . '/../phpmailer-master/src/SMTP.php';

// ✅ Include your custom env loader
require_once __DIR__ . '/db_script/env.php';

// Load environment variables
try {
    loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    error_log("Env Load Error: " . $e->getMessage());
}

/**
 * Setup and return a PHPMailer instance
 */
function setupMailer(): PHPMailer
{
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

/**
 * Send a verification email
 */
function sendVerificationEmail(string $toEmail, string $token): bool
{
    try {
        $mail = setupMailer();
        $mail->addAddress($toEmail);
        $mail->Subject = 'Verify your Leilife account';

        $verifyLink = "http://localhost/Leilife/public/index.php?page=verify&token=" . urlencode($token);

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

/**
 * Send a reset password link
 */
function sendResetLink(string $toEmail, string $token, string $link): bool
{
    try {
        $mail = setupMailer();
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

/**
 * Send an OTP code
 */
function sendOTP(string $toEmail, string $otp): bool
{
    try {
        $mail = setupMailer();
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

/**
 * Generate a 6-digit OTP
 */
function generateOTP(): string
{
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
