<div class="paginator">
	<span class="description"><?php echo sprintf(lang('viewing_page_x_of_y'),$page,$pages) ?></span>
	<?php for ($i = 1; $i <= $pages; $i++): ?>
		<?php if ($i == $page): ?>
			<span class="current"><?php echo $i ?></span>
		<?php else: ?>
			<?php if ($i == 1): ?>
				<span class="first">[<a href="<?php echo sprintf($link,1,$perpage) ?>"><?php echo lang('first_page') ?></a>]</span>
			<?php else: ?>
				<?php if ($i == $pages): ?>
					<span class="last">[<a href="<?php echo sprintf($link,$pages,$perpage) ?>"><?php echo lang('last_page') ?></a>]</span>
				<?php else: ?>
					<span class="page">[<a href="<?php echo sprintf($link,$i,$perpage) ?>"><?php echo $i ?></a>]</span>
				<?php endif ?>
			<?php endif ?>
		<?php endif ?>
	<?php endfor ?>
</div>