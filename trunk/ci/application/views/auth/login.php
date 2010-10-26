<div id="mainContent" class="container">
<h2><?php echo lang('auth_login') ?></h2>
<?php if ($this->authentication->flash()): ?>
<div class="message">
	<?php echo $this->authentication->flash() ?>
</div>
<?php endif ?>
<?php if (!empty($errors)): ?>
<?php $this->load->view('common/error',array('message' => $errors)) ?>
<?php endif ?>
<form action="<?php echo site_url('/auth/login') ?>" method="POST" id="loginForm">
	<input type="hidden" name="login_form_referrer" value="<?php echo $login_form_referrer ?>"/>
	<dl class="container span-9 push-7 form">
		<dt class="column span-3"><label for="loginInput"><?php echo lang('auth_login_noun') ?></label></dt>
		<dd class="column span-5 last"><input id="loginInput" name="login" type="text" value="<?php echo $login ?>"/></dd>
		<dt class="column span-3"><label for="passwordInput"><?php echo lang('auth_password') ?></label></dt>
		<dd class="column span-5 last"><input id="passwordInput" name="password" type="password"/></dd>
		<dt class="column span-3">&nbsp;</dt>
		<dd class="column span-5 last">
			<label for="rememberCheck">
				<input id="rememberCheck" type="checkbox" name="remember" value="1"<?php if ($remember): ?> checked="checked"<?php endif ?>/>
				<?php echo lang('auth_remember') ?>
			</label>
		</dd>
		<dt><input type="submit" class="button colum span-4 push-3 last" value="<?php echo lang('auth_login') ?>"/></dt>
	</dl>
</form>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#loginInput').focus();
	});
</script>
</div><!-- #mainContent -->