/**
 * @author Stefan Kaiser
 */

jQuery(function($) { post_submit_form : {


		function TextFieldCounter(textfield, maxChars) {
			var me = this;
			this.f = textfield;
			this.max = maxChars;
			this.counterHTML;

			this.__contruct = function() {
				me.counterHTML = $('<div class="tfcounter"><span>0</span>/' + me.max + '</div>');
				me.f.after(me.counterHTML);
				me.f.bind('keydown', me.observeInput);
				me.f.bind('keyup', me.observeInput);
			}
			
			this.observeInput = function(event) {
				var chars = me.f.val().length;
				if(chars > me.max) {
					me.f.val(me.f.val().substr(0, me.max));
					chars = me.max;
				}

				$('span', me.counterHTML).html(chars);
			}
			
			
			// call constructor
			this.__contruct();
		}

		function Accordeon(dom_selector_root, dom_selector_head) {
			$(dom_selector_head + ':not(.obligated)', dom_selector_root).click(function() {
				$(this).next().slideToggle();
				return false;
			}).next();
		}

		function MultiSelector(dom_selector_root, dom_selector_select) {
			var options = {
				minWidth: "180",
  				noneSelectedText: "Themen auswählen",
				header : false,
				selectedList : 2,
			}

			$(dom_selector_select, dom_selector_root).multiselect(options);
		}

		function AutoSuggestor(dom_selector_root, dom_selector_input) {
			var options = {
				extraParams : "&action=" + messageform_config.suggestTagsAction + "&bookboak=" + messageform_config.bookboak,
				queryParam : messageform_config.suggestTagsQueryParam,
				selectedItemProp : "name",
				searchObjProps : "name",
				minChars : messageform_validation.sub_topic_min_chars,
				asHtmlID : "custom_topics",
				startText : "Thema ...",
				emptyText : "neues Thema mit TAB",
				limitText : "nur maximal " + messageform_validation.custom_topics_max + " Themen sind erlaubt",
				selectionLimit : messageform_validation.custom_topics_max,
			};

			$(dom_selector_input, dom_selector_root).autoSuggest(messageform_config.ajaxurl, options);
		}

		function MessageForm(dom_selector) {
			var m = this;
			this.fileApiSupported = window.FormData !== undefined;
			this.form = $(dom_selector);
			this.submit = $('button#opinion_submit', dom_selector);
			this.inputs = {
				'msg' : $('#message', dom_selector),
				'descr' : $('#description', dom_selector),
				'link' : $('#reference_input', dom_selector),
				'topics' : $('#topics', dom_selector),
				'custom_topics' : $('#as-values-custom_topics', dom_selector),
				'maxfilesize' : $('#maxfilesize', dom_selector),
			};
			this.sending = false;
			this.progressbar = $('.progress', dom_selector);

			this.__construct = function() {

				new TextFieldCounter(m.inputs.msg, messageform_validation.message_max_chars);
				new TextFieldCounter(m.inputs.descr, messageform_validation.description_max_chars);

				var options = {
					beforeSubmit : m.beforeSubmit,
					success : m.successHandler,
					error : m.transmissionErrorHandler,
					url : messageform_config.ajaxurl,
					dataType : "json",
					data : {
						'action' : messageform_config.submitAction,
						'bookboak' : messageform_config.bookboak
					},
					uploadProgress : m.uploadProgress,

				};

				m.form.ajaxForm(options);
				m.submit.click(function() {
					
					if( $(this).attr('nopriv') !== undefined ) {
						
						$( (jQuery.browser.webkit)? "body": "html").animate({ scrollTop: 0 }, "slow", function(){
							$('#errormessage', m.form).fadeTo(400, 0.2).fadeTo(400, 1.0);
						});
						
					} else {
						
						m.form.submit();
						
					}
					
					// no further click handling
					return false;
				});
			}
			
			
			
			// collect the data
			this.beforeSubmit = function(formData, jqForm, options) {
				if(m.sending) {
					return false;
				} else {
					m.sending = true;
				}
				
				var file = null;
				for (var i=0; i < formData.length; i++) {
					if(formData[i].type == 'file') {
						if(formData[i].value !== undefined) {
							file = formData[i];
						}
						break;
					}
				}

				// handle file
				if(file != null && m.fileApiSupported) {
					// validate
					if( file.value.fileSize > messageform_validation.image_size_max) {
						m.errorHandler([{
							element: file.name, 
							message: "<p>Bilder dürfen nicht größer als " + (messageform_validation.image_size_max / 1000000) + " MB groß sein.</p>",
						}]);
						m.sending = false;
						return false;
						
						// show upload bar
					} else {
						if( m.progressbar.css('display') == 'none')
							m.progressbar.fadeIn();
					}
				} else {
					// hide uploadbar
					if( m.progressbar.css('display') != 'none')
						m.progressbar.fadeOut();
				}

				
				
				formData.length = 0;
				formData.push( {
					name : m.inputs.msg.attr('name'),
					value : m.inputs.msg.val()
				});
				formData.push( {
					name : m.inputs.descr.attr('name'),
					value : m.inputs.descr.val()
				});
				formData.push( {
					name : m.inputs.link.attr('name'),
					value : m.inputs.link.val()
				});
				formData.push( {
					name : m.inputs.topics.attr('name'),
					value : m.inputs.topics.val()
				});
				formData.push( {
					name : $('#custom_topics').attr('name'),
					value : m.inputs.custom_topics.val()
				});
				if(file != null) {
					formData.push( file );
				}

			}

			this.successHandler = function(response, statusText, xhr, $form) {
				m.sending = false;
				
				if( m.progressbar.css('display') != 'none') {
					m.progressbar.fadeOut();
				}

				// error handling
				if(response.error != null) {
					m.errorHandler(response.error);
				} else if(response.securityError != null) {
					m.securityErrorHandler(response.securityError);
				} else if(response.success != null) {
					m.submitSuccessHandler(response);
				}
			}

			this.uploadProgress = function(event, pos, total, percentage) {
				if( percentage != null && m.progressbar.css('display') != 'none') {
					$('.bar', m.progressbar).css('width', percentage + '%');
					$('.percent', m.progressbar).html(percentage + '%');
				}
			}

			this.transmissionErrorHandler = function(param) {
				m.sending = false;
				m.form.animate({
					opacity : 'toggle',
					height : 'toggle'
				}, "slow", function(event) {
					var errorDiv = $('<div id="connectionerror"><p>Hoppla, da ist was schief gelaufen. Bitte versuch es später noch einmal.</p></div>');
					errorDiv.css('display', 'none');
					m.form.replaceWith(errorDiv);
					errorDiv.animate({
						opacity : 'toggle',
						height : 'toggle'
					}, "slow");
				});

				setTimeout("location.reload()", 5000);
			}

			this.errorHandler = function(errors) {

				// remove all errors
				$('.formerror', m.form).removeClass('formerror');

				$('#errormessage', m.form).animate({
					opacity : 'toggle',
					height : 'toggle'
				}, 600, function(event) {
					$('#errormessage', m.form).empty();
					
					for (var e=0; e < errors.length; e++) {
						if(errors[e].element != null && errors[e].message != null) {

							// display error message
							$('#errormessage', m.form).append('<p>' + errors[e].message + '</p>');

							// set multiselect error
							if(errors[e].element == 'topics') {

								// all normal elements
							} else {
								$('[name="' + errors[e].element + '"]', m.form).addClass('formerror');
							}

						}
					}
						
				});
				
				$('#errormessage', m.form).animate({
					opacity : 'toggle',
					height : 'toggle'
				}, "slow");
				
				$("html, body").animate({ scrollTop: 0 }, "slow");
			}

			this.securityErrorHandler = function(securityError) {

				if(securityError.message != null) {
					m.form.animate({
						opacity : 'toggle',
						height : 'toggle'
					}, "slow", function(event) {
						var errorDiv = $(securityError.message);
						errorDiv.css('display', 'none');

						m.form.replaceWith(errorDiv);
						errorDiv.animate({
							opacity : 'toggle',
							height : 'toggle'
						}, "slow");
					});

					setTimeout("location.reload()", 5000);
				} else {
					location.reload();
				}
			}

			this.submitSuccessHandler = function(response) {
				m.form.animate({
					opacity : 'toggle',
					height : 'toggle'
				}, "slow", function(event) {
					var successDiv = $(response.success);
					successDiv.css('display', 'none');

					m.form.replaceWith(successDiv);
					successDiv.animate({
						opacity : 'toggle',
						height : 'toggle'
					}, "slow");
				});
			}
			
			
			// call constructor
			this.__construct();
		}


		$(document).ready(function() {
			var accordeon = new Accordeon('#messageform', '.itemhead');
			var multiselect = new MultiSelector('#messageform', '#topics');
			var autosuggest = new AutoSuggestor('#messageform', '#custom_topics');

			var mf = new MessageForm('#messageform:not(.nopriv)');

		});
	}

});
