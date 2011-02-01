jQuery(document).ready(function($) {
	
	var activeClass = "active",
	transition = 0;
	
	function init() {
		var div = $("<div id='tables'></div>");
		div.appendTo("#wrap");
		
		$("h2").each(function(index) {
			var self = $(this),
			table = self.next("table"),
			id = table.attr("id");

			self.attr('data-target', id).click(showTable);;
			table.attr({id: id}).hide().appendTo(div);
		});

		$("h2:first").click();
		
		$(document).keydown(function(e){
			if (e.keyCode == 37) { 
				var active = $("h2."+activeClass),
				prev = active.prev("h2");
				console.log(prev.size());
				if ( ! prev.size() ) {
					prev = $("h2:last");
				}
				prev.click();
				return false;
			}
			else if ( e.keyCode == 39 ) {
				var active = $("h2."+activeClass),
				next = active.next("h2");
				if ( ! next.size() ) {
					next = $("h2:first");
				}
				next.click();
				return false;
			}
		});
		
		$("body").addClass("clicky");
		
		$(window).bind( 'hashchange', goToHash).trigger('hashchange');
	}
	if ( $("body").attr("id") === "stats" ) {
		init();
	}
	
	function goToHash() {
		var hash = location.hash,
		table = $(hash),
		id = hash.replace('#', ''),
		h2 = $("h2[data-target="+id+"]");
		
		// if nothing, default to clicking on first h2
		if ( ! table.size() ) {
			$("h2:first").click();
			return false;
		}
		
		$("#tables table").hide();
		table.fadeIn(transition);
		
		$("h2").removeClass(activeClass);
		h2.addClass(activeClass);
	}
	
	function showTable(event) {
		var self = $(this),
		hash = "#" + self.attr("data-target");
		$.bbq.pushState(hash);
	}
	
	
});