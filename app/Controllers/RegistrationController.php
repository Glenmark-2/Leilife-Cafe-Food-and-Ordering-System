<?php

namespace App\Controllers;

use App\Services\RegistrationService;
use App\Services\MailService;
use App\Helpers\ResponseHelper;

class RegistrationController
{
    private RegistrationService $regService;
    private MailService $mailService;

    public function __construct()
    {
        // session_start should be called by entry point
        $this->regService = new RegistrationService();
        $this->mailService = new MailService();
    }

    public function handleSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHelper::respond(false, ["Invalid request method."]);
        }

        // CSRF
        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            ResponseHelper::respond(false, ["Security check failed. Please try again."]);
        }

        // Inputs
        $fname    = trim($_POST['fname'] ?? '');
        $lname    = trim($_POST['lname'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone_number'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $terms    = isset($_POST['terms']);

        // Validation
        $errors = [];
        if (!$terms) $errors[] = "You must accept the Terms & Conditions.";
        if (empty($fname) || empty($lname) || empty($email) || empty($password)) $errors[] = "Please fill in all required fields.";
        if (strlen($fname) > 100 || strlen($lname) > 100) $errors[] = "Name too long (max 100 characters).";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
        if (!preg_match('/^\+?\d{7,15}$/', $phone)) $errors[] = "Invalid phone number format.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";
        if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters long.";

        if (!empty($errors)) {
            // Set session generic error key for non-json
            if (!ResponseHelper::wantsJson()) {
                $_SESSION['signup_errors'] = $errors;
            }
            ResponseHelper::respond(false, $errors);
        }

        try {
            $input = [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'phone_number' => $phone,
                'password' => $password
            ];

            $result = $this->regService->registerUser($input);

            if (!$result['success']) {
                if (!ResponseHelper::wantsJson()) {
                    $_SESSION['signup_errors'] = $result['errors'];
                }
                ResponseHelper::respond(false, $result['errors']);
            }

            // Success
            ResponseHelper::sendImmediateSuccessAndContinue($result['redirect']);

            // Background Work
            if (!$this->mailService->sendVerificationEmail($email, $result['token'])) {
                 error_log("Signup saved but verification email could not be sent to $email");
            }

            exit;

        } catch (\Exception $e) {
            error_log("Signup Controller Error: " . $e->getMessage());
            ResponseHelper::respond(false, ["Server error. Please try again later."]);
        }
    }
}
