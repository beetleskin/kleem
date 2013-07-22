/**
 * @author Stefan Kaiser
 */


jQuery(function($) { 
	
	
	$.fn.ajaxPaging = function( options ) {
		//build main options before element iteration
		var opts = $.extend({}, {
			maxpage : 1
		}, options);

		//iterate and reformat each matched element
		return this.each(function() {
			var $this = $(this);

			//get the variables of query
			var maxpages = opts.maxpages;
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
						query : query
						
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
						$('.ratingbox:not(.nopriv)', newPosts).ratingBox();
						
						
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

		/** loadingImage function is used to show loading image until Ajax request complete */
		function pendingState() {
			$('#ajax_pagination_btn').hide();
			$("._ajaxpaging_loading").show();
		}

		/** hide the loading image */
		function hideloading() {
			$("._ajaxpaging_loading").hide();
		}
	}
	

	$(document).ready(function() {
		// init dynamic ajax paging
		var paging_btn  = $("#ajax_pagination_btn");
		if(paging_btn.length > 0) {
			paging_btn.ajaxPaging({
			    maxpages: ajax_post_paging_config.maxpages,
			    query: ajax_post_paging_config.query
			});
		}
	});
	
});
