(function ($, window, document, undefined) {
  $(function(){

    $('#nav-payments').click(function(){
      if(!$('#payments').is(':visible')){
        $('#nav-dashboard').parent().removeClass('active');
        $('#nav-payments').parent().addClass('active');
        $('#dashboard').hide();
        $('#payments').show();
        if(history.pushState)
          history.pushState(null, null, '#payments');
        else
          window.location.hash = '#payments';
      }
    });

    $('#nav-dashboard').click(function(){
      if(!$('#dashboard').is(':visible')){
        $('#nav-payments').parent().removeClass('active');
        $('#nav-dashboard').parent().addClass('active');
        $('#payments').hide();
        $('#dashboard').show();
        if(history.pushState)
          history.pushState(null, null, '#dashboard');
        else
          window.location.hash = '#dashboard';
      }
    });

    /* Dash */

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

    /* Payments section */

    $("#type-btn-group").on('click','label',function(){
      if(!$(this).hasClass('btn-success')){
        $(this).parent().find('label').toggleClass('btn-success btn-default');
        $('#managed-account-form').toggleClass('company');
        if($(this).children('input').val() === 'company'){
          $('#managed-account-form').find('.company input, input.company').attr('required',1);
          $('#managed-account-form').find('[name="legal_entity[personal_address][line2]"]').removeAttr('required');
        } else
          $('#managed-account-form').find('.company input, input.company').removeAttr('required');
      }
    });

    $("#legal-entity-add-owner").click(function(){
      var num = $(this).parent().prev().children().length;
      $(this).parent().prev().append('<div class="form-group row company">'+
                                       '<div class="col-xs-6"><input type="text" name="legal_entity[additional_owners]['+(num-1)+'][first_name]" class="form-control" placeholder="First name" required></div>'+
                                       '<div class="col-xs-6"><input type="text" name="legal_entity[additional_owners]['+(num-1)+'][last_name]" class="form-control" placeholder="Last name" required></div>'+
                                     '</div>');
      if(num < 3)
        $(this).find('span').html((num+1)+' owners').parent().next().show().next().show();
      else
        $(this).hide().next().hide().next().find('span').html('R');
    });

    $("#legal-entity-remove-owner").click(function(){
      $(this).find('span').html('r').parent().prev().show().prev().show().parent().prev().children().last().remove();
      var num = $(this).parent().prev().children().length;
      $(this).prev().prev().find('span').html(num+' owner'+(num > 1 ? 's' : ''));
      if(num === 1) $(this).hide().prev().hide();
    });

    $('#account-swap-international').click(function () {
      var $this = $(this),
        $externalAccount = $this.next();
      if(!$externalAccount.find('#account-international').val() || $externalAccount.find('#account-international').val().toLowerCase().trim() === 'false') {
        $this.html('<small>Change to sort code and account number</small>');
        $externalAccount.find('#account-sc').attr('type', 'hidden');
        $externalAccount.find('#account-acc').attr('type', 'hidden');
        $externalAccount.find('#account-swift').attr('type', 'tel');
        $externalAccount.find('#account-iban').attr('type', 'tel');
        $externalAccount.find('#account-international').val('true');
      } else {
        $this.html('<small>Change to IBAN and SWIFT</small>');
        $externalAccount.find('#account-swift').attr('type', 'hidden');
        $externalAccount.find('#account-iban').attr('type', 'hidden');
        $externalAccount.find('#account-sc').attr('type', 'tel');
        $externalAccount.find('#account-acc').attr('type', 'tel');
        $externalAccount.find('#account-international').val('false');
      }
    });

    $('[name="external_account[routing_number]"]').one('focus',function () {
      $(this).val('');
    });
    $('[name="external_account[account_number]"]').one('focus',function () {
      $(this).val('');
    });
    $('[name="external_account[routing_number_swift]"]').one('focus',function () {
      $(this).val('');
    });
    $('[name="external_account[account_number_iban]"]').one('focus',function () {
      $(this).val('');
    });

    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            var depths = this.name.split('[');
            depths = $.map(depths, function(string,i){return string.replace(/\]\s*$/, "")});
            switch(depths.length){
              case 1: o[depths[0]] = this.value!==''?this.value:null;
                      break;
              case 2: if(!o[depths[0]] || o[depths[0]].push)
                        o[depths[0]] = {};
                      o[depths[0]][depths[1]] = this.value!==''?this.value:null;
                      break;
              case 3: if(!o[depths[0]] || typeof o[depths[0]] !== 'object')
                        o[depths[0]] = {};
                      if(!o[depths[0]][depths[1]] || typeof o[depths[0]][depths[1]] !== 'object')
                        o[depths[0]][depths[1]] = {};
                      o[depths[0]][depths[1]][depths[2]] = this.value!==''?this.value:null;
                      break;
              case 4: if(!o[depths[0]] || typeof o[depths[0]] !== 'object')
                        o[depths[0]] = {};
                      if(!o[depths[0]][depths[1]] || typeof o[depths[0]][depths[1]] !== 'object')
                        o[depths[0]][depths[1]] = {};
                      if(!o[depths[0]][depths[1]][depths[2]] || typeof o[depths[0]][depths[1]][depths[2]] !== 'object')
                        o[depths[0]][depths[1]][depths[2]] = {};
                      o[depths[0]][depths[1]][depths[2]][depths[3]] = this.value!==''?this.value:null;
                      break;
            }
        });
        return o;
    };

    $("#managed-account-form").on('submit',function(e){
      $('#updating-spinner').show();
      $form = $(this);
      var data = $(this).serializeObject();
      if($('#account-international').val()==="true"){
        data["external_account"]["account_number"] = data["external_account"]["account_number_iban"];
        data["external_account"]["routing_number"] = data["external_account"]["routing_number_swift"];
      }
      delete data["external_account"]["account_number_iban"];
      delete data["external_account"]["routing_number_swift"];
      if(data["external_account"]["account_number"].indexOf('•') > -1 && data["external_account"]["routing_number"].indexOf('*') > -1)
        delete data["external_account"];
      else {
        if(data["external_account"]["account_number"].indexOf('•') > -1)
          delete data["external_account"]["account_number"];
        if(data["external_account"]["routing_number"].indexOf('*') > -1)
          delete data["external_account"]["routing_number"];
        data["external_account"]["object"] = "bank_account";
        data["external_account"]["country"] = "GB";
        data["external_account"]["currency"] = "GBP";
      }
      if(!data["metadata"])
        data["metadata"] = {};
      data["metadata"]["account_number_length"] = $('#account-international').val()==="true" ? $('#account-iban').val().length : $('#account-acc').val().length;
      data["metadata"]["business_tax_id"] = data["legal_entity"]["business_tax_id"];
      data["metadata"]["business_vat_id"] = data["legal_entity"]["business_vat_id"];
      if($('#tos_acceptance').length)
        data["tos_acceptance"] = true;
      $("#managed-account-form").find('.alert-warning').hide();
      $.ajax({
        method: 'POST',
        url: 'https://api.tktpass.com/me/account',
        dataType: 'json',
        data: data,
        xhrFields: {
          withCredentials: true
        },
        success: function (data, textStatus, jqXHR) {
          $('#updating-spinner').hide();
          alert('Changes saved successfully');
        },
        error: function (jqXHR, textStatus, errorThrown) {
          $('#updating-spinner').hide();
          //alert('error');
          $("#managed-account-form").find('.alert-warning').html('<strong>'+errorThrown+'</strong>: '+$.parseJSON(jqXHR.responseText).err).slideDown();
        }
      });
      e.preventDefault();
      return false;
    });

  });
})(this.jQuery, this, this.document);