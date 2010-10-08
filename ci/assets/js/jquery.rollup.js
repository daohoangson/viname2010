(function($){
	$.extend($,{
		context: function(context,functionName) {
			// bring some closure juicy into jQuery
			// keep the context for the specified function
			// useful with callbacks
			var f = context[functionName];
			return function() { return f.apply(context,arguments); };
		}
	});
	
	$.fn.setSelectionRange = function(start,end) {
		// select a segment of text
		// TODO: remove this?
		this.each(function(index, elem) {
			if (elem.setSelectionRange) {
				elem.setSelectionRange(start,end);
			} else if (elem.createTextRange) {
				var range = elem.createTextRange();
				range.collapse(true);
				range.moveStart('character',start);
				range.moveEnd('character',end);
				range.select();
			}
		});
		return this;
	};
	
	$.fn.submitAjax = function(options) {
		var $target = $(options.target);
		var $loading = $(options.loading);
		this.each(function() {
			var $form = $(this);
			var lastData = '';
			$form.submit(function() {
				var fdata = $form.serialize();
				if (fdata != lastData) {
					$loading.css('display','');
					$.ajax({
						'url': $form.attr('action'),
						'type': $form.attr('method'),
						'data': fdata + '&ajax=1',
						'dataType': 'html',
						'success': function(data) {
							if ($form.serialize() == fdata) {
								$loading.css('display','none');
								$target.html(data);
								if (typeof options.callback == 'function') {
									options.callback($form,data);
								}
							}
						},
					});
					lastData = fdata;
					return false;
				}
			});
		});
		return this;
	}
})(jQuery);