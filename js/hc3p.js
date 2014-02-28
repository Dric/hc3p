$(document).ready(function(){
    $(".address").each(function(){                         
        var embed ="<iframe width='425' height='350' frameborder='0' scrolling='no'  marginheight='0' marginwidth='0' src='https://maps.google.com/maps?&amp;q="+ encodeURIComponent( $(this).text() ) +"&amp;output=embed'></iframe>";
        $(this).html(embed);             
    });
		$(".photo").each(function(){
			if ($(this).hasClass('rotate-left')){
				var rot = -1;
			}else if ($(this).hasClass('rotate-right')){
				var rot = 1;
			}else{
				var rot = Math.random() < 0.5 ? -1 : 1;
			}
			if ($(this).data('rotate-coeff')){
				var coeff = $(this).data('rotate-coeff');
			}else{
				var coeff = 10;
			}
			$(this).rotate(Math.floor((Math.random()*rot*coeff)));
		});
		$(".backstretch").backstretch();
		$('.navbar ul li a').bind('click', function(e) {
			e.preventDefault();
			$('html, body').animate({ scrollTop: $(this.hash).offset().top - 30 }, 500);
		});
});