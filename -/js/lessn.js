jQuery(document).ready(function($) {
	
	var activeClass = "active",
	transition = 0;
	
	function init() {
		var div = $("<div id='tables'></div>");
		div.appendTo("#wrap");
		
		$("h2").each(function(index) {
			var self = $(this),
			table = self.next("table"),
			id = self.text().replace(/\s+/g,'-').replace(/[^\w+-]+/, '').toLowerCase();

			self.data('target', id).click(showTable);;
			table.attr({id: id}).hide().appendTo(div);
		});

		$("h2:first").click();
		
		$(document).keydown(function(e){
			if (e.keyCode == 37 || e.keyCode == 38) { 
				var active = $("h2."+activeClass),
				prev = active.prev("h2");
				console.log(prev.size());
				if ( ! prev.size() ) {
					prev = $("h2:last");
				}
				prev.click();
				return false;
			}
			else if ( e.keyCode == 39 || e.keyCode == 39 ) {
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
	}
	if ( $("body").attr("id") === "stats" ) {
		init();
	}
	
	function showTable() {
		var self = $(this),
		table = $("#" + self.data("target") );
		$("#tables table").hide();
		table.fadeIn(transition);
		
		$("h2").removeClass(activeClass);
		self.addClass(activeClass);
	}
	
	
});
