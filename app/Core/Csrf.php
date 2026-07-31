<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection. None of the original forms (login, booking, admin status
 * changes, room add/delete...) carried a token, so any external site could
 * submit those forms on a logged-in admin's behalf. Every state-changing
 * form in the rebuild includes csrf_field() and every POST route verifies it.
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            Session::set(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::SESSION_KEY);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(?string $token): bool
    {
        $expected = Session::get(self::SESSION_KEY);
        return is_string($token) && is_string($expected) && hash_equals($expected, $token);
    }

    /** Verifies the token from $_POST and aborts the request with 419 if invalid. */
    public static function verifyRequestOrFail(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!self::verify($token)) {
            http_response_code(419);
            Logger::warning('CSRF validation failed for ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
            echo view('errors/419', []);
            exit;
        }
    }
}
