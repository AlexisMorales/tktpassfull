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
    
  });
  
    $('#account').parent().on('show.bs.dropdown', function () {
      $(this).find('.fa').addClass('spin');
    });
    $('#account').parent().on('hide.bs.dropdown', function () {
      $(this).find('.fa').removeClass('spin');
    });
})(this, this.document, this.jQuery);