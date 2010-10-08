<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<title><?php echo lang('title') ?> - AdminCP</title>
	<base href="<?php echo base_url() ?>">
	<?php echo $head ?>
</head>
<body>
<?php echo $this->load->view('common/header','',true) ?>
<div id="content">
	<div>You are now in AdminCP. This message should be removed later</div>
<?php if ($message): ?>
<div class="message">
	<?php echo $message ?>
</div>
<?php endif ?>
<?php echo $output ?>
</div>
<?php echo $this->load->view('common/footer','',true) ?>
</body>