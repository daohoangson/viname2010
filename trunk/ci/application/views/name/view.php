<?php
	$first =& $indexed[0];
	if (count($indexed) == 1 AND $first->gender != -1) {
		$gender = $first->gender;
	} else {
		$gender = -1;
	}
?>
<h2><?php echo $first->original_full_name ?></h2>
<div class="name_profile">
	<?php if (!empty($first->family_name)): ?>
	<div class="family_name">
		<label><?php echo lang('family_name') ?></label>: 
		<a href="<?php echo site_url('/name/family/' . $this->Indexed->utilHash($first->original_family_name)) ?>"><?php echo $first->original_family_name ?></a>
	</div>	
	<div class="name">
		<label><?php echo lang('name') ?></label>:
		<a href="<?php echo site_url('/name/only/' . $this->Indexed->utilHash($first->original_name)) ?>"><?php echo $first->original_name ?></a>
	</div>
	<?php endif ?>
	<div class="gender">
		<label><?php echo lang('gender') ?></label>:
		<?php 
			switch ($gender) {
				case 0: echo lang('gender_male'); break;
				case 1: echo lang('gender_female'); break;
				default: echo lang('gender_both'); break;
			}
		?>
	</div>
</div>
<?php echo $comments ?>
<?php echo $commentForm ?>