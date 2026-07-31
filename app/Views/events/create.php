<?php $pageTitle = 'Events & Venues'; ?>
<section class="bg-secondary text-white py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="hero-eyebrow text-gold mb-3">Celebrate With Us</p>
    <h1 class="font-display text-4xl font-bold">Events &amp; Venues</h1>
    <p class="text-slate-400 mt-3 max-w-xl">From weddings to boardrooms — reserve one of our signature spaces.</p>
  </div>
</section>

<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <form method="POST" action="<?= url('/events') ?>" class="card p-7 space-y-5">
    <?= $csrf_field ?>

    <div>
      <label class="form-label">Venue</label>
      <select name="event_type_id" class="form-select <?= field_error_class('event_type_id') ?>" required>
        <option value="">Select a venue</option>
        <?php foreach ($eventTypes as $type): ?>
          <option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?> — <?= money($type['base_price']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (field_error('event_type_id')): ?><p class="form-error"><?= e(field_error('event_type_id')) ?></p><?php endif; ?>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">Full Name</label>
        <input type="text" name="guest_name" value="<?= old('guest_name') ?>" class="form-input <?= field_error_class('guest_name') ?>" required>
        <?php if (field_error('guest_name')): ?><p class="form-error"><?= e(field_error('guest_name')) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="form-label">Phone Number</label>
        <input type="text" name="guest_phone" value="<?= old('guest_phone') ?>" class="form-input <?= field_error_class('guest_phone') ?>" required>
        <?php if (field_error('guest_phone')): ?><p class="form-error"><?= e(field_error('guest_phone')) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div>
        <label class="form-label">Event Date</label>
        <input type="date" name="event_date" value="<?= old('event_date') ?>" class="form-input <?= field_error_class('event_date') ?>" required>
        <?php if (field_error('event_date')): ?><p class="form-error"><?= e(field_error('event_date')) ?></p><?php endif; ?>
      </div>
      <div>
        <label class="form-label">Start Time</label>
        <input type="time" name="start_time" value="<?= old('start_time') ?>" class="form-input" required>
      </div>
      <div>
        <label class="form-label">End Time</label>
        <input type="time" name="end_time" value="<?= old('end_time') ?>" class="form-input <?= field_error_class('end_time') ?>" required>
        <?php if (field_error('end_time')): ?><p class="form-error"><?= e(field_error('end_time')) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="form-label">City</label>
        <input type="text" name="guest_city" value="<?= old('guest_city') ?>" class="form-input">
      </div>
      <div>
        <label class="form-label">Expected Guests</label>
        <input type="number" name="guests_count" min="1" value="<?= old('guests_count', '1') ?>" class="form-input">
      </div>
    </div>

    <div>
      <label class="form-label">Notes (optional)</label>
      <textarea name="notes" rows="3" class="form-textarea"><?= old('notes') ?></textarea>
    </div>

    <button type="submit" class="btn btn-gold w-full">Request Booking</button>
  </form>
</section>
