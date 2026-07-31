<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="bg-primary text-app min-h-screen flex items-center justify-center px-4">
<?php include __DIR__ . '/../partials/flash.php'; ?>
<div class="w-full max-w-md fade-in">
  <a href="<?= url('/') ?>" class="flex items-center justify-center gap-2 font-display font-bold text-white text-xl mb-8">
    <i class="fa-solid fa-hotel text-gold"></i> <?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?>
  </a>
  <div class="bg-surface rounded-2xl shadow-luxury p-8">
    <?= $__content ?>
  </div>
  <p class="text-center text-slate-400 text-xs mt-6"><a href="<?= url('/') ?>" class="hover:text-gold"><i class="fa-solid fa-arrow-left mr-1"></i>Back to website</a></p>
</div>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
