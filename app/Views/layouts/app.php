<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body class="bg-bgapp text-app">
<?php include __DIR__ . '/../partials/flash.php'; ?>
<?php include __DIR__ . '/../partials/navbar.php'; ?>

<main><?= $__content ?></main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
