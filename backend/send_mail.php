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
function sendResetLink(string $toEmail, string $token): bool {
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kenna.cremin@ethereal.email';
        $mail->Password   = 'Cd2eZVhfHPJfrX6ZHt';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // From & To
        $mail->setFrom('noreply@leilife.com', 'Leilife Cafe');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset password';

        // <-- changed to route through public/index.php?page=verify
        $verifyLink = "http://localhost/Leilife/public/index.php?page=forgot-password&token=" . urlencode($token);

        $mail->Body = "Hello!<br><br>
                       Please click the button below to reset your password:<br><br>
                       <a href='{$verifyLink}' target='_blank' style='display:inline-block;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:5px;'>Verify Email</a><br><br>
                       Thank you!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function sendVerificationEmail(string $toEmail, string $token): bool {
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.ethereal.email';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kenna.cremin@ethereal.email';
        $mail->Password   = 'Cd2eZVhfHPJfrX6ZHt';
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



