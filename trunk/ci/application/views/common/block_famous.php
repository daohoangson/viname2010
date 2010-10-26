<?php $famous = $this->Indexed->generateList('famous',array('full_names' => $displayed),3); ?>
<?php if (!empty($famous)): ?>
<?php $this->load->helper('formatter') ?>
<div class="rightBlock">
	<h3 class="blockHeader"><?php echo lang('block_famous') ?></h3>
	<div class="blockContent">
		<ol>
		<?php foreach ($famous as $person): ?>
			<li class="famousPerson">
				<div class="famousName"><?php echo $person->full_name ?></div>
				<div class="famousInfo muted">
					<?php echo extractYearFromMysqlDate($person->dob,'?') ?>-<?php echo extractYearFromMysqlDate($person->dod,'?') ?>
				</div>
				<?php list($title,$info) = parseTitleAndInfo($person->title,$person->data) ?>
				<?php if (!empty($title)): ?>
					<div class="personTitle"><?php echo $title ?></div>
				<?php endif ?>
				<?php if (!empty($info)): ?>
					<div class="personInfo"><?php echo $info ?></div>
				<?php endif ?>
			</li>
		<?php endforeach ?>
		</ol>
	</div>
</div>
<?php endif ?>