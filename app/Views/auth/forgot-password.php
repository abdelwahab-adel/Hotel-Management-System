<?php $pageTitle = 'Forgot Password'; ?>
<h1 class="font-display text-2xl font-bold mb-1">Reset your password</h1>
<p class="text-sm text-muted mb-6">Enter your email and we'll send you a reset link.</p>

<form method="POST" action="<?= url('/forgot-password') ?>" class="space-y-4">
  <?= $csrf_field ?>
  <div>
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-input" required autofocus>
  </div>
  <button type="submit" class="btn btn-gold w-full">Send Reset Link</button>
</form>
<p class="text-center text-sm text-muted mt-6"><a href="<?= url('/login') ?>" class="text-gold font-semibold hover:underline">Back to sign in</a></p>
