<?php
	$first =& $indexed[0];
?>
<div id="mainContent" class="container">
	<h2><?php echo lang('name') ?> <?php echo $first->original_name ?></h2>
	<div class="column span-18">
		<div class="bordered">
			<?php echo lang('some_names') ?>
			<ol>
				<?php foreach ($indexed as $key => $record): ?>
				<?php if ($key < count($indexed) - 1): ?>
				<li><a href="<?php echo site_url('/name/view/' . $this->Indexed->utilHash($record->full_name)) ?>"><?php echo $record->original_full_name ?></a></li>
				<?php else: ?>
				<li><a href="<?php echo site_url('/search/more/' . $this->unicoder->base64_encode($first->original_name)) ?>" title="<?php echo lang('view_more') ?>">...</a></li>
				<?php endif ?>
				<?php endforeach ?>
			</ol>
		</div>
	</div>
	<div class="column span-6 last">
		something here?
	</div>
</div><!-- #mainContent -->