<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="bg-bgapp text-app">
<?php include __DIR__ . '/../partials/flash.php'; ?>
<div class="flex min-h-screen">
  <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>
  <div class="flex-1 min-w-0">
    <?php include __DIR__ . '/../partials/admin-topbar.php'; ?>
    <main class="p-4 sm:p-8"><?= $__content ?></main>
  </div>
</div>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
