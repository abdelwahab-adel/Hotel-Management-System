<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Authentication core.
 *
 * Security decisions vs. the original project:
 *   - Passwords are hashed with password_hash() (bcrypt) and verified with
 *     password_verify(). The original `signup` table stored raw passwords
 *     and validation.php compared them with `==` against $_POST directly.
 *   - Login is rate-limited per username+IP (see attemptIsAllowed) and the
 *     account is soft-locked after N failures, mitigating brute force.
 *   - Session ID is regenerated on login/logout to prevent session fixation.
 *   - The admin login was a second, separate hardcoded check
 *     (`success.php`, username "venky" / password "123"). There is now a
 *     single `users` table with a `role` column instead of two auth systems.
 */
final class Auth
{
    public static function attempt(string $username, string $password): bool|string
    {
        $identifier = $username . '|' . self::clientIp();

        if (!self::attemptIsAllowed($identifier)) {
            return 'locked';
        }

        $user = Database::one(
            'SELECT * FROM users WHERE (username = :u1 OR email = :u2) LIMIT 1',
            ['u1' => $username, 'u2' => $username]
        );

        self::recordAttempt($identifier);

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::clearAttempts($identifier);
        self::login($user);
        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['full_name']);
    }

    public static function logout(): void
    {
        Session::destroy();
        Session::start();
        Session::regenerate();
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id !== null ? (int) $id : null;
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function hasRole(array $roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Database::one('SELECT * FROM users WHERE id = :id', ['id' => self::id()]);
    }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    private static function clientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private static function attemptIsAllowed(string $identifier): bool
    {
        $security = Config::get('security');
        $windowStart = date('Y-m-d H:i:s', time() - ($security['login_lockout_minutes'] * 60));

        $count = (int) (Database::one(
            'SELECT COUNT(*) AS c FROM login_attempts WHERE identifier = :i AND attempted_at > :w',
            ['i' => $identifier, 'w' => $windowStart]
        )['c'] ?? 0);

        return $count < $security['login_max_attempts'];
    }

    private static function recordAttempt(string $identifier): void
    {
        Database::query('INSERT INTO login_attempts (identifier) VALUES (:i)', ['i' => $identifier]);
    }

    private static function clearAttempts(string $identifier): void
    {
        Database::query('DELETE FROM login_attempts WHERE identifier = :i', ['i' => $identifier]);
    }
}
