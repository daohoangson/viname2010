<form id="commentForm" action="<?php echo current_url() ?>" method="POST">
	<textarea name="comment"><?php echo $comment ?></textarea>
	<input type="submit" value="<?php echo lang('comment_submit') ?>">
</form>