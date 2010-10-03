<div class="error">
	<?php if (is_string($message)): ?>
	<?php echo $message ?>
	<?php else: ?>
		<?php if (is_array($message)): ?>
			<?php if (count($message) > 1): ?>
			<ol>
				<?php foreach ($message as $single): ?>
					<li><?php echo $single ?></li>
				<?php endforeach ?>
			</ol>
			<?php else: ?>
				<?php echo $message[0] ?>	
			<?php endif ?>
		<?php endif ?>
	<?php endif ?>
	
</div>