(function($){
	var Suggestion = function(domelement,options) {
		if (domelement.tagName && domelement.tagName.toLowerCase() == 'input') {
			this.__construct(domelement,options);
		}
	}
	
	Suggestion.prototype = {
		__construct: function(de,options) {
			this.words = [];
			this.separators = ' ';
			this.placeHolder = '';
			if (typeof options.placeHolder != 'undefined') this.placeHolder = options.placeHolder;
			
			if (typeof options != 'undefined') {
				if (typeof options.words == 'object') {
					for (var i in options.words) $.get(options.words[i],{},$.context(this,'loadWords'),'json');
				} else if (typeof options.words == 'string') {
					// request words from url
					$.get(options.words,{},$.context(this,'loadWords'),'json');
				} else if (typeof options.words != 'undefined') {
					this.words = options.words;
				}
			}
			
			// target input
			this.de = de;
			this.$ = $(de);
			// target parent
			this.$.parent().css('position','relative');
			// shadow container
			var container = document.createElement('div');
			$(container)
				.css('background','transparent')
				.css('position','relative');
			// shadow input
			/*this.shadow = document.createElement(de.tagName);
			this.shadow.type = de.type;
			this.shadow.tabIndex = -1;
			this.shadow.readOnly = true;*/
			this.shadow = this.$.clone().get(0);
			this.shadow.tabIndex = -1;
			this.shadow.readOnly = true;
			this.$s = $(this.shadow).val(this.placeHolder);
			$(container).append(this.shadow).insertBefore(de);
			this.$s.css('position','absolute')
				.css('border-color','transparent')
				.css('color','#AAA')
				.css('z-index',1);
			// shadow div (for text width detecting)
			// should be replaced
			this.div = document.createElement('div');
			this.$d = $(this.div);
			this.$d.insertBefore(de)
				.css('position','absolute')
				.css('top','-900px')
				.css('left','-9000px')
				.css('max-width','3000px')
				.css('overflow','hidden')
				.css('width','auto');
			var cssfontproperties = ['font-family','font-size','font-variant','font-weight'];
			for (var i = 0; i < cssfontproperties.length; i++) {
				this.$d.css(cssfontproperties[i],this.$.css(cssfontproperties[i]));
			}
			// primary input
			this.$.css('position','relative')
				.css('background','transparent')
				.css('z-index',3)
				.keydown($.context(this,'keyboarding'))
				.keyup($.context(this,'keyboarding'));
		}
		,loadWords: function(data) {
			if (typeof data.response == 'object') {
				for (var i in data.response) {
					this.words.push(data.response[i].toLowerCase());
				}
			}
		}
		,keyboarding: function(evt) {
			if (evt.type == 'keyup') {
				var words = this.$.val();
				var wordslength = words.length;
				var thiswordslength = this.words.length;
				var suggestion = '';
				var offset = 0;
				var found, str, strl, j, doffset;
				
				if (wordslength > 0) {
					do {
						found = '';
						str = words.substr(offset);
						strl = str.toLowerCase();
						for (j = 0; j < thiswordslength; j++) {
							if (this.words[j].length > found.length && strl.indexOf(this.words[j].substr(0,str.length)) == 0) {
								found = this.words[j];
							}
						}
						if (found.length > 0) {
							// found something, update the suggestion string and offset
							doffset = Math.min(found.length,str.length);
							suggestion += str.substr(0,doffset) + found.substr(str.length);
							offset += doffset;
						} else {
							// didn't find anything, advance through the next space character
							doffset = str.indexOf(' ') + 1;
							if (doffset == 0) doffset = str.length;
							suggestion += str.substr(0,doffset);
							offset += doffset;
						}
					} while (offset < wordslength); // do this until fully process the string
				}
				
				this.$d.html(suggestion);
				if (this.$d.width() > this.$s.width()) {
					this.$s.val('');
				} else if (suggestion.length > 0) {
					this.$s.val(suggestion);
				} else {
					this.$s.val(this.placeHolder);
				}
			} else if (evt.type == 'keydown') {
				if (evt.keyCode == 39 /* right */ || evt.keyCode == 9 /* tab */) {
					if (this.$.val() != this.$s.val() && this.$s.val().length > 0) {
						var oldlen = this.$.val().length;
						this.$.val(this.$s.val());
						// this.$.setSelectionRange(oldlen,this.$.val().length);
						return false;
					}
				}
			}
		}
	};
	
	$.fn.suggestion = function(options) {
		this.each(function() {new Suggestion(this,options);});
		return this;
	};
})(jQuery);	