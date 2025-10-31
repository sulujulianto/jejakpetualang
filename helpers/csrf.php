<?php
/**
 * Utility functions for CSRF protection across the application.
 */
if (!function_exists('ensure_session_started')) {
    /**
     * Ensure that a PHP session is active without altering the current session name.
     */
    function ensure_session_started(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get or create the CSRF token for the active session.
     */
    function csrf_token(): string
    {
        ensure_session_started();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Render the hidden CSRF token input field.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('require_valid_csrf_token')) {
    /**
     * Abort the request when the supplied CSRF token is missing or invalid.
     */
    function require_valid_csrf_token(): void
    {
        ensure_session_started();

        $token = $_POST['csrf_token'] ?? '';
        $expected = $_SESSION['csrf_token'] ?? '';

        if (!hash_equals($expected, $token)) {
            http_response_code(419);
            die('Token CSRF tidak valid. Silakan muat ulang halaman dan coba lagi.');
        }
    }
}
