<?php $pageTitle = 'Contact'; ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid grid-cols-1 md:grid-cols-2 gap-12">
  <div>
    <p class="hero-eyebrow text-gold mb-3">Get in Touch</p>
    <h1 class="font-display text-4xl font-bold mb-6">Contact Us</h1>
    <p class="text-muted mb-8">Have a question about a reservation or an upcoming event? Send us a message and our team will respond shortly.</p>
    <ul class="space-y-4 text-sm">
      <li class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center"><i class="fa-solid fa-phone"></i></span><?= e((string) setting('contact_phone', '')) ?></li>
      <li class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center"><i class="fa-solid fa-envelope"></i></span><?= e((string) setting('contact_notify_email', '')) ?></li>
      <li class="flex items-center gap-3"><span class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center"><i class="fa-solid fa-location-dot"></i></span><?= e((string) setting('contact_address', '')) ?></li>
    </ul>
  </div>

  <form method="POST" action="<?= url('/contact') ?>" class="card p-7 space-y-4">
    <?= $csrf_field ?>
    <div>
      <label class="form-label">Full Name</label>
      <input type="text" name="name" value="<?= old('name') ?>" class="form-input <?= field_error_class('name') ?>" required>
      <?php if (field_error('name')): ?><p class="form-error"><?= e(field_error('name')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Email</label>
      <input type="email" name="email" value="<?= old('email') ?>" class="form-input <?= field_error_class('email') ?>" required>
      <?php if (field_error('email')): ?><p class="form-error"><?= e(field_error('email')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Subject</label>
      <input type="text" name="subject" value="<?= old('subject') ?>" class="form-input <?= field_error_class('subject') ?>" required>
      <?php if (field_error('subject')): ?><p class="form-error"><?= e(field_error('subject')) ?></p><?php endif; ?>
    </div>
    <div>
      <label class="form-label">Message</label>
      <textarea name="message" rows="4" class="form-textarea <?= field_error_class('message') ?>" required><?= old('message') ?></textarea>
      <?php if (field_error('message')): ?><p class="form-error"><?= e(field_error('message')) ?></p><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-gold w-full">Send Message</button>
  </form>
</section>
