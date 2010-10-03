<div id="searcher">
	<form id="searchForm" action="<?php echo site_url('search/submit') ?>" method="POST">
		<table cellspacing="5" cellpadding="0" align="center">
			<tbody>
				<tr>
					<td>
						<input id="q" class="search_box" type="text" name="q" spellcheck="false" autocomplete="off"/>
					</td>
					<td>
						<input type="submit" value="<?php echo lang('search') ?>">
					</td>
				</tr>
			</tbody>
		</table>	
	</form>
</div>
<div id="resultsLoading" style="display: none"><img src="assets/img/loading.gif" alt="Loading" class="img_center"/></div>
<div id="resultsContainer">&nbsp;</div>
<?php 
	Shared::_jQuery();
	Shared::_js_static('assets/js/search.js');
	Shared::_css_static('assets/css/search.css');
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#q').suggestion({'words': [
		'<?php echo site_url('api/json/getList/family_names') ?>'
		,'<?php echo site_url('api/json/getList/names') ?>']})
		.focus();
	jQuery('#searchForm').submitAjax({'target': 'resultsContainer', 'loading': 'resultsLoading'});
});
</script>