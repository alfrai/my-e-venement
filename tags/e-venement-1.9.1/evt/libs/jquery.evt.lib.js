if(window.jQuery) {
var jquery = true;
function jquery_reinit()
{
	$('#personnes > span.pers > a').each(function(){
		$(this).mouseover(function() {
			$('#ficheindiv').removeClass('display'); $('#ficheindiv').show("slow");
		});
	});
	
	$('#ficheindivclose').click(function() {
		$(this).parent().fadeOut('slow');
	});
}

$(document).ready(function() {
});
}
