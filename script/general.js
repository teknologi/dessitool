$(document).ready(function() {
	$(".close").click(function() {
		$(this).parent().toggleClass("hide");
		return false;
	});

	$("#newscene").click(function() {
		$('#divscene').toggleClass("hide");
		return false;
	});

	// mod2
	var question_show = false;
	$("a.questext, a.moretext ").click(function() {
		$(this).children().toggleClass("hide");
		question_show = true;

		return false;
	});

	$("#language").click(function() {
		$("#lang").toggleClass("hide");
		return false;
	});
});
