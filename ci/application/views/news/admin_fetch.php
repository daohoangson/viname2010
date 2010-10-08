<?php if (!empty($results)): ?>
<div class="message">
<h3><?php echo lang('news_fetch_found_names') ?></h3>
<?php foreach ($results as $result): ?>
<div>
	<a href="<?php echo $result['url'] ?>"><?php echo $result['url'] ?></a>
</div>
<?php if (!empty($result['error'])): ?>
	<?php echo lang('news_fetch_error') ?>: <?php echo $result['error'] ?>
<?php else: ?>
<ol class="news_fetch_names">
	<?php foreach ($result['names'] as $name): ?>
	<?php if (empty($name)) continue; ?>
	<li>
		<?php echo $name ?>
		<form class="news_manage_form" action="<?php echo site_url('/admin/news/manage') ?>" method="POST">
			<input type="hidden" name="words[ignores][]" value="<?php echo $name ?>"/>
			<input type="submit" value="<?php echo lang('news_fetch_found_ignore') ?>">
		</form>
	</li>
	<?php endforeach ?>
</ol>
<?php endif ?>
<?php endforeach ?>
</div>
<?php Shared::_jQuery(); ?>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		var optionsCallback = function($form) {
			console.log($form.parent());
			$form.parent().animate({'opacity':0},'slow','',function() { $form.parent().remove(); });
		};
		
		$('.news_manage_form').each(function(){
			var options = {};
			var div = document.createElement('div');
			$(div).insertAfter(this);
			options.target = div;
			options.callback = optionsCallback;
			$(this).submitAjax(options);
		});
	});
})(jQuery);
</script>
<?php endif ?>
<form method="POST">
	<input id="urlInput" type="text" name="url" value="<?php echo $url ?>"/>
	<label for="isFeedCheck">
		<input id="isFeedCheck" type="checkbox" name="isFeed" value="1"<?php if ($isFeed): ?> checked="checked"<?php endif ?>/>
		<?php echo lang('news_url_is_feed') ?>
	</label>
	<input type="submit" value="<?php echo lang('news_fetch') ?>"/>
</form>
