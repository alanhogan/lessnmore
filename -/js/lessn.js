jQuery(document).ready(function($) {
	
	var activeClass = "active",
	transition = 00;
	
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

		$(document).keydown(function(e){
			if ( e.keyCode == 37 || e.keyCode == 39) {
				var active = $("h2." + activeClass),
				left = (e.keyCode == 37),
				dir = ( left ) ? 'prev' : 'next',
				end = ( left ) ? 'last' : 'first',
				target = active[dir]("h2");
				if ( ! target.size() ) {
					target = $("h2:" + end);
				}
				target.click();
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