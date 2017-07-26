window.app = (function(window, document, Framework7, $$, undefined) {

  // Exported selectors engine through closure arguments

  // Initialize your app
  var myApp = new Framework7();

  // Add view
  var homeView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
  });

  var listView = myApp.addView('#view-list', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
  });

  var settingsView = myApp.addView('#view-settings', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
  });

  //List view data
  var checkedin = 0;
  var total = 12;

  // Callbacks to run specific code for specific pages, such as:

  //Show progressbar on list index init
  myApp.onPageInit('list-index', function(page) {
    var container = $$('body');
    if (container.children('.progressbar, .progressbar-infinite').length) return; //don't run all this if there is a current progressbar loading
    myApp.showProgressbar(container, (checkedin/total)*100);
  }).trigger(); //And trigger it right in case we start on this page. TODO: Check if we should trigger or not

  //Show/hide progressbar on tab click
  $$(document).on('click', '.tabbar a', function (e) {
    if($$(this).attr('data-tab') !== '#view-list'){
      myApp.hideProgressbar($$('body'));
      return;
    }
    var container = $$('body');
    if (container.children('.progressbar, .progressbar-infinite').length) return; //don't show if already visible
    myApp.showProgressbar(container, (checkedin/total)*100);
  });

  //Form utility function
  function isOn(data, prop){
    if(!(prop in data))
      return false;
    if(!data[prop])
      return false;
    if(!Array.isArray(data[prop]))
      return !!data[prop]
    else {
      return data[prop].length > 0 && data[prop][0] === "on";
    }
  }

  //Check-in action
  function checkin($li){
    myApp.alert('Checked in '+$li.find('.item-title').html(), 'tktpass');
    myApp.swipeoutClose($li);
    $li.css({opacity:0.2});
    checkedin++;
    var container = $$('body');
    if (container.children('.progressbar, .progressbar-infinite').length)
      myApp.setProgressbar(container, (checkedin/total)*100);
  }
  $$('.checkin').on('click', function () {
    var $li = $$(this).parent().parent();
    var options = myApp.formGetData('form-options');
    if(!isOn(options, 'overswipe') && $li.find('.swipeout-overswipe').hasClass('swipeout-overswipe-active')){
      $li.find('.swipeout-overswipe').removeClass('swipeout-overswipe-active');
      return false;
    }
    if(isOn(options, 'confirm')){
      myApp.confirm('Check in '+$$(this).parent().prev().find('.item-title').text()+'?', 'tktpass', function () {
          checkin($li);
      });
    } else {
      checkin($$(this).parent().parent());
    }
  });

  return myApp;

})(this, this.document, this.Framework7, this.Dom7)