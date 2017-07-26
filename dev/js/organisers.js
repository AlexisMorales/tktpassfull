(function ($, window, document, undefined) {

  $(function(){

    /* ##########
       # Header #
       ######### */
    $('header a.btn-outline-white').eq(0).click(function (e) {
      $("html, body").animate({
        scrollTop: $("#tabs").offset().top - $('#mainNav .navbar-brand-black').height()
      }, 800);
    });
    $('header a.filled').click(function (e) {
      $("html, body").animate({
        scrollTop: $("#magic").offset().top - $('#mainNav .navbar-brand-black').height()
      }, 1800, "swing", function () {
        $('#magic .fb-input').focus();
      });
    });

    /* ########
       # Tabs #
       ######## */

    function getHrLeft(index) {
      var $li = $('#tabs .nav-tabs li').eq(index).children('a');
      return $li.offset().left + $li.width() / 2 - 73.33 - parseInt($('#tabs').css('marginLeft'));
    }
    setTimeout(function(){$('#tabs .nav-tabs hr').css('marginLeft', getHrLeft($('#tabs .nav-tabs li.active').index()) + 'px');},50);
    $('#tabs .nav-tabs li a').click(function (e) {
      var $li = $(this).parent(),
        $hr = $('#tabs .nav-tabs hr');
      $hr.css('marginLeft', getHrLeft($li.index()) + 'px');
    });
    $(window).on('resize', function (e) {
      $('#tabs .nav-tabs hr').css('marginLeft', getHrLeft($('#tabs .nav-tabs li.active').index()) + 'px');
    });

    /* ############
       # Features #
       ############ */

    function isScrolledIntoView($elem) {
      var docViewTop = $(window).scrollTop();
      var docViewBottom = docViewTop + $(window).height();
      var elemTop = $elem.offset().top;
      var elemBottom = elemTop + $elem.height();
      return((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
    }
    var animateFeatures = function (e) {
        var delay = 0,
          animTime = 800;
        $('#features .feature svg').css({
          'opacity': 0
        });
        $('#features .fa-check').css({
          'opacity': 0,
          'visibility': 'initial'
        }).each(function () {
          var $that = $(this);
          setTimeout(function () {
            $that.parent().parent().find('svg').removeAttr('style').attr('class', 'animated bounceIn');
          }, delay);
          $that.delay(delay).animate({
            'opacity': 1
          }, {
            'duration': animTime
          });
          delay += animTime;
        });
    }
    $('#tabs .nav-tabs .item-2 a').one('click',animateFeatures);

    /* ###########
       # Reviews #
       ###########*/
    $('#testimonial-carousel').slick({autoplay:true});

    /* #########
       # Magic #
       ######### */

    $('#create-link').click(function(e){
      if($(this).attr('data-target')!=='#login-modal') return;
      document.cookie = "prefill=0;path=/";
    });

    /* UI Progress Tick */

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

    var $fb_inputs = $('#publish-steps-modal .step').first().find('input');

    function checkVal($input) {
      if(!$input.class) $input = $($input);
      if($input[0].tagName === 'DIV') $input.val = $input.text;
      if($input.val().trim() !== "")
        $input.addClass('has-value');
      else {
        $input.removeClass('has-value valid error');
        return;
      }
      var reg = new RegExp($input.attr('pattern'));
      if(!reg || reg.test($input.val().trim()))
        $input.removeClass('error').addClass('valid');
      else
        $input.removeClass('valid').addClass('error');
    }

    function fillForm(data) {
      $('#event-name').val(data.name);
      $('#event-host').val(data.admin.name);
      $('#event-predict').hide().siblings('.gm-logo').hide();
      if(data.place.name.indexOf(',') !== -1) {
        $('#event-venue').val(data.place.name.substring(0, data.place.name.indexOf(',')));
        $('#event-address-1').val(data.place.name.substring(data.place.name.indexOf(',') + 1).trim());
      } else
        $('#event-venue').val(data.place.name);
      if(data.place.location) {
        $('#event-city').val(data.place.location.city);
        $('#event-postcode').val(data.place.location.zip ? data.place.location.zip : '');
      }
      $('#event-venue').show();
      $('#event-address-1').show();
      $('#event-city').show();
      $('#event-postcode').show().siblings('a').show();

      function addLeadingZero(num) {
        num = parseInt(num);
        return num > -1 && num < 10 ? '0' + num : num;
      }
      var dateTime = new Date(data.start_time);
      $('#event-start-date').val(addLeadingZero(dateTime.getDate()) + '/' + addLeadingZero(dateTime.getMonth() + 1) + '/' + dateTime.getFullYear().toString().substr(2, 2));
      $('#event-start-time').val(addLeadingZero(dateTime.getHours()) + ':' + addLeadingZero(dateTime.getMinutes()));
      if(data.end_time) {
        dateTime = new Date(data.end_time);
        $('#event-end-date').val(addLeadingZero(dateTime.getDate()) + '/' + addLeadingZero(dateTime.getMonth() + 1) + '/' + dateTime.getFullYear().toString().substr(2, 2));
        $('#event-end-time').val(addLeadingZero(dateTime.getHours()) + ':' + addLeadingZero(dateTime.getMinutes()));
      }
      if(data.description)
        $('#event-desc').html(data.description.replace(/\n/g, "<br/>"));
      $('#event-pic-url').val(data.cover.source);
      $('.sexy-picture').addClass('valid').find('.uploaded').attr('src', data.cover.source);
      if(data.type == 'private') {
        var $group = $('.sexy-picture').next().find('.btn-group');
        $group.html('<label class="btn btn-default" onclick="$(this).toggleClass(\'btn-success btn-default\').next().toggleClass(\'btn-success btn-default\')"> <input type="radio" name="private" value="false" id="privacy-button-public">Public</label><label class="btn btn-success active" onclick="$(this).toggleClass(\'btn-success btn-default\').prev().toggleClass(\'btn-success btn-default\')"> <input type="radio" name="private" value="true" class="has-value valid" checked>Private</label>');
      }

      var loggedIn = !($("#mainNav").find("#account-btn").length === 0);
      if(!loggedIn){
        $('#event-name, #event-host, #event-venue, #event-address-1, #event-address-2, #event-city, #event-postcode, #event-start-date, #event-start-time, #event-end-date, #event-end-time, #event-pic, #privacy-button-public, #privacy-button-private').attr('readonly',1);
        $('#event-desc').removeAttr('contenteditable');
        document.cookie = "prefill="+$('#event-fb-id').val()+';path=/';
        $('#publish-steps-modal').on('click','input, div.textarea',function(e){
          $('#login-modal').modal('show');
          return false;
        });
      }
    }

    var showTick;

    function showSpinner(id) {
      var progressIcon = document.querySelector('.progress-icon');

      var progressOutline = new ui.Actor({
        element: progressIcon.getElementById('tick-outline-path')
      });
      var progressTick = new ui.Actor({
        element: progressIcon.getElementById('tick-path')
      });

      progressOutline.start(showIcon).then(spinIcon);

      $("#event-fb-id").val(id);

      showTick = function () {
        $('#magic .progress-icon .tick-icon').eq(1).attr('stroke', '#6CC84E')
        progressOutline.start(progressCompleteOutline);
        progressTick.start(progressCompleteTick);
        $fb_inputs.each(function (i, e) {
          checkVal(e);
        });
        $('#event-desc').blur();
        setTimeout(function () {
          $('#publish-steps-modal').modal();
          $('#magic .fb-input').removeAttr('disabled');
        }, 1000);
      }
      $.ajax({
        method: 'GET',
        url: 'https://api.tktpass.com/fb-events/' + id,
        dataType: 'json',
        xhrFields: {
          withCredentials: true
        },
        success: function (data, textStatus, jqXHR) {
          fillForm(data);
          showTick();
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $("#event-fb-id").val('');
          $('#magic .alert-danger').html('<strong>' + (textStatus ? textStatus : '') + ':</strong> ' + errorThrown).slideDown();
          resetSpinner();
          $("#magic .fb-input").removeAttr('disabled');
        }
      });
    }
    window.showSpinner = showSpinner;

    function resetSpinner() {
      var contents = '\
            <defs>\
                <path id="tick-outline-path" d="M14 28c7.732 0 14-6.268 14-14S21.732 0 14 0 0 6.268 0 14s6.268 14 14 14z" opacity="0" />\
                <path id="tick-path" d="M6.173 16.252l5.722 4.228 9.22-12.69" opacity="0"/>\
            </defs>\
            <g class="tick-icon" stroke-width="2" stroke="none" fill="none" transform="translate(1, 1)">\
                <use class="tick-outline" xlink:href="#tick-outline-path" />\
                <use class="tick" xlink:href="#tick-path" />\
            </g>\
            <g class="tick-icon" stroke-width="2" stroke="#5ac336" fill="none" transform="translate(1, 1.2)">\
                <use class="tick-outline" xlink:href="#tick-outline-path" />\
                <use class="tick" xlink:href="#tick-path" />\
            </g>';
      $('#magic .progress-icon').empty().html(contents);
    }

    $('#magic .fb-input').focus(function () {
      $(this).parent().addClass('active');
    }).blur(function () {
      if($(this).val() == '')
        $(this).parent().removeClass('active');
    }).on('input', function () {
      var $alert = $(this).parent().parent().find('.alert');
      if($(this).val() === '') {
        $alert.slideUp();
        $(this).parent().next().slideDown();
        return;
      }
      var reg = /.*?\/events\/(\d{12,})(?:\/|$|\?).*/;
      var match = reg.exec($(this).val());
      if(match && match.length) {
        $alert.slideUp();
        $(this).parent().next().hide();
        resetSpinner();
        $(this).attr('disabled', 1);
        $('#magic .alert').slideUp();
        showSpinner(match[1]);
      } else if($(this).val().length > 12) {
        $(this).parent().next().show();
        $alert.filter('.alert-warning').slideDown();
        resetSpinner();
      }
    });

    /* #######################
       # Publish Steps Modal #
       ####################### */

    $fb_inputs.on('change', function () {
      checkVal(this);
    });
    $('#event-desc').on('blur', function (e) {
      checkVal(this);
    });
    $fb_inputs.on('focus', function () {
      $(this).addClass('focused');
    });
    $fb_inputs.on('blur', function () {
      $(this).removeClass('focused');
    });

    $('#event-desc').on('focus', function () {
      $(this).removeClass('error').addClass('focused');
    });
    $('#event-desc').on('blur', function () {
      $(this).removeClass('focused');
    });
    $('#event-desc').on('paste', function (e) {
      e.preventDefault();
      var text = (e.originalEvent || e).clipboardData.getData('text/html') || (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');

      var ALLOWED_TAGS = ["STRONG", "EM", "BLOCKQUOTE", "Q", "DEL", "INS", "A", "B", "U", "I", "SPAN", "P", "BR", "UL", "OL", "LI", "TABLE", "TBODY", "TR", "TH", "TD", "TFOOT"];

      function usurp(p) {
        //Replace parent `p' with its children
        var last = p;
        for(var i = p.childNodes.length - 1; i >= 0; i--) {
          var e = p.removeChild(p.childNodes[i]);
          p.parentNode.insertBefore(e, last);
          last = e;
        }
        p.parentNode.removeChild(p);
      }

      function sanitize(el) {
        //Remove all tags from element `el' that aren't in the ALLOWED_TAGS list
        var tags = Array.prototype.slice.apply(el.getElementsByTagName("*"), [0]);
        for(var i = 0; i < tags.length; i++) {
          if(ALLOWED_TAGS.indexOf(tags[i].nodeName) == -1) {
            usurp(tags[i]);
          }
        }
      }

      function sanitizeString(string) {
        var div = document.createElement("div");
        div.innerHTML = string;
        sanitize(div);
        return div.innerHTML;
      }

      var $data = $('<div></div>').append(sanitizeString(text));

      // replace all styles except bold and italic
      $.each($data.find("*"), function (idx, el) {
        var $item = $(el);
        if($item.length > 0) {
          var style = {
            'fontWeight': $item.css('font-weight'),
            'fontStyle': $item.css('font-style'),
            'textDecoration': $item.css('text-decoration')
          };
          var attributes = $.map($item[0].attributes, function (attr) {
            return attr.name;
          });
          $.each(attributes, function (i, attr) {
            if(attr !== 'href' ||
              (attr.substring(0, 2) !== 'ht' &&
                attr.substring(0, 2) !== '//')
            )
              $item.removeAttr(attr);
          });
          if(style.fontWeight > 400)
            $item = $('<b></b>').append($item);
          if(style.fontStyle === 'italic')
            $item = $('<i></i>').append($item);
          if(style.textDecoration === 'underline')
            $item = $('<u></u>').append($item);
          if($item[0].tagName.toLowerCase() === 'a')
            $item.attr({
              'target': '_blank',
              'rel': 'nofollow'
            });
        }
      });

      // remove unnecesary tags (if paste from word)
      $data.children('style').remove();
      $data.children('meta').remove()
      $data.children('link').remove();

      function cursorPosition(toEnd) {
        var element = document.getElementById("event-desc");
        var caretOffset = 0;
        if(typeof window.getSelection != "undefined") {
          var range = window.getSelection().getRangeAt(0);
          var preCaretRange = range.cloneRange();
          preCaretRange.selectNodeContents(element);
          preCaretRange.setEnd(range.endContainer, range.endOffset);
          caretOffset = preCaretRange.toString().length;
        } else if(typeof document.selection != "undefined" && document.selection.type != "Control") {
          var textRange = document.selection.createRange();
          var preCaretTextRange = document.body.createTextRange();
          preCaretTextRange.moveToElementText(element);
          preCaretTextRange.setEndPoint("EndToEnd", textRange);
          caretOffset = preCaretTextRange.text.length;
        }
        var divStr = $('#event-desc').html();
        return toEnd ? divStr.substring(caretOffset) : divStr.substring(0, caretOffset);
      }

      //$(this).html(cursorPosition(false)+$data.html()+cursorPosition(true));
      $(this).html($data.html());
    });

    function readURL(input) {
      if(input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#event-pic-url').val('');
          $('.sexy-picture').addClass('valid').find('.uploaded').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
      }
    }
    $("#event-pic").focusin(function () {
      $(this).parent().addClass('focused');
    }).focusout(function () {
      $(this).parent().removeClass('focused');
    });
    $("#event-pic").change(function () {
      readURL(this);
    });
    $("#event-pic").on('change blur', function () {
      if(!$("#event-pic")[0].files.length && !$('#event-pic-url').val())
        $('.sexy-picture').removeClass('valid');
    });

    $('#ticket-table').on('change', 'input', function () {
      checkVal(this);
    });
    $('#ticket-table').on('focus', 'input, select', function () {
      $(this).addClass('focused');
    });
    $('#ticket-table').on('blur', 'input, select', function () {
      $(this).removeClass('focused');
    });

    function animateNextStep() {
      var $active = $('#publish-steps-modal').scrollTop(0).find('.step.active');
      if($active.is(':last-child')) return;
      $active.find('.animated.tada').removeClass('animated tada');
      setTimeout(function () {
        $active.removeClass('active fadeInRight').next().removeClass('fadeOutRight').addClass('fadeInRight active');
      }, 300);
      $active.addClass('fadeOutLeft');
      $('#publish-steps-modal .progressbar li.active').last().next().addClass('active');
      if($active.next().is(':last-child'))
        $('#publish-steps-modal .modal-footer .btn').hide();
      else $('#publish-steps-modal .modal-footer .btn-default').show();
    }

    function animatePrevStep() {
      var $active = $('#publish-steps-modal .step.active');
      if($active.is(':first-child')) return;
      $active.find('.animated.tada').removeClass('animated tada');
      setTimeout(function () {
        $active.removeClass('active fadeInRight').prev().removeClass('fadeOutLeft').addClass('fadeInLeft active');
      }, 300);
      $active.addClass('fadeOutRight');
      var $fromLi = $('#publish-steps-modal .progressbar li.active').last().removeClass('active');
      if($fromLi.index() < 2) $('#publish-steps-modal .modal-footer .btn-default').hide();
    }

    function submitForm() {
      $("#upload-spinner").show();
      var data = {
        'name': $('#event-name').val(),
        'host': $('#event-host').val(),
        'venue': $('#event-venue').val(),
        'address_1': $('#event-address-1').val(),
        'city': $('#event-city').val(),
        'postcode': $('#event-postcode').val(),
        'start': $('#event-start-date').val() + ' ' + $('#event-start-time').val(),
        'end': $('#event-end-date').val() + ' ' + $('#event-end-time').val(),
        'description': $('#event-desc').html(),
        'image': $('#event-pic-url').val(),
        'private': ($('#privacy-button-private').is(':checked').length > 0 ? 1 : 0)
      };
      if($('#event-address-2').val())
        data["address_2"] = $('#event-address-2').val();
      if($('#event-fb-id').val())
        data["fb_id"] = $('#event-fb-id').val();
      $.ajax({
        method: 'POST',
        url: '//api.tktpass.com/events/',
        dataType: 'json',
        data: data,
        xhrFields: {
          withCredentials: true
        },
        success: function (data, textStatus, jqXHR) {
          $("#upload-spinner").addClass('adding');
          var tickets = [];
          $('#ticket-table tbody tr').slice(1).each(function(i,row){
            var $row = $(row), ticket = {};
            ticket.type = $row.find('.custom-select').val();
            ticket.name = $row.find('.ticket-name').val();
            ticket.price = parseInt(parseFloat($row.find('.ticket-price').val())*100);
            ticket.quantity = $row.find('.ticket-quantity').val();
            tickets.push(ticket);
          });
          $.ajax({
            method: 'PUT',
            url: '//api.tktpass.com/events/'+data.id+'/tickets',
            dataType: 'json',
            data: {"tickets":tickets},
            xhrFields: {
              withCredentials: true
            },
            success: function(data, textStatus, jqXHR) {
              if($("#event-pic").val() !== '') {
                $("#upload-spinner").addClass('uploading');
                var tooLongHandle = setTimeout(function(){
                  $("#upload-spinner").hide();
                  alert('Your event is published but your image upload is taking an unusually long time, an error may have occured. Please try uploading the event image again later.');
                },55000);
                $('#upload-form').attr('action','https://api.tktpass.com/events/'+data[0]["event_id"]+'/image');
                function iframeUpload($form,onUploadDone) {
                  var id, cb, $iframe, url;
                  // Generating a random id to identify
                  // both the iframe and the callback function
                  var num = Math.floor(Math.random() * 1000);
                  id = "uploader-frame-" + num;
                  cb = "uploader-cb-" + num;
                  // creating iframe and callback
                  $iframe = $('<iframe id="' + id + '" name="' + id + '" style="display:none;">');
                  url = $form.attr('action');
                  $form
                    .attr('action', url + '?iframe=' + cb)
                    .append($iframe)
                    .attr('target', id);
                  // defining callback
                  window[cb] = $.proxy(function (data) {
                    // removing iframe
                    $iframe.remove();
                    $form.removeAttr('target');
                    // removing callback
                    $form.attr('action', url);
                    window[cb] = undefined;
                    onUploadDone(data);
                  }, $form);
                  document.domain = "tktpass.com";
                  $form.submit();
                }
                iframeUpload($('#upload-form'),function(data){
                  clearTimeout(tooLongHandle);
                  $('#upload-spinner').removeClass('uploading adding').hide();
                  $("#event-link").val('https://tktpass.com/events/'+data.id+'/');
                  $("#whats-next-share .btn-facebook").attr('href','https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/events/'+data.id+'/');
                  $("#whats-next-share .btn-twitter").attr('href','https://twitter.com/home?status='+encodeURIComponent(data.name)+'%20https%3A//tktpass.com/events/'+data.id+'/');
                  $("#whats-next-share .btn-default").attr('href','mailto:?&subject='+encodeURIComponent(data.name)+'&body='+encodeURIComponent(data.name)+'%0D%0A%0D%0Ahttps%3A//tktpass.com/events/'+data.id+'/');
                  animateNextStep();
                });
                /*alert('taking to upload');
                $('#upload-form').submit();*/
              } else{
                $("#event-link").val('https://tktpass.com/events/'+data[0]["event_id"]+'/');
                $("#whats-next-share .btn-facebook").attr('href','https://www.facebook.com/sharer/sharer.php?u=https%3A//tktpass.com/events/'+data[0]["event_id"]+'/');
                $("#whats-next-share .btn-twitter").attr('href','https://twitter.com/home?status='+encodeURIComponent($('#event-name').val())+'%20https%3A//tktpass.com/events/'+data[0]["event_id"]+'/');
                $("#whats-next-share .btn-default").attr('href','mailto:?&subject='+encodeURIComponent($('#event-name').val())+'&body='+encodeURIComponent($('#event-name').val())+'%0A%0Ahttps%3A//tktpass.com/events/'+data[0]["event_id"]+'/');
                $('#upload-spinner').removeClass('uploading adding').hide();
                animateNextStep();
              }
            },
            error: function(jqXHR, textStatus, errorThrown){
              alert('Error ' + textStatus + ', event details added successfully but ticket details failed:' + jQuery.parseJSON(jqXHR.responseText).err);
              $('#upload-spinner').removeClass('adding').hide();
            }
          });
        },
        error: function (jqXHR, textStatus, errorThrown) {
          alert('Error ' + textStatus + ': ' + jQuery.parseJSON(jqXHR.responseText).err);
          $('#upload-spinner').hide();
        }
      });
    }

    function resetForm() {
      $('#event-name, #event-host, #event-predict, #event-venue, #event-address-1, #event-address-2, #event-city, #event-postcode, #event-start-date, #event-start-time, #event-end-date, #event-end-time, #event-pic').val('').removeClass('has-value valid');
      $('#event-predict').show().siblings('.gm-logo').show();
      $('#event-venue, #event-address-1, #event-address-2, #event-city, #event-postcode, #event-add-address-line').hide();
      $('#publish-steps-modal .sexy-picture').removeClass('has-value valid').find('img.uploaded').attr('src', '');
      $('#event-desc').empty().removeClass('has-value valid');
      $('#privacy-button-public').attr('checked', 1).parent().removeClass('btn-default').addClass('btn-success active')
        .next().removeClass('btn-success active').addClass('btn-default').children('input').removeAttr('checked');
      var template = _.template($('#ticket-table-row-template').html());
      var $tbody = $('#ticket-table tbody');
      $tbody.find('tr').slice(1).remove();
      $tbody.append(template({
        "index": 1
      })).children().eq(0).find('input').eq(0).attr('placeholder', 'eg. General');
    }

    function resetPublishStepsModal() {
      var $body = $('#publish-steps-modal .modal-body');
      $body.find('.progressbar li').removeClass('active').first().addClass('active');
      $('#publish-steps-modal .modal-footer .btn-default').hide();
      resetForm();
      $body.find('.step').removeClass('fadeInLeft fadeOutLeft active').first().addClass('active');
      var $footer = $('#publish-steps-modal .modal-footer');
      $footer.find('.btn-success').show();
      $footer.find('.btn-default').hide();
    }

    $("#event-add-address-line").click(function(){
      $('#event-address-2').css({'display':'none'}).removeAttr('class').slideDown();
      $(this).hide();
    });
    $('#publish-steps-modal [data-animate="next"]').on('click', function () {
      var loggedIn = !($("#mainNav").find("#account-btn").length === 0);
      if(!loggedIn){
        $("#login-modal").modal('show');
        return false;
      }
      $('#publish-steps-modal').scrollTop(0);
      var $active = $('#publish-steps-modal .step.active');
      var $errors = $active.find('input.error, .textarea.error, input[required]:not(.has-value), .textarea:not(.has-value)');
      if($errors.length) {
        $errors.removeClass('animated tada').addClass('error');
        setTimeout(function () {
          $errors.addClass('animated tada');
        }, 10);
      } else { 
        if($active.next().is(':last-child'))
          submitForm();
        else 
          animateNextStep();
      }
    });
    $('#publish-steps-modal [data-animate="prev"]').on('click', animatePrevStep);
    $('#publish-steps-modal').on('hidden.bs.modal', function () {
      resetPublishStepsModal();
      resetSpinner();
      $('#event-fb-id').val();
      document.cookie = "prefill=;path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC";
      $('#magic .fb-input').val('').parent().next().show();
    });
    $('#ticket-table-add-row').click(function () {
      var $tbody = $('#ticket-table tbody');
      var template = _.template($('#ticket-table-row-template').html());
      $tbody.append(template({
        "index": $tbody.children().length + 1
      }));
    });
    $('#ticket-table').on('keypress', function (e) {
      if(!$(e.target).is('input')) return;
      if($(e.target).closest('td').hasClass('price')) {
        if(e.keyCode != 46 && (e.keyCode < 48 || e.keyCode > 57)) return false;
        var value = $(e.target).val()+String.fromCharCode(e.keyCode);
        return value.match(/^\d*(\.(\d\d?)?)?$/i) !== null;
      } else if($(e.target).closest('td').hasClass('quantity'))
        if(e.keyCode < 48 || e.keyCode > 57) return false;
    });
    $('#ticket-table').on('click', '.fa-trash-o', function (e) {
      if($('#ticket-table tbody').children().length>2)
        $(this).closest('tr').remove();
    });

    $('#payment-swap-international').click(function () {
      var $this = $(this),
        $form = $this.prev();
      if(!$form.find('#payment-international').val() || $form.find('#payment-international').val().toLowerCase().trim() === 'false') {
        $this.html('<small>Change to sort code and account number</small>');
        $form.find('#payment-sc').attr('type', 'hidden');
        $form.find('#payment-acc').attr('type', 'hidden');
        $form.find('#payment-swift').attr('type', 'tel');
        $form.find('#payment-iban').attr('type', 'tel');
        $form.find('#payment-international').val('true');
      } else {
        $this.html('<small>Change to IBAN and SWIFT</small>');
        $form.find('#payment-swift').attr('type', 'hidden');
        $form.find('#payment-iban').attr('type', 'hidden');
        $form.find('#payment-sc').attr('type', 'tel');
        $form.find('#payment-acc').attr('type', 'tel');
        $form.find('#payment-international').val('false');
      }
    });
    $('form.form-update-payment').on('submit',function (e) {
      e.preventDefault();
      var $form = $(this),
          sc = $form.find('#payment-sc').val().replace(/\D/g,''),
          acc = $form.find('#payment-acc').val().replace(/\D/g,''),
          swift = $form.find('#payment-swift').val().replace(/\D/g,''),
          iban = $form.find('#payment-iban').val().replace(/\D/g,''),
          international = $form.find('#payment-international').val()==="true";
      $form.find('.has-error, .tada').removeClass('has-error tada animated');
      var error = false,
          data = {
            external_account: {
              object:'bank_account',
              country:'GB',currency:'GBP',
              routing_number:'',
              account_number:''
            }
          };
      if(international){
        if(!swift || swift.length < 8){
          setTimeout(function(){$form.find('#payment-swift').addClass('has-error animated tada');},5);
          error = true;
        }
        if(!iban || iban.length < 16){
          setTimeout(function(){$form.find('#payment-iban').addClass('has-error animated tada');},5);
          error = true;
        }
        if(error)
          return false;
        data["external_account"]["routing_number"] = swift;
        data["external_account"]["account_number"] = iban;
      } else {
        if(!sc || sc.length < 6){
          setTimeout(function(){$form.find('#payment-sc').addClass('has-error animated tada');},5);
          error = true;
        }
        if(!acc || acc.length < 7){
          setTimeout(function(){$form.find('#payment-acc').addClass('has-error animated tada');},5);
          error = true;
        }
        if(error)
          return false;
        data["external_account"]["routing_number"] = sc;
        data["external_account"]["account_number"] = acc;
      }
      $('#upload-spinner').addClass('updating').show();
      $.ajax({
        method: 'POST',
        url: '//api.tktpass.com/account',
        dataType: 'json',
        "data": data,
        xhrFields: {
          withCredentials: true
        },
        success: function(data, textStatus, jqXHR) {
          $('#upload-spinner').removeClass('updating').hide();
          setTimeout(function(){window.location.href = 'https://dev.tktpass.com/myevents.php#payments';},50);
        },
        error: function(jqXHR, textStatus, errorThrown){
          $('#upload-spinner').removeClass('updating').hide();
          alert('Error adding bank account details: ' + jQuery.parseJSON(jqXHR.responseText).err);
        }
      });
      return false;
    });

    $('#event-link').click(function () {
      this.select();
      var succeed = void 0;
      try {
        succeed = document.execCommand("copy");
      } catch(e) {
        succeed = false;
      }
      if(succeed) {
        var $p = $(this).prev();
        $p.addClass('copied');
        setTimeout(function () {
          $p.removeClass('copied')
        }, 1200);
      }
    });

    $('#login-modal').on('hide.bs.modal', function () {
      $('#publish-steps-modal').modal('hide');
    });

    /* ##############
       # Calculator #
       ############## */
    var getFee = {
      'eventbrite': function(num, price){
        var fee = Math.min(19.95,0.035*price+0.49)+0.02*price;
        return fee*num;
      },
      'billetto': function(num, price){
        var fee = Math.min(11.99,price-(price-0.4)/1.035);
        return fee*num;
      },
      'ticketleap': function(num, price){
        var fee = Math.min(8,0.02*price+0.8)+0.03*price;
        return fee*num;
      }
    };
    function calcRevenue(){
      var num = parseInt($('#num-tickets').val());
      var price = parseFloat($('#ticket-price').val());
      var tRev = num*price;
      var tRevPence = Math.round((tRev-Math.floor(tRev))*100);
      if(tRevPence < 10) tRevPence = '0'+tRevPence;
      $('#calculator .tktpass .rev-col').html('£ <span style="font-size:2em">'+Math.floor(tRev)+'</span>.'+tRevPence);
      var oFee = getFee[$('#other').val()](num,price);
      var oFeePence = Math.round((oFee-Math.floor(oFee))*100);
      if(oFeePence < 10) oFeePence = '0'+oFeePence;
      var oRevPence = Math.round((tRev-oFee-Math.floor(tRev-oFee))*100);
      if(oRevPence < 10) oRevPence = '0'+oRevPence;
      $('#calculator .other .fee-col').html('£ <span style="font-size:2em">'+Math.floor(oFee)+'</span>.'+oFeePence);
      $('#calculator .other .rev-col').html('£ <span style="font-size:2em">'+Math.floor(tRev-oFee)+'</span>.'+oRevPence);
      $('#diff').html((100*oFee/tRev).toFixed(1));
    }
    $('#num-tickets').on('input',calcRevenue);
    $('#num-tickets').on('change',function(){
      var num = Math.max(1,Math.min(1000000,parseInt($(this).val())));
      if(!num) num = 1;
      $(this).val(num);
      calcRevenue();
    });
    $('#ticket-price').on('input',function(){
      var p = parseFloat($(this).val());
      var min = parseFloat($(this).attr('min'));
      var max = parseFloat($(this).attr('max'));
      if(p<min || p>max){
        $(this).val(Math.max(min,Math.min(max,p)).toFixed(2));
      }
      calcRevenue();
    });
    $('#ticket-price').on('change',function(){
      var min = parseFloat($(this).attr('min'));
      var max = parseFloat($(this).attr('max'));
      var p = Math.max(min,Math.min(max,parseFloat($(this).val())));
      if(!p) p = 12;
      $(this).val(p.toFixed(2));
      calcRevenue();
    });
    $('#other').on('input',calcRevenue);
  });

})(this.jQuery, this, this.document);