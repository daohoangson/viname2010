<?php
	$families = array();
	foreach ($displayed as $full_name) {
		$words = explode(' ',$full_name);
		if (count($words) > 1) {
			$families[] = $this->unicoder->asciiAccent($words[0]);
		}
	}
	$families = array_unique($families);
?>
<?php if (count($families) == 1): ?>
	<?php $same_family = $this->Indexed->generateList('same_family',array('family' => $families[0],'ascii' => true),5); ?>
	<?php if (!empty($same_family)): ?>
<div class="rightBlock">
	<h3 class="blockHeader"><?php echo lang('block_same_family') ?></h3>
	<div class="blockContent">
		<ol>
		<?php foreach ($same_family as $person): ?>
			<?php if (in_array($person->original_full_name,$displayed)) continue; ?>
			<li>
				<div class="name"><?php echo $person->original_full_name ?></div>
			</li>
		<?php endforeach ?>
		</ol>
	</div>
</div>
	<?php endif ?>
<?php endif ?>