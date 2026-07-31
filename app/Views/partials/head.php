<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e($csrf_token) ?>">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e((string) setting('hotel_name', 'The Pacific Hotel')) ?></title>
<meta name="description" content="<?= e($pageDescription ?? 'A modern luxury hotel booking experience.') ?>">
<link rel="icon" href="data:,">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary: '#0F172A',
          secondary: '#1E293B',
          gold: '#C9A227',
          bgapp: '#F8FAFC',
          success: '#22C55E',
          danger: '#EF4444',
        },
        fontFamily: { display: ['Poppins', 'sans-serif'] },
      }
    }
  }
</script>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
<script>window.HMS_CURRENCY = <?= json_encode((string) setting('currency_symbol', '$')) ?>; window.HMS_CSRF = <?= json_encode($csrf_token) ?>;</script>
