<?php $pageTitle = 'Book ' . $roomType['name']; ?>
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 lg:grid-cols-3 gap-10">
  <form id="booking-form" method="POST" action="<?= url('/booking') ?>" class="lg:col-span-2 card p-7 space-y-5"
        data-room-type-slug="<?= e($roomType['slug']) ?>" data-quote-url="<?= url('/booking/quote') ?>">
    <?= $csrf_field ?>
    <input type="hidden" name="room_type_slug" value="<?= e($roomType['slug']) ?>">

    <div>
      <p class="hero-eyebrow text-gold mb-1">Reserve</p>
      <h1 class="font-display text-2xl font-bold"><?= e($roomType['name']) ?></h1>
      <p class="text-sm text-muted mt-1"><?= money($roomType['base_price']) ?> / night</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">Check-in</label>
        <input type="date" name="check_in" value="<?= old('check_in') ?>" class="form-input <?= field_error_class('check_in') ?>" required>
        <?php if (field_error('check_in')): ?><p class="form-error"><?= e(field_error('check_in')) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="form-label">Check-out</label>
        <input type="date" name="check_out" value="<?= old('check_out') ?>" class="form-input <?= field_error_class('check_out') ?>" required>
        <?php if (field_error('check_out')): ?><p class="form-error"><?= e(field_error('check_out')) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">Full Name</label>
        <input type="text" name="guest_name" value="<?= old('guest_name', $user['full_name'] ?? '') ?>" class="form-input <?= field_error_class('guest_name') ?>" required>
        <?php if (field_error('guest_name')): ?><p class="form-error"><?= e(field_error('guest_name')) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="form-label">Phone Number</label>
        <input type="text" name="guest_phone" value="<?= old('guest_phone', $user['phone'] ?? '') ?>" class="form-input <?= field_error_class('guest_phone') ?>" required>
        <?php if (field_error('guest_phone')): ?><p class="form-error"><?= e(field_error('guest_phone')) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">City</label>
        <input type="text" name="guest_city" value="<?= old('guest_city') ?>" class="form-input">
      </div>
      <div>
        <label class="form-label">Guests</label>
        <input type="number" name="guests_count" min="1" max="<?= (int) $roomType['max_guests'] ?>" value="<?= old('guests_count', '1') ?>" class="form-input">
      </div>
    </div>

    <?php if (!empty($extraServices)): ?>
      <div>
        <label class="form-label">Extra Services</label>
        <div class="grid grid-cols-2 gap-2">
          <?php foreach ($extraServices as $s): ?>
            <label class="flex items-center gap-2 text-sm border border-app rounded-lg px-3 py-2 cursor-pointer">
              <input type="checkbox" name="extra_service_ids[]" value="<?= (int) $s['id'] ?>">
              <?= e($s['name']) ?> <span class="text-muted">(+<?= money($s['price']) ?>)</span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">Coupon Code (optional)</label>
        <input type="text" name="coupon_code" value="<?= old('coupon_code') ?>" class="form-input" placeholder="e.g. WELCOME10">
      </div>
      <div>
        <label class="form-label">Payment Method</label>
        <select name="payment_method" class="form-select">
          <?php foreach ($gateways as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $value !== 'pay_at_hotel' ? 'disabled' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="form-label">Notes (optional)</label>
      <textarea name="notes" rows="2" class="form-textarea"><?= old('notes') ?></textarea>
    </div>

    <button type="submit" class="btn btn-gold w-full">Confirm Booking</button>
  </form>

  <div class="card p-6 h-max sticky top-24">
    <h3 class="font-display font-semibold mb-4">Price Summary</h3>
    <div id="booking-summary" class="transition-opacity">
      <p class="text-sm text-muted">Select your dates to see pricing.</p>
    </div>
  </div>
</section>
