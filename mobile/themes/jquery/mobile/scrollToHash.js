$(document).bind('pageshow',function(e) {
	var $anchor;
	$anchor = $(location.hash);
	if ($anchor) {
		// Get y pos of anchor element.
		var pos = $anchor.offset().top;

		// Don't use silentScroll() as it interferes with the automatic 
		// silentScroll(0) call done by JQM on page load. Instead, register
		// a one-shot 'silentscroll' handler that performs a plain
		// window.scrollTo() afterward.
		$(document).bind('silentscroll',function(e,data) {
			$(this).unbind(e);
			window.scrollTo(0, pos);
		});
	}
});
