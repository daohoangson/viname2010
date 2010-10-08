<?php if (!empty($changeds)): ?>
<div class="message">
	<strong><?php echo lang('updated') ?></strong>:
	<?php foreach ($changeds as $changed): ?>
		<?php echo lang('news_manage_' . $changed) ?>
	<?php endforeach ?>
</div>
<?php endif ?>