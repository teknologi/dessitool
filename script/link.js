$(document).ready(function() {


	$("#newscene").click(function() {
		$('#divscene').attr('class', '');
	});

	// mod2
	// var question_show = false;
	$("a.questext").click(function() {
		$(this).children().toggleClass("hide");
		question_show = true;
		return false;
	});

	$("#language").click(function() {
		$("#lang").toggleClass("hide");
		return false;
	});

});
