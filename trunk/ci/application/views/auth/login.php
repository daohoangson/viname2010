<?php if ($this->authentication->flash()): ?>
<div class="message">
	<?php echo $this->authentication->flash() ?>
</div>
<?php endif ?>
<?php if (!empty($errors)): ?>
<?php $this->load->view('common/error',array('message' => $errors)) ?>
<?php endif ?>
<form action="<?php echo site_url('/auth/login') ?>" method="POST" id="loginForm">
	<label for="loginInput"><?php echo lang('auth_login_noun') ?></label>
	<input id="loginInput" name="login" type="text" value="<?php echo $login ?>"/>
	<label for="passwordInput"><?php echo lang('auth_password') ?></label>
	<input id="passwordInput" name="password" type="password"/>
	<label for="rememberCheck">
		<input id="rememberCheck" type="checkbox" name="remember" value="1"<?php if ($remember): ?> checked="checked"<?php endif ?>/>
		<?php echo lang('auth_remember') ?>
	</label>
	<input type="submit" value="<?php echo lang('auth_login') ?>"/>
</form>