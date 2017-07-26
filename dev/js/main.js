(function($, window, document, undefined) {

  $(function(){

    //Facebook Hash Fix
    function removeHash () { 
      var scrollV, scrollH, loc = window.location;
      if ("pushState" in history)
        history.pushState("", document.title, loc.pathname + loc.search);
      else {
        // Prevent scrolling by storing the page's current scroll offset
        scrollV = document.body.scrollTop;
        scrollH = document.body.scrollLeft;
        loc.hash = "";
        // Restore the scroll offset, should be flicker free
        document.body.scrollTop = scrollV;
        document.body.scrollLeft = scrollH;
      }
    }
    if(window.location.href.substr(-1) == "#" || window.location.hash == "#_=_") removeHash();

    var scrollTo = function(eventOrTarget){
      var target;
      if(eventOrTarget){
        if(eventOrTarget.target){
          eventOrTarget.preventDefault();
          target = jQuery(this).attr("href");
        } else {
          target = eventOrTarget;
        }
      } else {
         var target = location.hash;
      }
      if (target && $(target).length && $(target).is(':visible')){
        $('html,body').animate({
          scrollTop: ($(target).offset().top) - $('#mainNav .navbar-brand-black').height()
        },500);
      }

      return false;
    }
    $(document).on('click', 'a[href*=#]', scrollTo);
    if (location.hash && $(location.hash).length && $(location.hash).is(':visible')){
        $('body').hide();
        setTimeout(function(){
            $('html, body').scrollTop(0);
            $('body').show();
            scrollTo();
        },50);
    }

    function withUser(callback, errorCallback){
      $.ajax({
        method: 'GET',
        url:'https://api.tktpass.com/user/',
        dataType: 'json',
        success: function(data, textStatus, jqXHR){
          callback.apply(this, arguments);
        },
        error: function(jqXHR, textStatus, errorThrown){
          errorCallback.apply(this, arguments);
        }
      });
    }

    /* ##########
       # Navbar #
       ########## */

    $("#mainNav").affix({offset: {top: 150} });
    
  });

})(this.jQuery, this, this.document);