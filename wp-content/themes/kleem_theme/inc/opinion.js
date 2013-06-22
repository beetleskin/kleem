/**
 * @author Stefan Kaiser
 */


jQuery(function($) { 
	
	$.fn.ratingBox = function( options ) {
		
		return this.each(function() {
			
			var $this = $(this);
			var msgID = $this.attr('postID');
			var active = true;
	
	
			function __construct() {
				$('.ratebutton', $this).click(rateClicked);
			}
			
			
			function setActive() {
				active = true;
			}
			
			
			function setInactive() {
				active = false;
			}
	
	
			function rateClicked(event) {
				// unbind the buttons
				if(!active) {
					return;
				}
	
				// send the rating
				var delta = ($(this).hasClass('agreement')) ? 1 : -1;
				$.ajax({
					type : 'POST',
					url : kleem_ajax_config.ajaxurl,
					beforeSend: setInactive,
					data : {
						action : kleem_ajax_config.rateAction,
						bookboak : kleem_ajax_config.bookboak,
						rating : {
							msgID : msgID,
							val : delta,
						}
					},
					success : ratingSent,
					error : ajaxError,
					dataType : 'json'
				});
			};
	
	
			function ratingSent(data, textStatus, jqXH) {
				if(data.newBox) {
					$this.replaceWith( $(data.newBox).ratingBox() );
				}
			}
	
	
			function ajaxError(jqXHR, textStatus, errorThrown) {
				setActive();
			}
	
			// call constructor
			__construct();
		});
	}
		

	$(document).ready(function() {

		// init the rating boxes
		$('.ratingbox:not(.nopriv)').ratingBox();
	});

});
