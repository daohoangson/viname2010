<h2><?php echo sprintf(lang('viewing_result_for_x'),$q) ?></h2>
<?php if (empty($result)): ?>
<div><?php echo lang('no_result_found') ?></div>
<?php else: ?>
<?php
	list($start,$end,$paginator) = $this->paginator->build(count($result),50,$args);
?>
<?php if (!empty($filters)): ?>
<ul class="filters">
	<?php foreach ($filters as $filtertype => $filters_type): ?>
	<?php foreach ($filters_type as $filterkey => $filter): ?>
	<?php
		if (!empty($active_filters[$filtertype][$filterkey])) {
			// this one is active
			$filter_active = true;
			$url = str_replace('/filter-' . $filterkey,'',current_url());
		} else {
			// this one is not active
			$filter_active = false;
			$url = current_url() . '/filter-' . $filterkey;
		}
	?>
	<li>
		<?php if ($filter_active): ?>
		<?php echo lang('search_filter_active') ?>
		<?php endif ?>
		<a href="<?php echo $url ?>">
		<?php if (!empty($filter['family_name'])): ?>
			<span class="family_name"><?php echo lang('family_name') ?>: <?php echo $filter['family_name'] ?></span>
		<?php endif ?>
		<?php if (!empty($filter['middle_name'])): ?>
			<span class="middle_name"><?php echo lang('middle_name') ?>: <?php echo $filter['middle_name'] ?></span>
		<?php endif ?>
		<?php if (!empty($filter['name'])): ?>
			<span class="name"><?php echo lang('name') ?>: <?php echo $filter['name'] ?></span>
		<?php endif ?>
		<?php if (isset($filter['gender'])): ?>
		<?php
			switch ($filter['gender']) {
				case 0: echo lang('gender_male'); break;
				case 1: echo lang('gender_female'); break;
			}
		?>
		<?php endif ?>
		</a>
		<?php if (!$filter_active): ?>
		(<?php echo $filter['count'] ?>)	
		<?php endif ?>
	</li>
	<?php endforeach ?>
	<?php endforeach ?>
</ul>
<?php endif ?>
<?php echo $paginator ?>
<ol id="results">
<li class="search_result search_header">
	<div class="name"><?php echo lang('full_name') ?></div>
	<div class="gender"><?php echo lang('gender') ?></div>
	<div class="count"><?php echo lang('popularity') ?></div>
	<div class="relevant">&nbsp;</div>
</li>
<?php for ($i = $start; $i <= $end; $i++): ?>
<?php $record =& $result[$i] ?>
<li class="search_result">
	<div class="name"><a href="<?php echo site_url('/name/view/' . $this->Indexed->utilHash($record->full_name)) ?>"><?php echo $record->full_name ?></a></div>
	<div class="gender">
		<?php
		switch ($record->gender) {
			case 0: echo lang('gender_male'); break;
			case 1: echo lang('gender_female'); break;
			default: echo lang('gender_both'); break;
		}
	?>
	</div>
	<div class="count">
		<?php
		if ($record->count > 500) {
			$popularity = 'huge';
		} else if ($record->count > 100) {
			$popularity = 'high';
		} else if ($record->count < 5) {
			$popularity = 'xrare';
		} else if ($record->count < 20) {
			$popularity = 'rare';
		} else if ($record->count < 50) {
			$popularity = 'low';
		} else {
			$popularity = 'medium';
		}
		echo lang('popularity_' . $popularity);
	?>
	<!--<?php echo $record->count ?>-->
	</div>
	<div class="relevant"><?php echo $record->relevant ?></div>
</li>
<?php endfor ?>
</ol>
<?php echo $paginator ?>
<?php endif ?>
<?php
	Shared::_css_static('assets/css/search.css');
?>