<?php $pageTitle = 'Sign In'; ?>
<h1 class="font-display text-2xl font-bold mb-1">Welcome back</h1>
<p class="text-sm text-muted mb-6">Sign in to manage your bookings.</p>

<form method="POST" action="<?= url('/login') ?>" class="space-y-4">
  <?= $csrf_field ?>
  <div>
    <label class="form-label">Username or Email</label>
    <input type="text" name="username" value="<?= old('username') ?>" class="form-input" required autofocus>
  </div>
  <div>
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-input" required>
  </div>
  <div class="flex justify-end">
    <a href="<?= url('/forgot-password') ?>" class="text-xs text-gold hover:underline">Forgot password?</a>
  </div>
  <button type="submit" class="btn btn-gold w-full">Sign In</button>
</form>

<p class="text-center text-sm text-muted mt-6">Don't have an account? <a href="<?= url('/register') ?>" class="text-gold font-semibold hover:underline">Create one</a></p>

<div class="mt-6 pt-6 border-t border-app text-xs text-muted">
  <p class="font-semibold mb-1">Demo accounts</p>
  <p>Admin: <code>admin</code> / <code>Admin@12345</code></p>
  <p>Receptionist: <code>reception</code> / <code>Reception@12345</code></p>
  <p>Customer: <code>customer</code> / <code>Customer@12345</code></p>
</div>
