var LHCCannedMessageAutoSuggest = (function() {
	
	function LHCCannedMessageAutoSuggest(params) {
		
		this.chat_id = params['chat_id'];
		this.suggesting = false; // Are we in suggesting mode		
		this.cannedMode = false; // false - tag mode menu, true - canned mode
		
		this.currentText = null;
		this.currentKeword = null;

		// Uppercase related
		this.nextUppercase = false;
		this.nextUppercasePos = 0;
		this.nextUppercaseCallback = null;
		this.nextUppercaseEnabled = typeof params['uppercase_enabled'] === 'undefined' || params['uppercase_enabled'] == true;

		// Store current request
		this.currentRequest = null;

		// Cache
		this.cacheCanned = {};

		this.htmlPreviewTimeout = null;

        // General one
		var _that = this;
		
		this.textareaHolder = jQuery('#CSChatMessage-'+this.chat_id);
        this.newEditor = false;

        this.keyUpCallback = function (evt) {

            if (_that.nextUppercaseEnabled == true)
            {
                if (_that.nextUppercase == true) {
                    clearTimeout(_that.nextUppercaseCallback);
                    _that.nextUppercaseCallback = setTimeout(function(){
                        _that.capitalizeSentences(evt);
                    },50);
                } else {
                    _that.capitalizeSentences(evt);
                }
            }

            if (evt.key == '#' || evt.keyCode == 51 || evt.keyCode == 222) {
                _that.currentText = _that.getValue();
                _that.showSuggester();
            } else if (evt.keyCode == 32 && _that.suggesting == true) {
                _that.stopSuggesting();
            } else if (_that.suggesting == true && evt.keyCode != 38 && evt.keyCode != 40 && evt.keyCode != 39 && evt.keyCode != 37 && evt.keyCode != 13) {

                if (_that.currentText !== _that.getValue()) { // Show suggester only if text is different
                    _that.showSuggester();
                    _that.currentText = _that.getValue();
                }

            } else if (_that.suggesting == true && (evt.keyCode == 37 || evt.keyCode == 39) && _that.cannedMode === false) {

                var oldKeyword = _that.currentKeword;

                if (oldKeyword !== _that.extractKeyword()) { // Only if keyword is different, happens if we migrate from canned messages to normal list.
                    _that.showSuggester();
                }

            } else if (_that.suggesting == false && (evt.keyCode == 39 || evt.keyCode == 37 || evt.keyCode == 8)) { // Perhaps user moved cursor to the right
                if (_that.extractKeyword() !== null) {
                    _that.showSuggester();
                }
            }
        };

        this.keyDownCallback = function (evt) {
            if (_that.suggesting == true) {
                if (evt.keyCode == 38) {
                    _that.moveAction('up');
                    evt.preventDefault();
                    evt.stopImmediatePropagation();
                } else if (evt.keyCode == 40) {
                    _that.moveAction('down');
                    evt.preventDefault();
                } else if ((evt.keyCode == 39 || evt.keyCode == 37) && _that.cannedMode === true) { // right/left arrow

                    // Current menu index
                    var index = $('#canned-hash-current-'+_that.chat_id+' li.current-item').parent().parent().index();

                    // We are in first block so we can return
                    // User clicked left arrow
                    if (index == 0 && evt.keyCode == 37) {
                        $('#canned-hash-current-'+_that.chat_id+' li.current-item > span.left-return').trigger( "click" );
                        evt.preventDefault();
                        evt.stopImmediatePropagation();
                    }

                    // How many columns we have
                    var subItems = $('#canned-hash-current-'+_that.chat_id+' .list-sub-items > li').length;

                    // we have only one sub-block so we can prefill instantly
                    // User clicked right arrow and we did not had any blocks
                    if (subItems == 0) {
                        $('#canned-hash-current-'+_that.chat_id+' li.current-item > span.canned-msg').trigger( "click" );
                    } else {

                        var indexInList = $('#canned-hash-current-'+_that.chat_id+' li.current-item').index();

                        // User clicked right and we have more than one block
                        $('#canned-hash-current-'+_that.chat_id+' li.current-item').removeClass('current-item');

                        // We have to check how many elements this block from right has and try to activate li element in direct position
                        if (evt.keyCode == 39) {
                            var newIndex = 0;
                            if (subItems - 1 >= (index + 1)) {
                                newIndex = (index + 1);
                            }
                            var listNew = $('#canned-hash-current-'+_that.chat_id+' > ul > li:eq(' + (newIndex) +') > ul');

                            var newIndexInList = 0;
                            if (listNew.find('> li').length - 1 >= indexInList) {
                                newIndexInList = indexInList;
                            }
                            _that.renderPreview(listNew.find(' > li:eq('+newIndexInList+')').addClass('current-item'));
                        } else if (evt.keyCode == 37) {
                            _that.renderPreview($('#canned-hash-current-'+_that.chat_id+' > ul > li:eq(' + (index - 1) +') > ul > li:eq('+indexInList+')').addClass('current-item'));
                        }
                    }

                    evt.preventDefault();
                    evt.stopImmediatePropagation();

                } else if (evt.keyCode == 39 || evt.keyCode == 13) { // right arrow OR enter

                    var preventDefault = true;
                    var element = null;

                    if (_that.cannedMode === false) {
                        element = $('#canned-hash-'+_that.chat_id+' > li.current-item a');
                    } else {
                        element = $('#canned-hash-current-'+_that.chat_id+' li.current-item > span.canned-msg');
                    }

                    if (element !== null && element.length > 0) {
                        element.trigger( "click" );
                        evt.preventDefault();
                        evt.stopImmediatePropagation();
                    }

                }
            }
        }

        if (this.textareaHolder.prop('nodeName') == 'LHC-EDITOR') {
            this.newEditor = true;
            this.textarea = jQuery(params['textarea']);
            this.textarea.bind('keyup', this.keyUpCallback);
            this.textarea.bind('keydown', this.keyDownCallback);
        } else {
            this.textarea = this.textareaHolder;
            this.textarea.bind('keyup', this.keyUpCallback);
            this.textarea.bind('keydown',  this.keyDownCallback);
        }
	}

    LHCCannedMessageAutoSuggest.prototype.unbindEvents = function(){
        this.textarea.unbind('keyup',this.keyUpCallback);
        this.textarea.unbind('keydown',this.keyDownCallback);
    }

    LHCCannedMessageAutoSuggest.prototype.getValue = function() {
        return this.textareaHolder.prop('nodeName') == 'LHC-EDITOR' ? this.textareaHolder[0].getContentLive() : this.textarea.val()
    }

    /*
    * Capitalizes sentences.
    * */
    LHCCannedMessageAutoSuggest.prototype.capitalizeSentences = function(evt) {

        if (this.newEditor === true) {
            let data = this.textareaHolder[0].getCaretPositionAndContent();
            var caretPos = data['caret'];
            var originalText = data['content'];
            var originalTextLength = data['full_content'].length;
        } else {
            var originalText = this.getValue();
            var caretPos = this.textarea[0].selectionStart;
            var originalTextLength = originalText.length;
        }

        var capText = originalText;

        if (evt.keyCode == 8 || evt.keyCode == 46) {
            this.nextUppercase = false;
            return;
        }

        // Replace very first character
    	if (originalTextLength <= 3) {
            capText = capText.replace(capText.charAt(0),capText.charAt(0).toUpperCase());
		}

        if (this.nextUppercase == true) {
             capText = capText.substr(0, this.nextUppercasePos) + capText.charAt(this.nextUppercasePos).toUpperCase() + capText.substr(this.nextUppercasePos+1);
		}

        if (originalText.charAt(caretPos-1) == ' ' && (originalText.charAt(caretPos-2) == '.' || originalText.charAt(caretPos-2) == '?' || originalText.charAt(caretPos-2) == '!') && originalText.length == caretPos) {
            this.nextUppercase = true;
            this.nextUppercasePos = caretPos;
        } else if (this.nextUppercase == true) {
            this.nextUppercase = false;
        }

        if (confLH.content_language == 'en') {
            capText = capText.replace(/\si\s/g,' I ');
        }

		if (capText != originalText) {

            if (this.newEditor === false) {
                this.textarea.val(capText);

                if ('selectionStart' in this.textarea[0]) {
                    this.textarea[0].selectionStart = caretPos;
                    this.textarea[0].selectionEnd = caretPos;
                } else if (this.textarea[0].setSelectionRange) {
                    this.textarea[0].setSelectionRange(caretPos, caretPos);
                } else if (this.textarea[0].createTextRange) {
                    var range = this.textarea[0].createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', caretPos);
                    range.moveStart('character', caretPos);
                    range.select();
                }
            } else {

                // In new editor we have to replace last character with &nbsp;
                if (capText.endsWith(' ')) {
                    capText = capText.substring(0, capText.length - 1) + "&nbsp;";
                }

                this.textareaHolder[0].replaceRange(capText);
            }

		}
	}

	LHCCannedMessageAutoSuggest.prototype.moveAction = function(action)
	{
		if (this.cannedMode === false) {
			var current = $('#canned-hash-'+this.chat_id+' > li.current-item');
		} else {
			var current = $('#canned-hash-current-'+this.chat_id+' li.current-item');
		}

		if (current.length == 0) {
			return;
		}

		if (action == 'up') {			
			var prev = current.prev();			
			if (prev.is('li')){
				current.removeClass('current-item');
                current = prev.addClass('current-item');
			} else {
                current = current.removeClass('current-item').parent().find(' > li').last().addClass('current-item');
			}
		} else if(action == 'down') {
			var next = current.next();			
			if (next.is('li')){
				current.removeClass('current-item');
                current = next.addClass('current-item');
			} else {
                current = current.removeClass('current-item').parent().find(' > li').first().addClass('current-item');
  			}
		}

        if (this.cannedMode === true) {
			this.renderPreview(current);
		}
	}

    LHCCannedMessageAutoSuggest.prototype.isVisible = function(lookIn, element, settings) {
        return (lookIn.height() + lookIn.offset().top) >= (element.offset().top + settings.threshold) && (element.offset().top > lookIn.offset().top - settings.threshold)
    };


    LHCCannedMessageAutoSuggest.prototype.renderPreview = function(element)
	{

		var dataMsg = element.find('> .canned-msg').attr('data-msg');

		clearTimeout(this.htmlPreviewTimeout);

		var _that = this;

		if (typeof dataMsg !== 'undefined') {

            if (!this.isVisible($('#canned-hash-current-' + this.chat_id),element,{threshold : 10})) {
                element[0].scrollIntoView();
            }

            var element = $('#canned-hash-current-' + this.chat_id).parent().find('.canned-msg-preview');

            if (element.length == 0) {
                $('#canned-hash-current-' + this.chat_id).parent().prepend('<div class="canned-msg-preview"></div>');
                element = $('#canned-hash-current-' + this.chat_id).parent().find('.canned-msg-preview');
			}

            element.text(dataMsg);

            this.htmlPreviewTimeout = setTimeout(function(){
                $.post(WWW_DIR_JAVASCRIPT + 'chat/previewmessage/' + _that.chat_id,{msg_body : true, msg : dataMsg}, function(data) {
                    element.html(data);
                    setTimeout(function(){
                        _that.adjustHeight();
                    },500);
                });
            },300);

            this.adjustHeight();

		} else {
            $('#canned-hash-current-' + this.chat_id).parent().find('.canned-msg-preview').remove();
		}
	}

    LHCCannedMessageAutoSuggest.prototype.adjustHeight = function()
    {
        var suggester = $('#chat-main-column-' + this.chat_id + ' .canned-suggester');

        if (suggester.height() > $('#CSChatMessage-'+this.chat_id).offset().top){
            $('#canned-hash-current-'+this.chat_id).css('max-height',$('#CSChatMessage-'+this.chat_id).offset().top - suggester.find('.canned-msg-preview').height() - 10);
        }
    }

	LHCCannedMessageAutoSuggest.prototype.stopSuggesting = function()
	{
		this.textarea.parent().find('.canned-suggester').remove();
		this.suggesting = false;
		this.cannedMode = false;
		this.currentText = null;
		this.currentKeword = null;
        if (this.newEditor) {
            this.textareaHolder[0].removeAttribute('disable_key_listeners');
        }
	}
	
	LHCCannedMessageAutoSuggest.prototype.extractKeyword = function()
	{
        var caretPos = 0, currentValue = "";
        if (this.newEditor === false){
            caretPos = this.textarea[0].selectionStart;
            currentValue = this.textarea.val();
        } else {
            let data = this.textareaHolder[0].getCaretPositionAndContent({"ignore_parent":true});
            caretPos = data['caret'];
            currentValue = data['content'];
        }

        var keyword = '';

		for (i = caretPos; i > 0; i--) {
			char = currentValue.substring(i-1, i);
			if (char == ' ') {	
				this.currentKeword = null;
				return null;
			} else if (char == '#') {
				this.currentKeword = keyword;
				return keyword;				
			} else {							
				keyword = char + keyword;
			}		
		}		
		
		this.currentKeword = null;
		return null;
	}

	this.timeoutRequest = null;

    LHCCannedMessageAutoSuggest.prototype.startSuggesting = function(){
        this.suggesting = true;
        if (this.newEditor) {
            this.textareaHolder[0].setAttribute('disable_key_listeners',true);
        }
    }

	LHCCannedMessageAutoSuggest.prototype.showSuggester = function()
	{
		var _that = this;
		
		this.extractKeyword();
		this.cannedMode = false;
        clearTimeout(this.timeoutRequest);

		if (this.currentKeword !== null) {

            _that.startSuggesting(true);

			this.timeoutRequest = setTimeout(function () {

				if (_that.currentRequest != null) {
                    _that.currentRequest.abort();
                    _that.currentRequest = null;
				}

				if (typeof _that.currentKeword !== "string") return ;

                var cacheKeyword = false;
				var cacheData = null;

                if (_that.currentKeword.length < 3) {
                    cacheKeyword = true;
                    if (typeof _that.cacheCanned[_that.currentKeword] !== 'undefined') {
                        cacheData = _that.cacheCanned[_that.currentKeword];
					}
                }

                if (cacheData !== null)
				{
                    _that.textarea.parent().find('.canned-suggester').remove();
                    _that.textarea.before(cacheData);
                    _that.initSuggester();
				} else {
                    _that.currentRequest = $.getJSON(WWW_DIR_JAVASCRIPT + 'cannedmsg/showsuggester/' + _that.chat_id,{keyword : _that.currentKeword}, function(data) {
                        _that.textarea.parent().find('.canned-suggester').remove();
                        _that.textarea.before(data.result);
                        _that.initSuggester();
                        if (cacheKeyword == true) {
                            _that.cacheCanned[_that.currentKeword] = data.result;
						}
                    });
				}

			}, 130);

		} else {

			this.stopSuggesting();
		}
	}

	LHCCannedMessageAutoSuggest.prototype.initSuggester = function()
	{
		var _that = this;
		var currentElement = $('#canned-hash-'+this.chat_id+' > li:last-child');

		if (currentElement.length > 0) {
            currentElement.addClass('current-item');
            this.textarea.parent().find('.canned-suggester').css("bottom", _that.textarea.height() + 16);
        } else {
            this.stopSuggesting();
		    return;
        }

		$('#canned-hash-'+this.chat_id+' > li > a').click(function() {
			
			_that.cannedMode = true;
			
			var content = $('#canned-hash-current-'+_that.chat_id);
			content.html('').show();
			$(this).parent().find('ul.list-sub-items').clone().appendTo(content);
            _that.renderPreview(content.find('ul > li:first-child > ul > li:first-child').addClass('current-item'));
			
			var container = $(this).parent().parent();
			container.hide();

            content.find('span.canned-msg').mouseover(function(){
                _that.renderPreview($(this).parent());
                $('#canned-hash-current-'+_that.chat_id+' li.current-item').removeClass('current-item');
                $(this).parent().addClass('current-item');
            });

			content.find('span.canned-msg').click(function(){

                var textAppend = $(this).attr('data-msg');
                var subjects_ids = $(this).attr('subjects_ids');
                var canned_id = $(this).attr('canned_id');

                if (_that.newEditor === false) {
                    currentValue = _that.getValue();

                    var caretPos = _that.textarea[0].selectionStart;

                    var textBeforeCursor = currentValue.substring(0, caretPos);
                    // Strip keyword
                    var index = textBeforeCursor.lastIndexOf('#');
                    textBeforeCursor = textBeforeCursor.substring(0, index) + textAppend;
                    _that.textarea.val(textBeforeCursor + currentValue.substring(caretPos));
                } else {
                    _that.textareaHolder[0].insertContent(textAppend,{"replace_from":"#","convert_bbcode" : true});
                }

                if (subjects_ids) {
                    _that.textareaHolder.attr('subjects_ids',subjects_ids);
                }

                if (canned_id) {
                    _that.textareaHolder.attr('canned_id',canned_id);
                }

				// Set cursor position
                if (_that.newEditor === false) {
                    if ('selectionStart' in _that.textarea[0]) {
                        _that.textarea[0].selectionStart = textBeforeCursor.length;
                        _that.textarea[0].selectionEnd = textBeforeCursor.length;
                    } else if (_that.textarea[0].setSelectionRange) {
                        _that.textarea[0].setSelectionRange(textBeforeCursor.length, textBeforeCursor.length);
                    } else if (_that.textarea[0].createTextRange) {
                        var range = _that.textarea[0].createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', textBeforeCursor.length);
                        range.moveStart('character', textBeforeCursor.length);
                        range.select();
                    }

                    _that.textarea[0].focus();
                }

				_that.stopSuggesting();
			});
			
			content.find('span.left-return').click(function(){
				container.show();
				content.html('').hide();
                content.parent().find('.canned-msg-preview').remove();
				_that.cannedMode = false;
			});
		});
		
		// Show first canned message list if there is only one tag matched
		if ($('#canned-hash-'+this.chat_id+' > li').length == 1) {
			$('#canned-hash-'+this.chat_id+' > li > a').trigger( "click" );
		} else {
            this.renderPreview(currentElement);
		}
	}
	
	return LHCCannedMessageAutoSuggest;
})();