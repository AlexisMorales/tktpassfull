(function(window, document, $, undefined) {
  $(function(){
    /*
     * Menu Nav
     */
    var menu = document.getElementById('mob-menu'),
        WINDOW_CHANGE_EVENT = ('onorientationchange' in window) ? 'orientationchange' : 'resize';

    function toggleMenu() {
        menu.classList.toggle('open');
        document.getElementById('toggle').classList.toggle('x');
    }

    function closeMenu() {
        if (menu.classList.contains('open')) {
            toggleMenu();
        }
    }
    document.getElementById('toggle').addEventListener('click', function(e) {
        toggleMenu();
    });
    window.addEventListener(WINDOW_CHANGE_EVENT, closeMenu);
    
    $('#hostModal').modal({show:false});
    $("#host-link").click(function(e) {
        var $hm = $('#hostModal');
        if(!($hm.parent().is('body'))) $hm.appendTo(document.body);
        $hm.modal('show');
    });
    
    /*
     * Scroll Nav
     */
    var scrolling = false;
    var stopScrollTimeout = null;
    function checkNavScroll(){
        var $nav = $('.landing-navbar');
        var st = $(window).scrollTop();
        var h = $(window).height();
        if(st < h/2 && $nav.hasClass('scrolled')){
            setTimeout(function(){navbarTransitioning = false;},700);
            $nav.removeClass('done');
            $nav.removeClass('scrolled');
            $nav.find('.logo').attr('src','img/logo_w.svg');
        }
        else if(st >= h/2 && !$nav.hasClass('scrolled')){
            setTimeout(function(){$nav.addClass('done');},700);
            $nav.addClass('scrolled');
            $nav.find('.logo').attr('src','img/logo_f.svg');
        }
    }
    $(window).on('scroll',function(e) {
        if(stopScrollTimeout){
          clearTimeout(stopScrollTimeout);
          stopScrollTimeout = null;
        }
        scrolling = true;
        stopScrollTimeout = setTimeout(function(){scrolling = false;checkNavScroll();},500);
    });
    checkNavScroll();
		    
    /*
     * Login
     */
    $('#loginModal').modal({show:false});
    $("#login,#mob-login").click(function(e) {
        $('#loginModal').modal('show');
    });
    /*$("#loginModal #reg-btn").click(function(e) {
        $(this).parent().slideUp();
        $('.signup-form').slideDown();
    });
    $("#loginModal #email-btn").click(function(e) {
        $(this).parent().slideUp();
        $('.login-form').slideDown();
    });
    $("#loginModal").on('hidden.bs.modal',function(e) {
        var $p = $('#reg-btn').parent();
        if(!$p.is(':visible')){
            $p.show();
            $('.signup-form, .login-form').hide();
        }
    });*/
    $('#account').parent().on('show.bs.dropdown', function () {
      $(this).find('.fa').addClass('spin');
    });
    $('#account').parent().on('hide.bs.dropdown', function () {
      $(this).find('.fa').removeClass('spin');
    });
    
    /*
     * Landing
     */
    $("#landing .arrow").click(function(e) {
        $('html, body').animate({
            scrollTop: $(document.getElementById('section1')).offset().top-30
        }, 800);
    });
    
    /*
     * Events Carousel
     */
		String.prototype.stripSlashes = function(){
      return this.replace(/\\(.)/mg, "$1");
    }
    function updateEventExpand(slidePressed){
        $('.event-info-title').html($(slidePressed).find('.event-title').html());
        $('.event-info-time').html($(slidePressed).find('.event-time').html());
        $('.event-info-location a').attr('href','//maps.google.com/?q='+($(slidePressed).data('city') ? $(slidePressed).data('venue')+',%20'+$(slidePressed).data('city') : $(slidePressed).data('postcode')));
        $('.event-info-location span').html($(slidePressed).find('.event-location').text().trim());
        $('.event-info-description').html($(slidePressed).data('description').stripSlashes());
        if($(slidePressed).data('fbid')) $('.event-info-fb').css('display','initial').attr('href','//www.facebook.com/events/'+$(slidePressed).data('fbid')+'/');
			  else $('.event-info-fb').css('display','none');
    }
    $('#event-carousel').slick({
            speed: 400,
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: false,
            centerMode: true,
            focusOnSelect: true,
            initialSlide: 0,
            responsive: [{
              breakpoint: 768,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                initialSlide: 0
              }
            }]
    });
    updateEventExpand($('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide));
    setTimeout(function(){updateEventExpand($('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide))},500);
    $('.event-buttons label').on('click', function(){
        if($(this).hasClass('active')) return;
        if($(this).text().trim() == 'Upcoming')
            $('#event-carousel').slick('slickUnfilter');
        else 
            $('#event-carousel').slick('slickFilter','.hot');
        $('#event-carousel').slick('slickGoTo',0,true);
    });
    $('#event-carousel').on('beforeChange', function(event, slick){
        setTimeout(function(){updateEventExpand(slick.$slideTrack.find('.slick-current').first());},0);
    });
		function checkSoldOut(slick){
			if(!slick) slick = $('#event-carousel').slick('getSlick');
			if(slick.$slideTrack.find('.slick-current').data('status')=="waitlist"){
				$('.event-info-buttons .btn-primary').html('Add to waiting list');
				$('#buy-book-button').html($('#buy-book-button').html().replace('Book now', 'Add to waiting list'));
			} else {
				$('.event-info-buttons .btn-primary').html('Need a ticket?');
				$('#buy-book-button').html($('#buy-book-button').html().replace('Add to waiting list', 'Book now'));
			}
		}
		checkSoldOut();
    $('#event-carousel').on('afterChange', function(event, slick){
        checkSoldOut(slick);
    });
		$('#event-carousel .event').each(function(){
			var that = this;
			$.ajax({
				url:'https://maps.googleapis.com/maps/api/geocode/json?address='+encodeURIComponent($(this).data('postcode')),
        success:function(data){
					$('#event-'+that.id.substring(6)).data('city',data.results[0].address_components[3].short_name).find('.event-location').append(', '+data.results[0].address_components[3].short_name)
				}
			});
		});
    
    $('#buyModal').modal({show:false});
    $(".event-info-buttons .btn-primary").click(function(e) {
        if(!($('#account').is(':visible') || $('#mob-account').is(':visible'))){
          $('#login-required').show();
          $('#loginModal').on('hide.bs.modal',function(){
            $('#loginModal').off('hide.bs.modal');
            $('#login-required').hide();
          });
          $('#loginModal').modal('show');
          return;
        }
        $("#buyModal .modal-title").html($('.event-info-title').html().replace(', ','<span class="hidden-xs-down">, </span><span class="hidden-sm-up"><br></span>')+' <small> | '+$('.event-info-time').html()+'</small>');
			  if($('#event-carousel').slick('getSlick').currentSlide === 4) $('#buyModal .buy-options').hide();
		    else $('#buyModal .buy-options').show();
        $('#buyModal').modal('show');
    });
    window.buyInt = null;
    function setBuy(q){
      q = parseInt(q);
      if(isNaN(q) || q<1 || q>99) return false;
      $('#buy-quantity').html(q);
      var transport = $("#buy-option-1")[0].checked;
			var $currEvent = $('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide);
      var price = parseInt($currEvent.data('price'))/100;
      var transportPrice = parseInt($currEvent.data('transport'))/100;
      var total = (price+(transport?transportPrice:0))*q;
      $('#buy-book-button span').html(total.toFixed(2));
    }
    function increaseBuy(){
      setBuy(parseInt($('#buy-quantity').html())+1);
    }
    function decreaseBuy(){
      setBuy(parseInt($('#buy-quantity').html())-1);
    }
    $("#increase-buy-quantity").on('mousedown touchstart',function(e){
      if(window.buyInt) return;
      increaseBuy()
      window.buyInt = setInterval(increaseBuy,200);
      e.preventDefault();
      return false;
    });
    $("#increase-buy-quantity").on('mouseup touchend mouseout touchcancel',function(e){
      clearInterval(window.buyInt);
      window.buyInt = null;
      e.preventDefault();
      return false;
    });
    $("#decrease-buy-quantity").on('mousedown touchstart',function(e){
      if(window.buyInt) return;
      decreaseBuy();
      window.buyInt = setInterval(decreaseBuy,200);
      e.preventDefault();
      return false;
    });
    $("#decrease-buy-quantity").on('mouseup touchend mouseout touchcancel',function(e){
      clearInterval(window.buyInt);
      window.buyInt = null;
      e.preventDefault();
      return false;
    });
    $("#increase-buy-quantity, #decrease-buy-quantity").disableSelection();
    $("#buy-option-1-label").on('click',function(){
      $cb = $("#buy-option-1");
      $cb[0].checked = !$cb[0].checked;
      var q = parseInt($('#buy-quantity').html());
      var transport = $("#buy-option-1")[0].checked;
			var $currEvent = $('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide);
      var price = parseInt($currEvent.data('price'))/100;
      var transportPrice = parseInt($currEvent.data('transport'))/100;
      var total = (price+(transport?transportPrice:0))*q;
      $('#buy-book-button span').html(total.toFixed(2));
    });
    $("#buy-option-1").on('change',function(){
      var q = parseInt($('#buy-quantity').html());
      var transport = $("#buy-option-1")[0].checked;
			var $currEvent = $('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide);
      var price = parseInt($currEvent.data('price'))/100;
      var transportPrice = parseInt($currEvent.data('transport'))/100;
      var total = (price+(transport?transportPrice:0))*q;
      $('#buy-book-button span').html(total.toFixed(2));
    });
    $("#buyModal .fa-question-circle").on('click',function(e){
      $("#buy-option-1-info").slideToggle();
      e.stopPropagation();
      e.preventDefault();
      return false;
    });
    $("#buyModal").on('show.bs.modal',function(e) {
			  $('#buy-book-quantity span').html(Math.max(parseInt($('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide).data('quantity')),0));
        setBuy(1);
    });
    $("#buyModal").on('hidden.bs.modal',function(e) {
        if(!$('#buy-book-button').is(':visible')){
					$('#buyModal .modal-body').show();
					$('#buy-book-button').show();
					$('.alert-success, .btn-mytickets').hide();
				}
    });
    
    $('#sellModal').modal({show:false});
    $(".event-info-buttons .btn-secondary").click(function(e) {
        if(!($('#account').is(':visible') || $('#mob-account').is(':visible'))){
          $('#login-required').show();
          $('#loginModal').on('hide.bs.modal',function(){
            $('#loginModal').off('hide.bs.modal');
            $('#login-required').hide();
          });
          $('#loginModal').modal('show');
          return;
        }
        $("#sellModal .modal-title").html($('.event-info-title').html()+' <small> | '+$('.event-info-time').html().replace(', ','<span class="hidden-xs-down">, </span><span class="hidden-sm-up"><br></span>')+'</small>');
        $('#sellModal').modal('show');
    });
    window.sellInt = null;
		function setSellSliderMax(m){
			var currentProp = window.sellSlider.element.value/window.sellSlider.options.max;
			window.sellSlider.options.max = m;
			var v = parseInt(currentProp*m*10)/10;
			window.sellSlider.element.value = v;
			$('#sell-slider-value').html(v === 0 ? 'Free?!' : '£'+v.toFixed(2));
		}
    function setSell(q){
      q = parseInt(q);
      if(isNaN(q) || q<1 || q>99) return false;
      $('#sell-quantity').html(q);
      var transport = $("#sell-option-1")[0].checked;
			var $currEvent = $('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide);
      var price = parseInt($currEvent.data('price'))/100;
      var transportPrice = parseInt($currEvent.data('transport'))/100;
			if(window.sellSlider && window.sellSlider.options.max != price+(transport?transportPrice:0)) setSellSliderMax(price+(transport?transportPrice:0));
      $('#sell-book-button span').html(window.sellSlider ? (parseFloat(window.sellSlider.element.value)*q).toFixed(2) : "6.00");
    }
    function increaseSell(){
      setSell(parseInt($('#sell-quantity').html())+1);
    }
    function decreaseSell(){
      setSell(parseInt($('#sell-quantity').html())-1);
    }
    window.sellInt = null;
    $("#increase-sell-quantity").on('mousedown touchstart',function(e){
      if(window.sellInt) return;
      increaseSell()
      window.sellInt = setInterval(increaseSell,200);
      e.preventDefault();
      return false;
    });
    $("#increase-sell-quantity").on('mouseup touchend mouseout touchcancel',function(e){
      clearInterval(window.sellInt);
      window.sellInt = null;
      e.preventDefault();
      return false;
    });
    $("#decrease-sell-quantity").on('mousedown touchstart',function(e){
      if(window.sellInt) return;
      decreaseSell();
      window.sellInt = setInterval(decreaseSell,200);
      e.preventDefault();
      return false;
    });
    $("#decrease-sell-quantity").on('mouseup touchend mouseout touchcancel',function(e){
      clearInterval(window.sellInt);
      window.sellInt = null;
      e.preventDefault();
      return false;
    });
    $("#increase-sell-quantity, #decrease-sell-quantity").disableSelection();
    $("#sell-option-1-label").on('click',function(){
      $cb = $("#sell-option-1");
      $cb[0].checked = !$cb[0].checked;
      setSell(parseInt($('#sell-quantity').html()));
    });
    $("#sell-option-1").on('change',function(){
      setSell(parseInt($('#sell-quantity').html()));
    });
    $("#sellModal .fa-question-circle").on('click',function(e){
      $("#sell-option-1-info").slideToggle();
      e.stopPropagation();
      e.preventDefault();
      return false;
    });
    $("#sellModal").on('show.bs.modal',function(e) {
			  if(window.sellSlider) setSell(1);
    });
    function calculateChance(full,sell){
			return parseInt(100-Math.pow(sell/full,6.5)*30)
    }
    function updateSellSliderValue(){
			var v = parseFloat($('#sell-slider').val());
	    $('#sell-slider-value').html(v === 0 ? 'Free?!' : '£'+v.toFixed(2));
      setSell(parseInt($('#sell-quantity').html()));
      var transport = $("#sell-option-1")[0].checked;
			var $currEvent = $('#event-carousel').slick('getSlick').$slides.eq($('#event-carousel').slick('getSlick').currentSlide);
      var price = parseInt($currEvent.data('price'))/100;
      var transportPrice = parseInt($currEvent.data('transport'))/100;
	    $('#sell-slider-chance span').html(calculateChance(price+(transport?transportPrice:0),v));
    }
    $("#sellModal").on('shown.bs.modal',function(e) {
			  $("#sellModal").off('shown.bs.modal');
        window.sellSlider = new Powerange($('#sell-slider')[0],{callback: updateSellSliderValue, decimal: true, min: 0, max: 6, start: 4, step: 0.1, hideRange: true });
				setSell(1);
    });
    $("#sellModal").on('hidden.bs.modal',function(e) {
        if(!$('#sell-book-button').is(':visible')) $('#sell-book-button').show().next().hide();
    });
    $("#sell-book-button").on('click',function(e) {
        $('#sell-phone-number').parent().removeClass('shake has-danger');
        var mobNum = $('#sell-phone-number').val().replace(/\s/g,'');
        if(!mobNum || !/^\+?\d+$/.test(mobNum) || mobNum.length < 6){
          $('#sell-phone-number').parent().addClass('shake has-danger');
          return false;
        }
        if(!($('#account').is(':visible') || $('#mob-account').is(':visible'))){
          $('#sellModal').modal('hide');
          $('#login-required').show();
          $('#loginModal').on('hide.bs.modal',function(){
            $('#loginModal').off('hide.bs.modal');
            $('#login-required').hide();
          });
          $('#loginModal').modal('show');
          return;
        }
        var slick = $('#event-carousel').slick('getSlick');
        var resale = {
          event_id: slick.$slides.eq(slick.currentSlide).attr('id').substr(6),
          quantity: parseInt($('#sell-quantity').html()),
          transport: $('#sell-option-1')[0].checked,
          mobile: mobNum,
          price:  parseInt(parseFloat($('#sell-slider-value').html().substr(1))*100)
        }
        $.ajax({
          url: '/api/resell',
          method: 'POST',
          data: resale,
    			dataType: 'json',
          success: function(data, textStatus, jqXHR){
						console.log(data);
            if(data.tktpass){
              setSell(1);
              $('#sell-book-button').hide().next().slideDown();
            } else
              alert('Sorry a fake error occured sending your request. Please try again later.');
          },
          error: function( jqXHR, textStatus, errorThrown){
            alert('Sorry a real error occured sending your request. Please try again later.');
          }
        });
    });
    
    /*
     * Testimonials
     */
    $('#testimonial-carousel').slick({
        speed: 800,
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        arrows: false,
        autoplay: true,
        autoplaySpeed: 6500,
        dots: true
    });
    
    /*
     * Footer
     */
    $('#contactModal').modal({show:false});
    $("#contact-us").click(function(e) {
        $('#contactModal').modal('show');
    });
    $("#contact-us-send").click(function(e) {
        $('#contactModal input, #contactModal textarea').parent().removeClass('animated shake has-danger');
        var hasError = false;
        if(!$('#account').data('name') && $('#contact-us-name').val().length < 2){
          hasError = true;
          $('#contact-us-name').parent().addClass('animated shake has-danger');
        }
        if(!$('#account').data('email') && !/[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z][A-Za-z]+)+/.test($('#contact-us-email').val())){
          hasError = true;
          $('#contact-us-email').parent().addClass('animated shake has-danger');
        }
        if($('#contact-us-message').val().length < 8){
          hasError = true;
          $('#contact-us-message').parent().addClass('animated shake has-danger');
        }
        //if(!grecaptcha.getResponse()) 
        //  hasError = true;
        if(hasError) return false;
        $.ajax({
          method: 'POST',
          url: '/api/contact',
    			dataType: 'json',
          data: {
            name: $('#contact-us-name').val(),
            email: $('#contact-us-email').val(),
            message: $('#contact-us-message').val()
          },
          success: function(data, textStatus, jqXHR){
						console.log(data);
            if(data.tktpass){
              $('#contactModal textarea').val('');
              $('#contactModal .modal-footer button').slideUp().last().next().slideDown();
            } else
              alert('Sorry an error occured sending your request. Please try again later.');
          },
          error: function( jqXHR, textStatus, errorThrown){
            alert('Sorry a server error occured sending your request. Please try again later.');
          }
        });
    });
    $('#contactModal').on('hidden.bs.modal',function(){
      $('#contactModal input, #contactModal textarea').parent().removeClass('animated shake has-danger');
      $('#contactModal .modal-footer button').show().last().next().hide();
    });
    
    $("footer .brand").click(function(e) {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
        e.preventDefault();
        return false;
    });
  });
})(this, this.document, this.jQuery);