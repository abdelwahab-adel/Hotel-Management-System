<?php $pageTitle = 'Settings'; ?>
<form method="POST" action="<?= url('/admin/settings') ?>" class="card p-7 space-y-4 max-w-2xl">
  <?= $csrf_field ?>
  <div>
    <label class="form-label">Hotel Name</label>
    <input type="text" name="hotel_name" value="<?= e($settings['hotel_name'] ?? '') ?>" class="form-input">
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? '$') ?>" class="form-input"></div>
    <div><label class="form-label">Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate_percent" value="<?= e($settings['tax_rate_percent'] ?? '0') ?>" class="form-input"></div>
  </div>
  <div>
    <label class="form-label">Contact Notification Email</label>
    <input type="email" name="contact_notify_email" value="<?= e($settings['contact_notify_email'] ?? '') ?>" class="form-input">
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div><label class="form-label">Contact Phone</label><input type="text" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>" class="form-input"></div>
    <div><label class="form-label">Address</label><input type="text" name="contact_address" value="<?= e($settings['contact_address'] ?? '') ?>" class="form-input"></div>
  </div>
  <button type="submit" class="btn btn-gold">Save Settings</button>
</form>
