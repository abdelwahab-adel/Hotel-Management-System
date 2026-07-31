<?php $pageTitle = 'Activity Log'; ?>
<div class="card divide-app overflow-hidden">
  <?php foreach ($logs as $log): ?>
    <div class="p-4 text-sm flex justify-between">
      <div>
        <p class="font-medium"><?= e($log['full_name'] ?? 'System') ?> — <span class="text-muted"><?= e(str_replace('_',' ',$log['action'])) ?></span></p>
        <p class="text-xs text-muted mt-0.5"><?= e($log['description']) ?> · IP <?= e((string) $log['ip_address']) ?></p>
      </div>
      <span class="text-xs text-muted whitespace-nowrap"><?= e(date('M j, Y g:ia', strtotime($log['created_at']))) ?></span>
    </div>
  <?php endforeach; ?>
  <?php if (empty($logs)): ?><p class="p-8 text-center text-muted">No activity recorded yet.</p><?php endif; ?>
</div>
