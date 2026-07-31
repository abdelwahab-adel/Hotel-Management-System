<?php $successMsg = flash('success'); $errorMsg = flash('error'); ?>
<?php if ($successMsg): ?><div class="hidden" data-flash="<?= e($successMsg) ?>" data-flash-type="success"></div><?php endif; ?>
<?php if ($errorMsg): ?><div class="hidden" data-flash="<?= e($errorMsg) ?>" data-flash-type="error"></div><?php endif; ?>
