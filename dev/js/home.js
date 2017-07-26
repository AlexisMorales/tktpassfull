(function($, window, document, undefined) {

  $(function(){

    Stripe.setPublishableKey('pk_test_6e2JAHrqsSfADzDHDMeAssBG');

    /* ###############
       # Event Cards #
       ############### */
    /*$('.toggle-tickets').click(function() {
      $tickets = $(this).parent().siblings('.collapse');
     
      if ($tickets.hasClass('in')) {
        $tickets.collapse('hide');
        $(this).html('Show Tickets');
        $(this).closest('.ticket-card').removeClass('active');
      } else {
        $tickets.collapse('show');
        $(this).html('Hide Tickets');
        $(this).closest('.ticket-card').addClass('active');
      }
    });*/
    $('#event-slider').slick({infinite:false});
    /*$('#hot-slider').slick({
      centerMode: true,
      slidesToShow: 3,
      centerPadding: '0px',
      focusOnSelect:true,
      responsive: [
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 1
          }
        }
      ]
    });*/
    $('.event-card .share .btn-tell-a-friend').on('click',function(e){
      $(this).parent().addClass('expanded');
      e.stopPropagation();
    });
    $('#events, #hot').parent().on('click','#events, #hot, #events *, #hot *',function(e){
      $('#events, #hot').find('.event-card .expanded').removeClass('expanded');
      e.stopPropagation();
    });
    
    /* #############
       # Buy Modal #
       ############# */
    function addLeadingZero(num) {
      if (num < 10) {
        return "0" + num;
      } else {
        return "" + num;
      }
    }
    
    function openBuyModal($el){
      if(!$el.data('id'))
        $el.data($.parseJSON(atob($el.data('obj')))).removeAttr('data-obj');
      var $buyModal = $('#buyModal');
      var order = $.merge([],$el.data('tickets'));
      order.push({name:"tktpass Freedom",price:0,id:"tktpass_freedom_promo"});
      $.each(order,function(i,ticket){
        ticket.available = ticket.quantity;
        ticket.quantity = 0;
      });
      $buyModal.data($.extend($el.data(),{"order":order}));
      $buyModal.find('.modal-header').css({"background-image":"url("+$buyModal.data('image')+")"});
      $buyModal.find('.modal-title').html($buyModal.data('name'));
      $buyModal.find('.location span').html($buyModal.data('venue')+', '+$buyModal.data('city'));
      var d = new Date($buyModal.data('start'));
      var dayNames = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
      var monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
      $buyModal.find('.date span').html(d.getHours()+':'+addLeadingZero(d.getMinutes())+' '+dayNames[d.getDay()].substring(0,3)+' '+d.getDate()+' '+monthNames[d.getMonth()].substring(0,3));//+', '+(d.getYear()+1900)
      buildBuyStep1();
      $buyModal.modal('show');
    }
    
    function buildBuyStep1(){
      var $buyModal = $('#buyModal');
      var $tbody = $buyModal.find('.modal-body .step-1 table tbody').empty();
      var template = _.template($('#ticket-row-template').html());
      $.each($buyModal.data('tickets'),function(i,ticket){
        $tbody.append(template(ticket));
      });
      $.each($tbody.find('tr:not(.spacer)'),function(i,row){
        $(row).data($.parseJSON(atob($(row).attr('data-obj')))).removeAttr('data-obj');
      });
    }
    
    function addBuyQuantity($row, add){
      var $buyModal = $('#buyModal');
      var order = $.merge([],$buyModal.data('order'));
      $.each(order,function(i,ticket){
        if($row.data('id') === ticket.id){
          var quantity = order[i].quantity;
          add = parseInt(add);
          var $s = $row.find('.quantity span');
          if(!isNaN(add) && add!==0){
            if((quantity+add)>-1 && (quantity+add)<100){
              order[i].quantity = quantity+add;
              $s.html(quantity+add);
            } else if((quantity+add)>99){
              order[i].quantity = 99;
              $s.html(99);
            } else{
              order[i].quantity = 0;
              $s.html(0);
            }
          }
        }
      });
      $buyModal.data('order',order);
      updateBuyStep1Total();
    }
    function updateBuyStep1Total(){
      var total = 0;
      var order = $('#buyModal').data('order');
      $.each(order,function(i,ticket){
        if(order[i].id !== "tktpass_freedom_promo")
          total += ticket.quantity*ticket.price;
      });
      $('#buyModal .step-1>p .total').html((total/100).toFixed(2));
    }
    $('#buyModal').on('click','.quantity a',function(e){
        e.preventDefault();
        e.stopPropagation();
        addBuyQuantity($(this).closest('tr'),($(this).attr('data-type')=="minus" ? -1 : 1));
    });
    
    function buyModalContinue(){
      var $buyModal = $('#buyModal');
      var order = $buyModal.data('order');
      var quantityGZ = false;
      for(var i=0,l=order.length;i<l;i++){
        if(order[i].id !== "tktpass_freedom_promo" && order[i].quantity > 0){
          quantityGZ = true;
          break;
        }
      }
      if(quantityGZ){
        buildBuyStep2();
        $buyModal.find('.step-1').hide();
        $buyModal.find('.step-2').show()
      }
    }
    
    function buyModalToggleFreedom(){
      var $buyModal = $('#buyModal');
      var order = $.merge([],$buyModal.data('order'));
      var $innerCircle = $('#add-freedom').find('.inner-circle');
      $innerCircle.toggleClass('checked');
      $.each(order,function(i,ticket){
        if(ticket.id === "tktpass_freedom_promo"){
          order[i].quantity = $innerCircle.hasClass('checked') ? 1 : 0;
        }
      });
      $buyModal.data('order', order);
      buildBuyStep2();
    }
    
    function buildBuyStep2(){
      $buyModal = $('#buyModal');
      var $tbody = $buyModal.find('.modal-body .step-2 table tbody').empty();
      $tbody.next().empty();
      var order = $.merge([],$buyModal.data('order'));
      var template = _.template($('#sub-row-template').html());
      var total = 0;
      var addFreedom = false;
      $.each(order,function(i,ticket){
        total += ticket.quantity*ticket.price;
        if(ticket.id !== "tktpass_freedom_promo" && ticket.quantity > 0)
          $tbody.append(template($.extend(ticket,{"saving":""})));
        else if(ticket.quantity > 0) addFreedom = true;
      });
      $tbody.append('<tr class="spacer"><td class="spacer"></td></tr>');
      if(addFreedom){
        $tbody.append('<tr> <td class="ticket">Booking Fee:</td> <td class="saving">Saving £<span>'+(total/1000).toFixed(2)+'</span></td> <td class="sub">£<span>0.00</span></td> </tr>');
        $tbody.append('<tr> <td class="ticket premium">Freedom:</td> <td class="saving"></td> <td class="sub">£0.00</td>');
      }
      else {
        $tbody.append('<tr> <td class="ticket">Booking Fee:</td> <td class="saving"></td> <td class="sub">£<span>'+(total/1000).toFixed(2)+'</span></td> </tr>');
        total = total*1.1;
      }
      $tbody.next().append('<tr> <td class="ticket">Total:</td> <td class="saving"></td> <td class="sub">£<span>'+(total/100).toFixed(2)+'</span></td> </tr>');
      if(!total){
        $buyModal.find('#payment-form .form-group, #payment-form .row').css('visibility','hidden');
        $buyModal.find('.cc-brand-wrap').css('visibility','hidden');
        $buyModal.find('#buy-modal-pay').html('Confirm');
      }
    }
    
    function buyModalBack(e){
      $('#buyModal').find('.step-2').hide();
      $('#buyModal').find('.step-1').show();
    }
    
    function buyModalPay(){
      var $buyModal = $('#buyModal'), $form = $('#payment-form');
      $buyModal.find('#buy-modal-pay').prop('disabled', true);//
      $form.find('.spinner').show();
      $form.find('.payment-errors').hide();
      var order = $buyModal.data('order');
      var total = 0;
      $.each(order,function(i,ticket){
        total += ticket.quantity*ticket.price;
      });
      if(total){
        var accepted = ["visa","mastercard","amex"];
        var cardNames = {
          "visa": "Visa",
          "mastercard": "Mastercard",
          "amex": "American Express",
          "dinersclub": "Diners Club",
          "discover": "Discover",
          "unionpay": "UnionPay",
          "jcb": "JCB",
          "maestro": "Maestro",
          "forbrugsforeningen": "Forbrugsforeningen",
          "dankort": "Dankort",
          "elo": "Elo"
        };
        var cardType = $.payment.cardType($form.find('.cc-number').val());
        if(cardType !== null){
          if(accepted.indexOf(cardType) < 0){
            $form.prev().find('.cc-brand').removeClass('visa mastercard amex');
            $form.find('.spinner').hide();
            $form.find('.payment-errors').html('Sorry we do not currently accept '+cardNames[cardType]+' cards.').show();
            $form.find('.cc-number').closest('.form-group').addClass('has-error');
            $buyModal.find('#buy-modal-pay').prop('disabled', false);
            return false;
          } else {
            $form.prev().find('.cc-brand').removeClass('visa mastercard amex').addClass(cardType);
          }
        }
        $form.find('.cc-number').toggleInputError(!$.payment.validateCardNumber($form.find('.cc-number').val()));
        $form.find('.cc-exp').toggleInputError(!$.payment.validateCardExpiry($form.find('.cc-exp').payment('cardExpiryVal')));
        $form.find('.cc-cvc').toggleInputError(!$.payment.validateCardCVC($form.find('.cc-cvc').val(), cardType));
        if($form.find('.has-error').length){
          $form.find('.spinner').hide();
          $form.find('.payment-errors').html('Error, please correct the highlighted fields').show();
          $buyModal.find('#buy-modal-pay').prop('disabled', false);
          return false;
        }
        function stripeResponseHandler(status, response) {
          // Grab the form:
          var $buyModal = $('#buyModal'), $form = $('#payment-form');
          //Hide spinner
          if (response.error) { // Problem!
            // Show the errors on the form
            $form.find('.spinner').hide();
            $form.find('.payment-errors').html(response.error.message).show();
            $buyModal.find('#buy-modal-pay').prop('disabled', false); // Re-enable submission
          } else { // Token was created!
            // Get the token ID:
            var token = response.id;
            // Insert the token into the form so it gets submitted to the server:
            $tokenInput = $form.find('[name="stripeToken"]');
            if($tokenInput.length)
              $tokenInput.val(token);
            else
              $form.append($('<input type="hidden" name="stripeToken" />').val(token));
            // Submit the form:
            $.ajax({
              method: 'POST',
              url: 'https://api.tktpass.com/order/',
              dataType: 'json',
              xhrFields: {
                withCredentials: true
              },
              data: {
                order: $buyModal.data('order'),
                source: token
              },
              success: function (data, textStatus, jqXHR) {
                $form.find('.spinner').hide();
                $buyModal.find('#buy-modal-pay').prop('disabled', false); // Re-enable submission
                //alert('Charge successful!');
                $('#buyModal').find('.step-2').hide();
                $('#buyModal').find('.step-3').show();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                $form.find('.spinner').hide();
                $buyModal.find('#buy-modal-pay').prop('disabled', false); // Re-enable submission
                var data = $.parseJSON(jqXHR.responseText);
                $form.find('.payment-errors').html('Payment failed: '+(data.err ? data.err : (data ? data : jqXHR.responseText))).show();
              }
            });
          }
        }
        Stripe.card.createToken({
          number: $form.find('.cc-number').val(),
          cvc: $form.find('.cc-cvc').val(),
          exp: $form.find('.cc-exp').val()
        }, stripeResponseHandler);
      } else {
        $.ajax({
          method: 'POST',
          url: 'https://api.tktpass.com/order/',
          dataType: 'json',
          xhrFields: {
            withCredentials: true
          },
          data: {
            order: $buyModal.data('order')
          },
          success: function (data, textStatus, jqXHR) {
            $form.find('.spinner').hide();
            $buyModal.find('#buy-modal-pay').prop('disabled', false); // Re-enable submission
            //alert('Charge successful!');
            $('#buyModal').find('.step-2').hide();
            $('#buyModal').find('.step-3').show();
            $buyModal.find('#payment-form .form-group, #payment-form .row').css('visibility','');
            $buyModal.find('.cc-brand-wrap').css('visibility','');
            $buyModal.find('#buy-modal-pay').html('Pay with card');
          },
          error: function (jqXHR, textStatus, errorThrown) {
            $form.find('.spinner').hide();
            $buyModal.find('#buy-modal-pay').prop('disabled', false); // Re-enable submission
            var data = $.parseJSON(jqXHR.responseText);
            $form.find('.payment-errors').html('Payment failed: '+(data.err ? data.err : (data ? data : jqXHR.responseText))).show();
          }
        });
      }
    }
    
    function closeBuyModal(){
      var $buyModal = $('#buyModal');
      $buyModal.removeData();
      $buyModal.find('.step-1 > p .total').html((0).toFixed(2));
      $('#add-freedom').find('.inner-circle').removeClass('checked');
      var $form = $('#payment-form');
      $form.find('.form-group').removeClass('animated tada has-success has-error');
      $form.find('.cc-cvc').val('');
      $form.find('.form-group, .row').css('visibility','');
      $form.find('.payment-errors').hide();
      $buyModal.find('.cc-brand-wrap').css('visibility','');
      $buyModal.find('#buy-modal-pay').html('Pay with card');
      $buyModal.find('.step-2').hide();
      $buyModal.find('.step-3').hide();
      $buyModal.find('.step-1').show();
    }
    
    $('#buyModal').modal({show:false});
    $('body').on('click','.event-card .btn-success',function(e){
      var $loginModal = $('#login-modal');
      if($loginModal.length)
        $loginModal.modal('show');
      else
        openBuyModal($(this).closest('.event-card'));
    });
    $('#buy-modal-continue').on('click',buyModalContinue);
    $('#buy-modal-pay').on('click',buyModalPay);
    $('#buy-modal-back').on('click',buyModalBack);
    $('#add-freedom').on('click',buyModalToggleFreedom);
    $('#buyModal').on('hide.bs.modal', closeBuyModal);
    
    var $form = $('#payment-form');
    $form.find('.cc-number').payment('formatCardNumber');
    $form.find('.cc-exp').payment('formatCardExpiry');
    $form.find('.cc-cvc').payment('formatCardCVC');
    $.fn.toggleInputError = function(erred) {
      if(erred || ($(this).prop('required') && $(this).val()==="")){
        this.closest('.form-group').removeClass('has-success tada').addClass('has-error animated');
        var that = this;
        setTimeout(function(){that.closest('.form-group').addClass('tada');},10);
      } else {
        this.closest('.form-group').removeClass('has-error animated tada').addClass('has-success');
      }
      return this;
    };
    $form.on('focus','input',function(){
      $(this).closest('.form-group').removeClass('has-success has-error');
    });
    
    /* ##############
       # Sell Modal #
       ############## */
    
    function openSellModal(el){
        
    }
    
    function closeSellModal(){
        
    }
  });

})(this.jQuery, this, this.document);