(function(window, document, $, undefined) {
  $(function(){
    $('#price-carousel').slick({
            speed: 400,
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: false,
            centerMode: true,
            focusOnSelect: true,
            initialSlide: 0,
            draggable: false,
            accessibility: false,
            arrows: false
    }).hide();
    $('#tmm-btn').on('click',function(){
        if($(this).hasClass('active')){
          $('.button-row button').removeClass('active');
          $('#price-carousel').slideUp();
        } else {
          $('.button-row button').removeClass('active');
          $(this).addClass('active');
          if(!$('#price-carousel').is(':visible')){
            $('#price-carousel').slideDown({
              step: function(){$('body').scrollTop($(document).height())}
            });
          }
          $('#price-carousel').slick('getSlick').goTo(0);
        }
    });
    $('#price-btn').on('click',function(){
        if($(this).hasClass('active')){
          $('.button-row button').removeClass('active');
          $('#price-carousel').slideUp();
        } else {
          $('.button-row button').removeClass('active');
          $(this).addClass('active');
          if(!$('#price-carousel').is(':visible')){
            $('#price-carousel').slideDown({
              step: function(){$('body').scrollTop($(document).height())}
            });
          }
          $('#price-carousel').slick('getSlick').goTo(1);
        }
    });
    
    ////////////////////////////////////////////////
    
    var showIcon = new ui.Tween({
        values: {
            opacity: 1,
            length: {
                to: 65,
                ease: 'easeIn'
            }
        }
    });

    var spinIcon = new ui.Simulate({
        values: {
            rotate: -400
        }
    });

    var progressCompleteOutline = new ui.Tween({
        values: {
            rotate: '-=200',
            length: 100
        }
    });

    var progressCompleteTick = new ui.Tween({
        delay: 150,
        values: {
            length: 100,
            opacity: 1
        }
    });
    
    function animateToForm(){
      var $form = $('.form');
      $form.find('.fb-input').animate({opacity: 0}, {
        step: function(now, mx) {
          //as the opacity of current_fs reduces to 0 - stored in "now"
          //1. scale current_fs down to 80%
          scale = 1 - (1 - now) * 0.2;
          //2. bring next_fs from the right(50%)
          left = (now * 50)+"%";
          //3. increase opacity of next_fs to 1 as it moves in
          opacity = 1 - now;
          $(this).css({
            'transform': 'scale('+scale+')'
          });
        }, 
        duration: 600,
        complete: function(){
          $form.css({height:$form.height()});
          $form.find('.fb-input').css('display','none');
          $form.find('.steps-wrap').css('display','');
          $form.animate({height:$form.find('.steps-wrap').height()},{complete:function(){$form.css('height','')}});
          $form.find('.steps-wrap').animate({opacity: 1}, {
            step: function(now, mx) {
              //as the opacity of current_fs reduces to 0 - stored in "now"
              //1. scale current_fs down to 80%
              scale = 0.8 + now * 0.2;
              //2. bring next_fs from the right(50%)
              r = (-200+now*200)+"px";
              //3. increase opacity of next_fs to 1 as it moves in
              opacity = 1 - now;
              $(this).css({
                'right': r
              });
            }, 
            duration: 600,
            complete: function(){
              $('.form .steps').css({'position':'relative','right':'','left':'0'});
            }
          });
        }
      });
    }
    
    var $inputs = $('.step input');
    function checkVal(input){
      if(!input.val) input = $(input);
      if(input[0].tagName === 'DIV') input.val = input.text;
      if(input.val().trim() !== "")
        input.addClass('filled');
      else {
        input.removeClass('filled');
      }
      var reg = new RegExp(input.attr('pattern'));
      if(reg.test(input.val().trim()))
        input.addClass('valid');
      else
        input.removeClass('valid');
    }
    function fillForm(){
      $('#event-name').val('JagerMonster');
      $('#event-loc').val('Leamington Spa');
      $('#event-date').val('28/02/16');
      $('#event-time').val('22:00');
      $('#event-desc').html('<!--StartFragment--><span style="font-weight: 400; font-style: normal; text-decoration: none;">The downtown hype just keeps on going! After yet another sell-out event we\'re back for Friday Week 6 and are expecting the same scenes again!<span style="font-weight: 400; font-style: normal; text-decoration: none;">&nbsp;</span></span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">With an absolutely unbeatable atmosphere, huge tunes from all your favourite genres and the biggest crowd in town... You know where to be!</span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">Main room featuring the biggest dancefloor classics as well as new music from the world of house and chart remixes.<span style="font-weight: 400; font-style: normal; text-decoration: none;">&nbsp;</span></span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">Don\'t forget those dirty beats in the legendary R&amp;B Room upstairs with our resident DJ mixing in the old school with the new.</span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">And in the front room we bring you the finest old school and funky beats to get the dancefloor moving...<span style="font-weight: 400; font-style: normal; text-decoration: none;">&nbsp;</span></span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">Come and experience Warwick\'s biggest and longest running student night!</span><br style="font-weight: 400; font-style: normal; text-decoration: none;"><span style="font-weight: 400; font-style: normal; text-decoration: none;">----</span><span style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;">+ £1.50 Jager Bombs<br style="font-weight: 400; font-style: normal; text-decoration: none;">+ £1.50 Vodka Mixers<br style="font-weight: 400; font-style: normal; text-decoration: none;">+ £1.50 Bottles<br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;">----<br style="font-weight: 400; font-style: normal; text-decoration: none;"><br style="font-weight: 400; font-style: normal; text-decoration: none;">Be there early. QJumps available now! - Text 07411 414414 or any of the reps below to arrange collection.</span><!--EndFragment-->');
    }
    
    var showTick;
    function showSpinner() {
        $('#fb-input').attr('disabled',1);
        var progressIcon = document.querySelector('.progress-icon');

        var progressOutline = new ui.Actor({
            element: progressIcon.getElementById('tick-outline-path')
        });
        var progressTick = new ui.Actor({
            element: progressIcon.getElementById('tick-path')
        });

        progressOutline.start(showIcon).then(spinIcon);

        showTick = function() {
            $('.progress-icon .tick-icon').eq(1).attr('stroke','#6CC84E')
            progressOutline.start(progressCompleteOutline);
            progressTick.start(progressCompleteTick);
            fillForm();
            $inputs.each(function(i,e){
              checkVal(e);
            });
            $('#event-desc').blur();
            setTimeout(animateToForm,1200);
        }
        setTimeout(showTick,3000);
    }

    $('#fb-input').on('input',function(){
      var $errorMsg = $(this).parent().parent().next();
      if($(this).val() === ''){
        if($errorMsg.is(':visible')) $errorMsg.slideUp();
        return;
      }
      var reg = /.*?\/events\/(\d{12,})(?:\/|$|\?).*/;
      var match = reg.exec($(this).val());
      if(match && match.length){
        if($errorMsg.is(':visible'))
          $errorMsg.slideUp();
        showSpinner();
        console.log(match[1]);
      } else if($(this).val().length > 12) {
        $errorMsg.slideDown();
      }
    });
    
    $inputs.on('change',function(){
      checkVal(this);
    });
    //$('#event-time').timeEntry({ampmPrefix: ' ',timeSteps:[1, 5, 0],spinnerIncDecOnly:true,spinnerImage:'/img/spinnerUpDown.png',spinnerSize: [15, 16, 0]});
    $('#event-desc').on('blur', function(e) {
      checkVal(this);
    });
    var blurHandles = [];
    $inputs.on('focus',function(){
      $.each(blurHandles,function(i,e){
        if(e.target == this)
          clearTimeout(e);
      });
      $(this).addClass('focused');
    });
    $inputs.on('blur',function(){
      var $that = $(this);
      var handle = setTimeout(function(){$that.removeClass('focused');},200);
      handle.target = this;
      blurHandles.push(handle);
    });
    $('#event-desc').on('focus',function(){
      $.each(blurHandles,function(i,e){
        if(e.target == this)
          clearTimeout(e);
      });
      $(this).addClass('focused');
    });
    $('#event-desc').on('blur',function(){
      var $that = $(this);
      var handle = setTimeout(function(){$that.removeClass('focused');},200);
      handle.target = this;
      blurHandles.push(handle);
    });
    $('#event-desc').on('paste',function(e){
      e.preventDefault();
      var text = (e.originalEvent || e).clipboardData.getData('text/html') || (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
      
      var ALLOWED_TAGS = ["STRONG", "EM", "BLOCKQUOTE", "Q", "DEL", "INS", "A", "B", "U", "SPAN", "P", "BR"];

      function sanitize(el) {
          //Remove all tags from element `el' that aren't in the ALLOWED_TAGS list
          var tags = Array.prototype.slice.apply(el.getElementsByTagName("*"), [0]);
          for (var i = 0; i < tags.length; i++) {
              if (ALLOWED_TAGS.indexOf(tags[i].nodeName) == -1) {
                  usurp(tags[i]);
              }
          }
      }

      function usurp(p) {
          //Replace parent `p' with its children
          var last = p;
          for (var i = p.childNodes.length - 1; i >= 0; i--) {
              var e = p.removeChild(p.childNodes[i]);
              p.parentNode.insertBefore(e, last);
              last = e;
          }
          p.parentNode.removeChild(p);
      }
      
      function sanitizeString(string) {
          var div = document.createElement("div");
          div.innerHTML = string;
          sanitize(div);
          return div.innerHTML;
      }
      
      var $data = $('<div></div>').append(sanitizeString(text));
      
      // replace all styles except bold and italic
      $.each($data.find("*"), function(idx, el) {
          var $item = $(el);
          if ($item.length > 0){
             var saveStyle = {
                  'font-weight': $item.css('font-weight'),
                  'font-style': $item.css('font-style'),
                  'text-decoration': $item.css('text-decoration')
              };
            var attributes = $.map($item[0].attributes, function(attr) {
              return attr.name;
            });
            $.each(attributes, function(i, attr) {
              if(attr !== 'href')
                $item.removeAttr(attr);
            });
            $item.removeClass().css(saveStyle);
            if($item[0].tagName.toLowerCase() === 'a')
              $item.attr('target','_blank');
          }
      });

      // remove unnecesary tags (if paste from word)
      $data.children('style').remove();
      $data.children('meta').remove()
      $data.children('link').remove();
      
      function cursorPosition(toEnd) {
        var element = document.getElementById("event-desc");
        var caretOffset = 0;
        if (typeof window.getSelection != "undefined") {
            var range = window.getSelection().getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            caretOffset = preCaretRange.toString().length;
        } else if (typeof document.selection != "undefined" && document.selection.type != "Control") {
            var textRange = document.selection.createRange();
            var preCaretTextRange = document.body.createTextRange();
            preCaretTextRange.moveToElementText(element);
            preCaretTextRange.setEndPoint("EndToEnd", textRange);
            caretOffset = preCaretTextRange.text.length;
        }
        var divStr = $('#event-desc').html();
        return toEnd ? divStr.substring(caretOffset) : divStr.substring(0, caretOffset);
      }
      
      $(this).html(cursorPosition(false)+$data.html()+cursorPosition(true));
    });
    
    var createClicked = false;
    $('#create-link').on('click',function(){
      if(!createClicked){
        createClicked = true;
        animateToForm();
      }
    });
    
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.sexy-picture').addClass('valid').find('.uploaded').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    var firstBlur = true;
    $("#event-pic").click(function(){
        $('.sexy-picture .click-div').css('border-color','#6CC84E');
        firstBlur = true;
    });
    $("#event-pic").change(function(){
        readURL(this);
    });
    $("#event-pic").on('blur mousmove',function(){
        if(!$("#event-pic")[0].files.length)
          $('.sexy-picture').removeClass('valid');
        if(!firstBlur)
          $('.sexy-picture .click-div').css('border-color','');
        else firstBlur = false;
    });
    
    function animateNextStep(){
      var $form = $('.form');
      var $org = $form.find('.step.current');
      setTimeout(function(){
        $form.css('height',$form.height());
        $org.removeClass('current').next().removeClass('fadeOutRight').addClass('current fadeInRight');
        $form.animate({height:$form.find('.steps-wrap').height()},{complete:function(){$form.css('height','')}});
      },333);
      $org.addClass('fadeOutLeft');
      $('#progressbar li.active').next().addClass('active');
    }
    
    function animatePrevStep(){
      var $form = $('.form');
      var $org = $form.find('.step.current');
      setTimeout(function(){
        $form.css('height',$form.height());
        $org.removeClass('current').prev().removeClass('fadeOutLeft').addClass('current fadeInLeft');
        $form.animate({height:$form.find('.steps-wrap').height()},{complete:function(){$form.css('height','')}});
      },333);
      $org.addClass('fadeOutRight');
      $('#progressbar li.active').last().removeClass('active');
    }
    
    $('.animateNextStep').on('click',function(){animateNextStep()});
    $('.animatePrevStep').on('click',animatePrevStep);
    
    //////////////////////////////////////////////////////
    
    $("#event-date").datepicker({
      firstDay: 1,
      dayNamesMin: [ "S", "M", "T", "W", "T", "F", "S" ],
      dateFormat:'dd/mm/y',
      minDate: new Date()
    });
    
    //////////////////////////////////////////////////////
    
    //jQuery time
    var current_fs, next_fs, previous_fs; //fieldsets
    var left, opacity, scale; //fieldset properties which we will animate
    var animating; //flag to prevent quick multi-click glitches

    $(".next").click(function(){
      if(animating) return false;
      animating = true;

      current_fs = $(this).parent().css({'position': 'absolute'});
      next_fs = $(this).parent().next().css({'position': 'absolute'});

      //activate next step on progressbar using the index of next_fs
      $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

      //show the next fieldset
      next_fs.show(); 
      //hide the current fieldset with style
      current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
          //as the opacity of current_fs reduces to 0 - stored in "now"
          //1. scale current_fs down to 80%
          scale = 1 - (1 - now) * 0.2;
          //2. bring next_fs from the right(50%)
          left = (now * 50)+"%";
          //3. increase opacity of next_fs to 1 as it moves in
          opacity = 1 - now;
          current_fs.css({
            'transform': 'scale('+scale+')'
          });
          next_fs.css({'left': left, 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
          current_fs.hide();
          next_fs.css({'position': 'initial'});
          animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
      });
    });

    $(".previous").click(function(){
      if(animating) return false;
      animating = true;

      current_fs = $(this).parent().css({'position': 'absolute'});
      previous_fs = $(this).parent().prev().css({'position': 'absolute'});

      //de-activate current step on progressbar
      $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

      //show the previous fieldset
      previous_fs.show(); 
      //hide the current fieldset with style
      current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
          //as the opacity of current_fs reduces to 0 - stored in "now"
          //1. scale previous_fs from 80% to 100%
          scale = 0.8 + (1 - now) * 0.2;
          //2. take current_fs to the right(50%) - from 0%
          left = ((1-now) * 50)+"%";
          //3. increase opacity of previous_fs to 1 as it moves in
          opacity = 1 - now;
          current_fs.css({'left': left});
          previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
          current_fs.hide();
          previous_fs.css({'position': 'initial'});
          animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
      });
    });

    $(".submit").click(function(){
      return false;
    })

  });
})(this, this.document, this.jQuery);