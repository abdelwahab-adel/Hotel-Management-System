<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [], 'layouts.auth');
    }

    public function login(): string
    {
        $username = $this->input('username', '');
        $password = $this->input('password', '');

        $result = Auth::attempt($username, (string) $password);

        if ($result === 'locked') {
            Session::flash('error', 'Too many failed login attempts. Please try again in a few minutes.');
            return $this->redirect('/login');
        }

        if ($result !== true) {
            Session::flash('error', 'Invalid username/email or password.');
            Session::flash('_old', ['username' => $username]);
            return $this->redirect('/login');
        }

        log_activity('login', 'User logged in');

        $redirectTo = Auth::hasRole(['super_admin', 'admin', 'receptionist']) ? '/admin' : '/dashboard';
        return $this->redirect($redirectTo);
    }

    public function showRegister(): string
    {
        return $this->view('auth.register', [], 'layouts.auth');
    }

    public function register(): string
    {
        $data = [
            'full_name' => $this->input('full_name', ''),
            'username'  => $this->input('username', ''),
            'email'     => $this->input('email', ''),
            'phone'     => $this->input('phone', ''),
            'password'  => (string) $this->input('password', ''),
            'password_confirmation' => (string) $this->input('password_confirmation', ''),
        ];

        $v = new Validator($data);
        $v->required('full_name', 'Full name')->maxLength('full_name', 150, 'Full name')
          ->required('username', 'Username')->alphaDash('username', 'Username')->minLength('username', 3, 'Username')->maxLength('username', 50, 'Username')
          ->required('email', 'Email')->email('email')
          ->required('password', 'Password')->minLength('password', 8, 'Password')
          ->confirmed('password', 'password_confirmation', 'Password')
          ->phone('phone');

        $errors = $v->errors();
        if ($data['username'] !== '' && $data['email'] !== '' && User::usernameOrEmailExists($data['username'], $data['email'])) {
            $errors['username'][] = 'That username or email is already registered.';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('_old', $data);
            return $this->redirect('/register');
        }

        User::create([
            'uuid'          => uuid4(),
            'full_name'     => $data['full_name'],
            'username'      => $data['username'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?: null,
            'password_hash' => Auth::hashPassword($data['password']),
            'role'          => 'customer',
        ]);

        Session::flash('success', 'Account created successfully. Please log in.');
        return $this->redirect('/login');
    }

    public function logout(): string
    {
        log_activity('logout', 'User logged out');
        Auth::logout();
        return $this->redirect('/login');
    }

    public function showForgotPassword(): string
    {
        return $this->view('auth.forgot-password', [], 'layouts.auth');
    }

    public function sendResetLink(): string
    {
        $email = $this->input('email', '');
        $user = Database::one('SELECT * FROM users WHERE email = :e', ['e' => $email]);

        // Always show the same message whether or not the email exists,
        // to avoid leaking which emails are registered (user enumeration).
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::query(
                'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:u, :t, :e)',
                ['u' => $user['id'], 't' => hash('sha256', $token), 'e' => date('Y-m-d H:i:s', time() + 3600)]
            );

            (new \App\Services\MailService())->send(
                $user['email'],
                'Password Reset — ' . setting('hotel_name', 'The Pacific Hotel'),
                "Hi {$user['full_name']},\n\nUse this link to reset your password (valid for 1 hour):\n" .
                url('/reset-password/' . $token) . "\n\nIf you did not request this, ignore this email."
            );
        }

        Session::flash('success', 'If that email is registered, a reset link has been sent.');
        return $this->redirect('/forgot-password');
    }

    public function showResetPassword(string $token): string
    {
        return $this->view('auth.reset-password', ['token' => $token], 'layouts.auth');
    }

    public function resetPassword(): string
    {
        $token = $this->input('token', '');
        $password = (string) $this->input('password', '');
        $confirmation = (string) $this->input('password_confirmation', '');

        $v = new Validator(['password' => $password, 'password_confirmation' => $confirmation]);
        $v->required('password', 'Password')->minLength('password', 8, 'Password')
          ->confirmed('password', 'password_confirmation', 'Password');

        if ($v->fails()) {
            Session::flash('error', 'Please choose a password with at least 8 characters that matches its confirmation.');
            return $this->redirect('/reset-password/' . $token);
        }

        $reset = Database::one(
            'SELECT * FROM password_resets WHERE token_hash = :t AND used_at IS NULL AND expires_at > NOW()',
            ['t' => hash('sha256', $token)]
        );

        if (!$reset) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            return $this->redirect('/forgot-password');
        }

        Database::query('UPDATE users SET password_hash = :p WHERE id = :id', [
            'p' => Auth::hashPassword($password),
            'id' => $reset['user_id'],
        ]);
        Database::query('UPDATE password_resets SET used_at = NOW() WHERE id = :id', ['id' => $reset['id']]);

        Session::flash('success', 'Your password has been reset. Please log in.');
        return $this->redirect('/login');
    }
}
