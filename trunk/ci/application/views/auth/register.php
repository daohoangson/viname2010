<?php if (!empty($errors)): ?>
<?php $this->load->view('common/error',array('message' => $errors)) ?>
<?php endif ?>
<form action="<?php echo site_url('/auth/register') ?>" method="POST" id="registerForm">
	<label for="loginInput"><?php echo lang('auth_login_noun') ?></label>
	<input id="loginInput" name="login" type="text" value="<?php echo $login ?>"/>
	<label for="passwordInput"><?php echo lang('auth_password') ?></label>
	<input id="passwordInput" name="password" type="password"/>
	<label for="emailInput"><?php echo lang('auth_email') ?></label>
	<input id="emailInput" name="email" type="text" value="<?php echo $email ?>"/>
	<label for="agreeCheck">
		<input id="agreeCheck" type="checkbox" name="agree" value="1"<?php if ($agree): ?> checked="checked"<?php endif ?>/>
		<?php echo lang('auth_agree_with_tos') ?>
	</label>
	<input type="submit" value="<?php echo lang('auth_register') ?>"/>
</form>