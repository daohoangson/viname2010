<?php echo $this->load->view('news/admin_manage_changed',array('changeds' => $changeds),true) ?>
<h3><?php echo lang('news_manage_traineds') ?></h3>
<?php if (!empty($traineds)): ?>
<div class="news_traineds">
	<?php foreach ($traineds as $host => $hinfo): ?>
	<div class="news_trained">
		<?php echo $host ?>
		<?php if (!empty($hinfo['alias'])): ?>
		<em><?php echo 'A.K.A. ' . $hinfo['alias'] ?></em>
		<?php endif ?>
		(<?php echo htmlentities($hinfo['start']) ?>|<?php if (!empty($hinfo['author_start'])): ?><?php echo htmlentities($hinfo['author_start']) ?><?php else: ?><?php echo lang('news_train_no_author') ?><?php endif ?>|<?php
			if (!empty($hinfo['entityencoded'])): ?><?php echo lang('news_train_entityencoded') ?><?php else: ?><?php echo lang('news_train_entityencoded_not') ?><?php endif ?>)
		<a href="<?php echo site_url('/admin/news/train/' . $host) ?>"><?php echo lang('news_train_edit') ?></a>
	</div>
	<?php endforeach ?>
</div>
<?php else: ?>
<em><?php echo lang('news_traineds_list_empty') ?></em>
<?php endif ?>
<a href="<?php echo site_url('/admin/news/train') ?>"><?php echo lang('news_train') ?></a>

<form method="POST">
<h3><?php echo lang('news_manage_clean') ?></h3>
<?php echo $this->load->view('news/admin_manage_words',array('type' => 'clean','data' => $clean),true) ?>

<h3><?php echo lang('news_manage_ignores') ?></h3>
<?php echo $this->load->view('news/admin_manage_words',array('type' => 'ignores','data' => $ignores),true) ?>

<h3><?php echo lang('news_manage_ignores_sub') ?></h3>
<?php echo $this->load->view('news/admin_manage_words',array('type' => 'ignores_sub','data' => $ignores_sub),true) ?>

<h3><?php echo lang('news_manage_pronouns') ?></h3>
<?php echo $this->load->view('news/admin_manage_words',array('type' => 'pronouns','data' => $pronouns),true) ?>

</form>
<script type="text/javascript">
(function($){
	var newsInputKeying = function(e) {
		var $target = $(e.target);
		var nclass = $target.attr('class');
		if (nclass) {
			var count = 0;
			$('input.' + nclass).each(function(){
				if (this.value == '') count++;
			});
			if (count == 0) $target.clone().val('').keyup(newsInputKeying).insertAfter($target);
		}
	};
	
	$(document).ready(function(){
		$('.news_words_input input[type=text]').each(function(){
			$(this).keyup(newsInputKeying);
		});
	});
}
)(jQuery);
</script>