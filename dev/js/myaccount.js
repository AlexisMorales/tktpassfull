(function ($, window, document, undefined) {
  if($.fn.serializeObject === undefined){
    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        /*$.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });*/
        $.each(a,function(){
          if(/[^\[\]\s]+\[[^\[\]]*\]/.test(this.name)){
            var parts = this.name.split('[');
            $.each(parts,function(i,e){parts[i]=e.replace(/\]+$/, "");});
            if(!o[parts[0]]){
              o[parts[0]] = [];
            } else if(o[parts[0]] && !o[parts[0]].push){
              o[parts[0]] = [o[parts[0]]];
            }
            if(parts.length > 2 && parts[1]){
              if(!o[parts[0]][parts[1]])
                o[parts[0]][parts[1]] = [];
              else if(!$.isArray(o[parts[0]][parts[1]]))
                o[parts[0]][parts[1]] = [o[parts[0]][parts[1]]];
              if(parts[2]){
                o[parts[0]][parts[1]][parts[2]] = this.value;
              } else {
                o[parts[0]][parts[1]].push(this.value);
              }
            } else {
              if(parts[1])
                o[parts[0]][parts[1]] = this.value;
              else
                o[parts[0]].push(this.value);
            }
          } else {
            o[this.name] = this.value || '';
          }
        });
        return o;
    };
  }
  $(function(){
    $("#annual-switch").on('change',function(e){
      $('.membership-types .pay-monthly, .membership-types .pay-annually').toggleClass('hidden');
    });
    $('#profile-form').on('submit',function(e){
      e.preventDefault();
      var data = $(this).serializeObject();
      if(data['birthday'][2] && parseInt(data['birthday'][2])){
        data['birthday'][0] = parseInt(data['birthday'][0]);
        data['birthday'][1] = parseInt(data['birthday'][1]);
        data['birthday'][2] = parseInt(data['birthday'][2]);
        data['birthday'][0] = data['birthday'][0]>31 ? 31 : data['birthday'][0];
        data['birthday'][0] = data['birthday'][0]<1 ? 1 : data['birthday'][0];
        data['birthday'][1] = data['birthday'][1]>12 ? 12 : data['birthday'][1];
        data['birthday'][1] = data['birthday'][1]<1 ? 1 : data['birthday'][1];
        data['birthday'][2] = data['birthday'][2]<(new Date()).getYear()-100 ? data['birthday'][2]+2000 : data['birthday'][2]<100 ? data['birthday'][2]+1900 : data['birthday'][2];
        data['birthday'][0] = (data['birthday'][0] < 10 ? '0' : '')+data['birthday'][0];
        data['birthday'][1] = (data['birthday'][1] < 10 ? '0' : '')+data['birthday'][1];
        data['birthday'] = data['birthday'][2]+'-'+data['birthday'][1]+'-'+data['birthday'][0];
      } else {
        delete data['birthday'];
      }
      if(!data['newPassword']){
        delete data['newPassword'];
        delete data['newPassword2'];
      }
      $.ajax({
        url: this.action,
        method: this.method || 'GET',
        data: data,
        xhrFields: {
          withCredentials: true
        },
        success: function(data, textStatus, jqXHR){
          $('#inputNewPassword').val('');
          $('#inputNewPassword2').val('');
          alert('Saved');
        },
        error: function(jqXHR, textStatus, errorThrown){
          alert('Save failed: '+jqXHR.responseText);
        }
      });
      return false;
    });
    $("#cards").on('click','.delete',function(e){
      $(this).closest('.col-sm-4').remove();
    });
  });
})(this.jQuery, this, this.document);