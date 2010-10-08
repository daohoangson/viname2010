<div id="header">
	<a href="<?php echo site_url('') ?>"><?php echo lang('home') ?></a>
	<?php if ($this->authentication->isLoggedIn()): ?>
		<?php echo $this->authentication->getUser('username') ?>
		<a href="<?php echo site_url('/logout') ?>"><?php echo lang('auth_logout') ?></a>
	<?php else: ?>
		<a href="<?php echo site_url('/register') ?>"><?php echo lang('auth_register') ?></a>
		<a href="<?php echo site_url('/login') ?>"><?php echo lang('auth_login') ?></a>
	<?php endif ?>
</div>