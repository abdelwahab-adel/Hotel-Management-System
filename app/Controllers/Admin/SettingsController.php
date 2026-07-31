<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Validator;
use App\Models\ActivityLog;
use App\Models\User;

final class SettingsController extends Controller
{
    public function index(): string
    {
        $settings = Database::all('SELECT * FROM settings');
        $map = [];
        foreach ($settings as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }

        return $this->view('admin.settings', ['settings' => $map], 'layouts.admin');
    }

    public function update(): string
    {
        $keys = ['hotel_name', 'currency_symbol', 'tax_rate_percent', 'contact_notify_email', 'contact_phone', 'contact_address'];
        foreach ($keys as $key) {
            $value = $this->input($key, '');
            $exists = Database::one('SELECT setting_key FROM settings WHERE setting_key = :k', ['k' => $key]);
            if ($exists) {
                Database::query('UPDATE settings SET setting_value = :v WHERE setting_key = :k', ['v' => $value, 'k' => $key]);
            } else {
                Database::query('INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)', ['k' => $key, 'v' => $value]);
            }
        }

        log_activity('settings_updated', 'Site settings changed');
        Session::flash('success', 'Settings saved.');
        return $this->redirect('/admin/settings');
    }

    public function staff(): string
    {
        $staff = Database::all("SELECT * FROM users WHERE role IN ('super_admin','admin','receptionist') ORDER BY role");
        return $this->view('admin.staff', ['staff' => $staff], 'layouts.admin');
    }

    public function storeStaff(): string
    {
        $data = [
            'full_name' => $this->input('full_name', ''),
            'username'  => $this->input('username', ''),
            'email'     => $this->input('email', ''),
            'role'      => $this->input('role', 'receptionist'),
        ];
        $password = (string) $this->input('password', '');

        $v = new Validator($data);
        $v->required('full_name', 'Full name')
          ->required('username', 'Username')->alphaDash('username', 'Username')
          ->required('email', 'Email')->email('email')
          ->in('role', ['admin', 'receptionist'], 'Role');

        if ($v->fails() || strlen($password) < 8 || User::usernameOrEmailExists($data['username'], $data['email'])) {
            Session::flash('error', 'Please check the staff form — username/email may already exist, or the password is too short (8+ characters).');
            return $this->redirect('/admin/staff');
        }

        $data['uuid'] = uuid4();
        $data['password_hash'] = Auth::hashPassword($password);

        User::create($data);
        log_activity('staff_created', "{$data['username']} ({$data['role']})");
        Session::flash('success', 'Staff account created.');
        return $this->redirect('/admin/staff');
    }

    public function activityLogs(): string
    {
        return $this->view('admin.activity-logs', ['logs' => ActivityLog::recent(100)], 'layouts.admin');
    }
}
