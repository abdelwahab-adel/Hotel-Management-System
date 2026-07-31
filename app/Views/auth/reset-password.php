<?php $pageTitle = 'Reset Password'; ?>
<h1 class="font-display text-2xl font-bold mb-1">Choose a new password</h1>
<p class="text-sm text-muted mb-6">Must be at least 8 characters.</p>

<form method="POST" action="<?= url('/reset-password') ?>" class="space-y-4">
  <?= $csrf_field ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <div>
    <label class="form-label">New Password</label>
    <input type="password" name="password" class="form-input" required>
  </div>
  <div>
    <label class="form-label">Confirm New Password</label>
    <input type="password" name="password_confirmation" class="form-input" required>
  </div>
  <button type="submit" class="btn btn-gold w-full">Reset Password</button>
</form>
