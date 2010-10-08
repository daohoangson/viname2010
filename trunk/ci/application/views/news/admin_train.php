<?php if (!empty($error)): ?>
<div class="message">
	<?php echo $error ?>
</div>
<?php endif ?>
<form method="POST">
	<label for="hostInput"><?php echo lang('news_train_host') ?></label>
	<input id="hostInput" type="text" name="host" value="<?php echo $host ?>"/>
	<label for="aliasInput"><?php echo lang('news_train_alias') ?></label>
	<textarea id="aliasInput" name="alias"><?php echo @$hostinfo['alias'] ?></textarea>
	<label for="startInput"><?php echo lang('news_train_start') ?></label>
	<textarea id="startInput" name="start"><?php echo @$hostinfo['start'] ?></textarea>
	<label for="authorStartInput"><?php echo lang('news_train_author_start') ?></label>
	<textarea id="authorStartInput" name="author_start"><?php echo @$hostinfo['author_start'] ?></textarea>
	<label for="entityEncodedCheck">
		<input id="entityEncodedCheck" type="checkbox" name="entityencoded" value="1"<?php if (!empty($hostinfo['entityencoded'])): ?> checked="checked"<?php endif ?>/>
		<?php echo lang('news_train_entityencoded') ?>
	</label>
	<?php if (!empty($hostinfo)): ?>
	<label for="deleteCheck">
		<input id="deleteCheck" type="checkbox" name="delete[<?php echo $this->unicoder->base64_encode($host) ?>]" value="1"/>
		<?php echo lang('news_train_remove') ?>
	</label>
	<?php endif ?>
	<input type="submit" value="<?php echo lang('news_train_save') ?>">
</form>