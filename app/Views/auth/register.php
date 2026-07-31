<?php $pageTitle = 'Create Account'; ?>
<h1 class="font-display text-2xl font-bold mb-1">Create your account</h1>
<p class="text-sm text-muted mb-6">Book faster and track your reservations.</p>

<form method="POST" action="<?= url('/register') ?>" class="space-y-4">
  <?= $csrf_field ?>
  <div>
    <label class="form-label">Full Name</label>
    <input type="text" name="full_name" value="<?= old('full_name') ?>" class="form-input <?= field_error_class('full_name') ?>" required>
    <?php if (field_error('full_name')): ?><p class="form-error"><?= e(field_error('full_name')) ?></p><?php endif; ?>
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="form-label">Username</label>
      <input type="text" name="username" value="<?= old('username') ?>" class="form-input <?= field_error_class('username') ?>" required>
      <?php if (field_error('username')): ?><p class="form-error"><?= e(field_error('username')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Phone</label>
      <input type="text" name="phone" value="<?= old('phone') ?>" class="form-input <?= field_error_class('phone') ?>">
      <?php if (field_error('phone')): ?><p class="form-error"><?= e(field_error('phone')) ?></p><?php endif; ?>
    </div>
  </div>
  <div>
    <label class="form-label">Email</label>
    <input type="email" name="email" value="<?= old('email') ?>" class="form-input <?= field_error_class('email') ?>" required>
    <?php if (field_error('email')): ?><p class="form-error"><?= e(field_error('email')) ?></p><?php endif; ?>
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-input <?= field_error_class('password') ?>" required>
      <?php if (field_error('password')): ?><p class="form-error"><?= e(field_error('password')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Confirm Password</label>
      <input type="password" name="password_confirmation" class="form-input" required>
    </div>
  </div>
  <button type="submit" class="btn btn-gold w-full">Create Account</button>
</form>

<p class="text-center text-sm text-muted mt-6">Already have an account? <a href="<?= url('/login') ?>" class="text-gold font-semibold hover:underline">Sign in</a></p>
