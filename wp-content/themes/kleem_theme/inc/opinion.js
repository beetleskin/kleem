/**
 * @author Stefan Kaiser
 */


jQuery(function($) { 
	
	
	ajaxPaging : {
	
		$.fn.ajaxpaging = function(options) {
			//build main options before element iteration
			var opts = $.extend({}, $.fn.ajaxpaging.defaults, options);

			//iterate and reformat each matched element
			return this.each(function() {
				var $this = $(this);

				//get the variables of query
				var maxpages = opts.maxpages;
				var template_part = opts.template_part;
				var query = opts.query;
				var paged = 1;
				var active = true;

				$this.bind('click', function() {
					paged++;

					//Ajax request for query next post item from the database
					$.ajax({
						type : "POST",
						url : kleem_ajax_config.ajaxurl,
						data : {
							paged : paged,
							bookboak : kleem_ajax_config.bookboak,
							action : kleem_ajax_config.morePostsAction,
							template_part : template_part,
							query : query,
							
						},
						dataType : "html",
						beforeSend : pendingState,
						success : function(msg) {
							
							
							//append the new content
							var newPosts = $(msg).css('display', 'none');
							
							// stupid error check
							if(newPosts.attr('id') == "securityErrorMessage" || newPosts.length == 0 || maxpages == paged) {
								active = false;
							}
							
							// init the rating boxes
							$('.ratingbox:not(.nopriv)', newPosts).each(function(e) {
								ratingboxes.push(new RatingBox(this));
							});
							
							
							$("#ajax-post-container").append(newPosts);
							newPosts.fadeIn();
							

							/** hide next link for fetch more post if you are in the last page */
							if( active ) {
								$('#ajax_pagination_btn').show();
							} else {
								$(this).click(false);
							}
								
							/* trigger CompletPagination callback */
							$("#ajax_pagination_btn").trigger("complete-paginate");
						},
						complete : hideloading
					});
					return false;
				});

			});
		};

		/** loadingImage function is used to show loading image until Ajax request complete */
		function pendingState() {
			$('#ajax_pagination_btn').hide();
			$("._ajaxpaging_loading").show();
		}

		/** hide the loading image */
		function hideloading() {
			$("._ajaxpaging_loading").hide();
		}

		// plugin defaults
		$.fn.ajaxpaging.defaults = {
			maxpage : 1
		};
	}
	

	
	message_post : {
		var ratingboxes = new Array();

		function RatingBox(ratingbox) {
			var rb = this;
			this.box = $(ratingbox);
			this.msgID = rb.box.attr('postID');
			this.active = true;

			this.__construct = function() {
				$('.ratebutton', rb.box).each(function(){
					var delta = ($(this).hasClass('agreement')) ? 1 : -1;
					$(this).bind('click', {rateDelta: delta}, rb.rateClicked);
				});
			}
			
			this.setActive = function() {
				rb.active = true;
			}
			
			this.setInactive = function() {
				rb.active = false;
			}

			this.rateClicked = function(event) {
				// unbind the buttons
				if(!rb.active) {
					return;
				}

				// send the rating
				$.ajax({
					type : 'POST',
					url : kleem_ajax_config.ajaxurl,
					beforeSend: rb.setInactive,
					data : {
						action : kleem_ajax_config.rateAction,
						bookboak : kleem_ajax_config.bookboak,
						rating : {
							msgID : rb.msgID,
							val : event.data.rateDelta,
						}
					},
					success : rb.ratingSent,
					error : rb.ajaxError,
					dataType : 'json'
				});
			};

			this.ratingSent = function(data, textStatus, jqXH) {
				if(data.newBox) {
					$('.ratebutton', rb.box).unbind('click');
					var oldBox = rb.box;
					rb.box = $(data.newBox);
					oldBox.replaceWith(rb.box);
					rb.__construct();
				}
				rb.setActive();
			}

			this.ajaxError = function(jqXHR, textStatus, errorThrown) {
				rb.setActive();
			}

			// call constructor
			this.__construct();
		}
		

		$(document).ready(function() {

			// init the rating boxes
			$('.ratingbox:not(.nopriv)').each(function(e) {
				ratingboxes.push(new RatingBox(this));
			});
			
			// init dynamic ajax paging
			var paging_btn  = $("#ajax_pagination_btn");
			if(paging_btn.length > 0) {
				paging_btn.ajaxpaging({
				    template_part: ajax_post_paging_config.template_part,
				    maxpages: ajax_post_paging_config.maxpages,
				    query: ajax_post_paging_config.query
				});
			}
		});
	}
});
