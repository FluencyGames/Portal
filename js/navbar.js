$(window).ready(function() {
	// Fixed positioning
	navbarOffset = $("#navbar").offset();
	$(window).bind("scroll", fixNavbar);
	
	// Set the navbar title (for mobile)
	var title = $("#navbar li.selected").text().trim();
	if (title) { // Signed in?
		$("#navbar .tiny-title > .title").text(title);
	}
	// If mobile, scroll to the navbar
	if ($(window).width() < 600) {
		var pos = $("#navbar").offset().top;
		setTimeout(function() {
			$("html, body").scrollTop(pos);
		}, 1);
	}
	// Add events to the nav menu
	$("#navbar .icon-menu," +
		"#navbar .overlay").on("click", function() {
		$("#navbar").toggleClass("open");
		if ($("#navbar").hasClass("open")) {
			document.body.style.overflowY = "hidden";
		} else {
			document.body.style.overflowY = "scroll";
		}
	});
});

function fixNavbar() {
	if ($(window).scrollTop() >= navbarOffset.top) {
		$("#navbar").addClass("fixed");
	} else {
		$("#navbar").removeClass("fixed");
	}
}