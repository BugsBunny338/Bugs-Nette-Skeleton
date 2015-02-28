
$(function(){

	// foundation bootstrap
	$(document).foundation({
		topbar: {
			// custom_back_text: true,
			back_text: '- zpět -'
		}
	});

	// anchor jscript scroll
	$('a[href^=#]').click(function(i, el) {
		var anchorId = $(this).attr('href').substr(1),
	    	body = $("body");
		if (anchorId.length)
  		{
  			body.animate({
	    		scrollTop: $('#' + anchorId).offset().top
	  		}, 400, 'swing', function() { /*callback*/ });
	  		return false;
	  	}
	});

	// form delete confirmation
	$(function() {
	    $( ".confirm" ).click(function(event) {
	        var result = confirm("Jste si jistý/á?");
	        if (!result)
	        {
	            event.preventDefault();
	        }
	    });
	});

});
