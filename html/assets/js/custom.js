jQuery(document).ready(function() {

    
	/*
		Preloader
	
	
  	$(window).load(function() {

   	// will first fade out the loading animation 
    	$("#status").fadeOut("slow"); 

    	// will fade out the whole DIV that covers the website. 
    	$("#preloader").delay(500).fadeOut("slow").remove();      

  	}) */

  var tktpassNext = false;
  $("#typed").typed({
    strings: ["Plans change", "Are your tickets flexible?^1000", "Now they will be.^1000","Sign up"],
    typeSpeed: 90,
    startDelay: 400,
    backSpeed: 40,
    backDelay: 2000,
    showCursor: false
  });

	/*
		Final Countdown Settings
	*/
	var finalDate = '2016/11/01';

	$('div.counter').countdown(finalDate)
   	.on('update.countdown', function(event) {

   		$(this).html(event.strftime('<div class="days-wrapper"><span class="days">%D</span><br>days</div>' + 
   										 	 '<div class="hours-wrapper"><span class="hours">%H</span><br>hours</div>' + 
   										 	 '<div class="minutes-wrapper"><span class="minutes">%M</span><br>minutes</div>' +
   										 	 '<div class="seconds-wrapper"><span class="seconds">%S</span><br>seconds</div>'));

   });  

});
