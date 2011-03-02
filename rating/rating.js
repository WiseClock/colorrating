$(document).ready(function() {
	$('.status').prepend("<div class='score_this'>(<a href='#'>rate this</a>)</div>");
	$('.score_this').click(function(){
		$(this).slideUp();
		return false;
	});
	
	$('.score a').click(function() {
		$(this).parent().parent().parent().addClass('scored');
		$.get("rating/rating.php" + $(this).attr("href") +"&update=true", {}, function(data){
			$('.scored').fadeOut("normal",function() {
				$(this).html(data);
				$(this).fadeIn();
				$(this).removeClass('scored');
			});
		});
		return false; 
	});
});