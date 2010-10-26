<div id="header">
	<?php if (!defined('LAYOUT_NO_SEARCHBAR') OR !LAYOUT_NO_SEARCHBAR): ?>
	<div id="quickSearcher">
		<form action="<?php echo site_url('search/submit') ?>" method="POST">
			<input id="q" class="quickSearchBox" type="text" name="q" spellcheck="false" />
			<input type="submit" class="button" value="<?php echo lang('search') ?>" />
		</form>
	</div>
	<?php endif ?>
	<div class="links">
		<a href="<?php echo site_url('') ?>"><?php echo lang('home') ?></a>
		<?php if ($this->authentication->isLoggedIn()): ?>
			<a href="<?php echo site_url('/profile') ?>"><?php echo lang('hi') ?></a>
			<?php echo $this->authentication->getUser('username') ?>
			<a href="<?php echo site_url('/logout') ?>"><?php echo lang('auth_logout') ?></a>
		<?php else: ?>
			<a href="<?php echo site_url('/register') ?>"><?php echo lang('auth_register') ?></a>
			<a href="<?php echo site_url('/login') ?>"><?php echo lang('auth_login') ?></a>
		<?php endif ?>
	</div>
</div>