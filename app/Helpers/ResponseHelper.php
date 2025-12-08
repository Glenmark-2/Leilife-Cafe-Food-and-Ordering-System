<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function wantsJson(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }
        return false;
    }

    public static function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public static function respond(bool $success, array $errors = [], ?string $redirect = null, string $failRedirect = '/Leilife/public/index.php?page=signUp'): void
    {
        if (self::wantsJson()) {
            if ($success) {
                self::jsonResponse(['success' => true, 'redirect' => $redirect]);
            } else {
                self::jsonResponse(['success' => false, 'errors' => $errors], 400);
            }
        } else {
             if (!$success) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['signup_errors'] = $errors; // Context specific key.. keeping logic generic
                header("Location: " . $failRedirect);
            } else {
                header("Location: " . $redirect);
            }
            exit;
        }
    }

    public static function sendImmediateSuccessAndContinue(?string $redirect = null): void
    {
        if (self::wantsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            header("Location: " . $redirect);
        }

        ignore_user_abort(true);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @flush();
        }
    }
}
