<?php if (!empty($filters)): ?>
<ul class="filters clearfix">
	<?php foreach ($filters as $filtertype => $filters_type): ?>
	<?php if (count($filters_type) == 1 AND empty($active_filters[$filtertype])) continue ?>
	<?php foreach ($filters_type as $filterkey => $filter): ?>
	<?php
		if (empty($filter['count'])) continue; // go through empty filter
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
	<li class="filter <?php if ($filter_active): ?>filterActivated<?php endif ?>">
		<?php if ($filter_active): ?>
		<!-- <?php echo lang('search_filter_active') ?> -->
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
		<span class="filterCount"><?php echo $filter['count'] ?></span>
		<?php endif ?>
	</li>
	<?php endforeach ?>
	<?php endforeach ?>
</ul>
<?php endif ?>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$('.filter a').each(function(){
			var $this = $(this);
			var $next = $this.next('.filterCount').css('position','absolute').css('display','none');
			if ($next.get(0)) {
				$this.hover(function(e){
					switch (e.type) {
						case 'mouseenter':
							var offset = $this.offset();
							$next.css('top',(offset.top - $next.height()/2) + 'px')
								.css('left',(offset.left + $this.width() + 5) + 'px');
							$next.css('display','');
							break;
						default: $next.css('display','none'); break;
					}
				});
			}
		})
	})
})(jQuery);
</script>