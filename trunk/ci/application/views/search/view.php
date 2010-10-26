<?php $displayed = array() ?>
<div id="mainContent" class="container">
	<h2><?php echo sprintf(lang('viewing_result_for_x'),$q) ?></h2>
	<?php if (empty($result)): ?>
	<div><?php echo lang('no_result_found') ?></div>
	<?php else: ?>
	<div id="resultsViewer" class="column span-18">
		<?php
			list($start,$end,$paginator) = $this->paginator->build(count($result),$perpage_default,$args);
			$this->ordering->sort($result);
		?>
		<div class="filtersContainer">
			<?php echo $this->load->view('search/filters',array('filters' => $filters, 'active_filters' => $active_filters)) ?>
		</div>
		<div class="resultsContainer">
			<?php echo $paginator ?>
			<ol id="results" class="container">
				<li class="resultsHeader">
					<div class="name column span-8"><?php echo lang('full_name') ?></div>
					<div class="gender column span-2"><?php echo lang('gender') ?></div>
					<div class="popularity column span-4"><a href="<?php echo $this->ordering->buildLink('popularity'); ?>"><?php echo lang('popularity') ?></a></div>
					<div class="relevant column span-4 last">&nbsp;</div>
				</li>
				<?php for ($i = $start; $i <= $end; $i++): ?>
				<?php $record =& $result[$i] ?>
				<li class="result">
					<?php $displayed[] = $record->full_name ?>
					<div class="name column span-8"><a href="<?php echo site_url('/name/view/' . $this->Indexed->utilHash($record->full_name)) ?>"><?php echo $record->full_name ?></a></div>
					<div class="gender column span-2">
						<?php
						switch ($record->gender) {
							case 0: echo lang('gender_male'); break;
							case 1: echo lang('gender_female'); break;
							default: echo lang('gender_both'); break;
						}
					?>
					</div>
					<div class="popularity column span-4">
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
					<?php echo $record->count ?>
					</div>
					<div class="relevant column span-4 last"><?php echo $record->relevant ?></div>
				</li>
				<?php endfor ?>
			</ol>
			<?php echo $paginator ?>
		</div><!-- #resultsContainer -->
	</div><!-- #resultViewer -->
	<div id="suggest" class="column span-6 last">
		<?php
			$blocks = array();
			$blocktypes = array('famous','same_family'/*,'same_name'*/);
			while (trim(implode('',$blocks)) == '' AND !empty($blocktypes)) {
				$block = array_shift($blocktypes);
				$blocks[$block] = $this->load->view('common/block_' . $block,array('displayed' => $displayed),true);
			}
		?>
		<?php foreach ($blocks as $block): ?>
			<?php echo $block ?>
		<?php endforeach ?>
	</div>
</div><!-- #mainContent -->
<?php endif ?>
<?php
	Shared::_css_static('assets/css/search.css');
?>