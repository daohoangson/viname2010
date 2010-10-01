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
})(jQuery);