<?php if (!empty($data)): ?>
<div class="news_words_list">
<?php foreach ($data as $id => $single): ?>
	<label for="delete_<?php echo $type ?>_<?php echo $id ?>">
		<input id="delete_<?php echo $type ?>_<?php echo $id ?>" type="checkbox" name="delete[<?php echo $type ?>][<?php echo $this->unicoder->base64_encode($id) ?>]" value="1"/>
		<?php echo strlen($single)>1?$single:($single . ' (' . ord($single) . ')') ?>
	</label>
<?php endforeach ?>
<div><?php echo lang('news_words_list_total') ?>: <?php echo count($data) ?></div>
</div>
<?php else: ?>
<em><?php echo lang('news_words_list_empty') ?></em>
<?php endif ?>
<div class="news_words_input">
	<input type="text" name="words[<?php echo $type ?>][]" class="news_<?php echo $type ?>"/>
	<input type="submit" value="<?php echo lang('news_manage_update') ?>"/>
</div>
