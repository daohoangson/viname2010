<div id="searcher">
	<form id="searchForm" action="<?php echo site_url('search/submit') ?>" method="POST">
		<table cellspacing="5" cellpadding="0">
			<tbody>
				<tr>
					<td>
						<input id="q" class="searchBox" type="text" name="q" spellcheck="false" autocomplete="off" />
					</td>
					<td>
						<input type="submit" class="button" value="<?php echo lang('search') ?>" />
					</td>
				</tr>
			</tbody>
		</table>	
	</form>
	<div id="resultsLoading" style="display: none"><?php echo lang('search_loading') ?></div>
</div>
<?php define('LAYOUT_NO_SEARCHBAR',true) // disable the global search bar ?>
<div id="resultsAjax" style="display: none">&nbsp;</div>
<?php
	Shared::_js_static('assets/js/search.js');
	Shared::_css_static('assets/css/search.css');
?>
<script type="text/javascript">
	(function($){
		var resultsNavigator = function() {
			$('.filters a, .paginator a').clickAjax({
				'target': '#resultsAjax',
				'loading': '#resultsLoading',
				'callback': function() {
					resultsNavigator();
					$(document).scrollTop($('#resultsAjax').offset().top);
				}
			})
		};
		
		$(document).ready(function(){
			$('#q').suggestion({
				'words': [
					'<?php echo site_url('api/json/getList/family_names') ?>'
					,'<?php echo site_url('api/json/getList/names') ?>'
				],
				'placeHolder': '<?php echo lang('enter_names_here') ?>'
			});
			$('#footer').css('display','none');
			$('#searcher')
				.find('form').submitAjax({
					'target': '#resultsAjax',
					'loading': '#resultsLoading',
					'callback': function() {
						$('#searcher').animate({'margin-top': 0},'slow');
						$('#footer').css('display','inherit');
						resultsNavigator();
					}
			});
			if (!window.location.hash) {
				$('#searcher').css('margin-top',($(document).height() - $('#searcher').height())/3);
				$('#q').focus();
			}
		});
	})(jQuery);
</script>