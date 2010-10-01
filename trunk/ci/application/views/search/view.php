<h2><?php echo sprintf(lang('viewing_result_for'),$data->q) ?></h2>
<?php if (empty($data->result)): ?>
<div><?php echo lang('no_result_found') ?></div>
<?php else: ?>
<ol id="results">
<li class="search_result search_header">
	<div class="name"><?php echo lang('name') ?></div>
	<div class="gender"><?php echo lang('gender') ?></div>
	<div class="count">&nbsp;</div>
	<div class="score">&nbsp;</div>
	<div class="relevant"><?php echo lang('relevant') ?></div>
</li>
<?php foreach ($data->result as $record): ?>
<li class="search_result">
	<div class="name"><?php echo $record->full_name ?></div>
	<div class="gender"><?php echo $record->gender ?></div>
	<div class="count"><?php echo $record->count ?></div>
	<div class="score"><?php echo $record->average_score ?></div>
	<div class="relevant"><?php echo $record->relevant ?>%</div>
</li>
<?php endforeach ?>
</ol>
<?php endif ?>
<?php
	Shared::_css_static('assets/css/search.css');
?>