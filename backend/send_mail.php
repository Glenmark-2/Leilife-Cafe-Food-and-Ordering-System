<?php
// backend/send_mail.php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\MailService;

function sendVerificationEmail(string $toEmail, string $token): bool
{
    return (new MailService())->sendVerificationEmail($toEmail, $token);
}

function sendResetLink(string $toEmail, string $token, string $link): bool
{
    return (new MailService())->sendResetLink($toEmail, $token, $link);
}

function sendOTP(string $toEmail, string $otp): bool
{
    return (new MailService())->sendOTP($toEmail, $otp);
}

function generateOTP(): string
{
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
