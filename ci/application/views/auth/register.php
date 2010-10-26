<div id="mainContent" class="container">
<h2><?php echo lang('auth_register') ?></h2>
<?php if (!empty($errors)): ?>
<?php $this->load->view('common/error',array('message' => $errors)) ?>
<?php endif ?>
<form action="<?php echo site_url('/auth/register') ?>" method="POST" id="registerForm">
	<dl class="container span-9 push-7 form">
		<dt class="column span-3"><label for="loginInput"><?php echo lang('auth_login_noun') ?></label></dt>
		<dd class="column span-5 last"><input id="loginInput" name="login" type="text" value="<?php echo $login ?>"/></dd>
		<dt class="column span-3"><label for="passwordInput"><?php echo lang('auth_password') ?></label></dt>
		<dd class="column span-5 last"><input id="passwordInput" name="password" type="password"/></dd>
		<dt class="column span-3"><label for="emailInput"><?php echo lang('auth_email') ?></label></dt>
		<dd class="column span-5 last"><input id="emailInput" name="email" type="text" value="<?php echo $email ?>"/></dd>
		<dt class="column span-8 last">
			<label for="agreeCheck">
				<input id="agreeCheck" type="checkbox" name="agree" value="1"<?php if ($agree): ?> checked="checked"<?php endif ?>/>
				<?php echo lang('auth_agree_with_tos') ?>
			</label>
		</dt>
		<dt><input type="submit" class="button colum span-4 push-3 last" value="<?php echo lang('auth_register') ?>"/></dt>
</form>
</div><!-- #mainContent -->