(function($){
	$.extend($,{
		context: function(context,functionName) {
			// bring some closure juicy into jQuery
			// keep the context for the specified function
			// useful with callbacks
			var f = context[functionName];
			return function() { return f.apply(context,arguments); };
		}
		,loadUrlAjax: function(href,options) {
			var $loading = $(options.loading).css('display','');
			$.post(href,{'ajax': 1},function(data) {
				$loading.css('display','none');
				$.displayDataAjax(data,options);
			},'json');
		}
		,displayDataAjax: function(data,options)  {
			var $target = $(options.target);
			$target.each(function() {
				$(this).css('display','').html(data.html);
				if (typeof options.callback == 'function') {
					options.callback(this,data);
				}
			});
			var wl = window.location;
			var root = wl.protocol + '//' + wl.host + wl.pathname;
			var hash = data.url.replace(root,'');
			window.location = root + '#' + hash;
			window.ajaxHash = hash;
		}
		,initAjaxListener: function(options) {
			if (typeof window.ajaxListener == 'undefined') {
				// it's our job
				window.ajaxHash = '';
				window.ajaxListener = function() {
					var wl = window.location;
					var hash = wl.hash.replace('#','');
					if (typeof hash == 'string' && hash != window.ajaxHash) {
						window.ajaxHash = hash;
						$.loadUrlAjax(wl.protocol + '//' + wl.host + wl.pathname + hash,options);
					}
				}
				window.ajaxListener();
				window.setInterval('ajaxListener();',250);
			} else {
				// don't do it twice
			}
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
	
	$.fn.clickAjax = function(options) {
		$.initAjaxListener(options);
		this.click(function() {
			var href = this.href;
			if (typeof href == 'string') {
				$.loadUrlAjax(href,options);
				return false;
			}
		});
		return this;
	}
	
	$.fn.submitAjax = function(options) {
		$.initAjaxListener(options);
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
						'dataType': 'json',
						'success': function(data) {
							if ($form.serialize() == fdata) {
								$loading.css('display','none');
								$.displayDataAjax(data,options);
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