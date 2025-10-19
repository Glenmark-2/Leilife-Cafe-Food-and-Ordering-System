<?php
// backend/send_mail.php

// Include PHPMailer files
require __DIR__ . '/../phpmailer-master/src/Exception.php';
require __DIR__ . '/../phpmailer-master/src/PHPMailer.php';
require __DIR__ . '/../phpmailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send a verification email with a token link.
 *
 * @param string $toEmail Recipient email
 * @param string $token Verification token
 * @return bool True if sent, false on error
 */
function sendResetLink(string $toEmail, string $token, string $link): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'claire62@ethereal.email';
        $mail->Password   = 'w8UGhWtGjEU4ky4bCQ';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply@leilife.com', 'Leilife Cafe');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Reset password';

        $mail->Body = "Hello!<br><br>
                       Please click the button below to reset your password:<br><br>
                       <a href='{$link}' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:5px;'>Verify Email</a><br><br>
                       Thank you!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}




function sendVerificationEmail(string $toEmail, string $token): bool
{
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'claire62@ethereal.email';
        $mail->Password   = 'w8UGhWtGjEU4ky4bCQ';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // From & To
        $mail->setFrom('noreply@leilife.com', 'Leilife Cafe');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your Leilife account';

        // <-- changed to route through public/index.php?page=verify
        $verifyLink = "http://localhost/Leilife/public/index.php?page=verify&token=" . urlencode($token);

        $mail->Body = "Hello!<br><br>
               Please click the button below to verify your email:<br><br>
               <a href='{$verifyLink}' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:5px;'>Verify Email</a><br><br>
               Thank you!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}



/**
 * Generate a secure 6-digit OTP
 */
function generateOTP(): string
{
    return str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
}

/**
 * Send OTP to email
 *
 * @param string $toEmail Recipient email
 * @param string $otp 6-digit code
 * @return bool
 */
function sendOTP(string $toEmail, string $otp): bool
{
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'claire62@ethereal.email';
        $mail->Password   = 'w8UGhWtGjEU4ky4bCQ';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // From & To
        $mail->setFrom('noreply@leilife.com', 'Leilife Cafe');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Leilife OTP Code';

        $mail->Body = "Hello!<br><br>
            Your OTP code for email verification is:<br><br>
            <div style='font-size:24px;font-weight:bold;letter-spacing:5px;color:#08284f;'>{$otp}</div><br>
            This code will expire in 5 minutes.<br><br>
            Thank you!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
