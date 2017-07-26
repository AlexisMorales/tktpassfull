(function ($, window, document, undefined) {
  $(function(){
    var $resellModal = $('#resellModal');
    function updateResellModal(ids){
      $resellModal.removeData('ids');
      $.ajax({
        url: 'https://api.tktpass.com/tickets/?ids='+encodeURIComponent(ids),
        method: 'GET',
        dataType: 'json',
        xhrFields: {
           withCredentials: true
        },
        success: function(data, textStatus, jqXHR){
          var ids = '';
          $.each(data,function(i,type){
            ids += (ids?',':'')+type.ids;
          });
          $resellModal.data('ids',ids);
          $resellModal.find('.modal-title').html(data[0].name);
          $resellModal.find('.ticket-type').html(data[0].event_ticket_type_name);
          var slider = document.getElementById('resell-slider');
          if(!slider.noUiSlider){
            noUiSlider.create(slider, {
              start: [ Math.round((2/30)*parseInt(data[0].event_ticket_type_price))*10 ],
              step: 10,
              range: {
                'min': [ 0 ],
                'max': [ parseInt(data[0].event_ticket_type_price) ]
              }
            });
            var sliderValueElement = document.getElementById('slider-value');
            slider.noUiSlider.on('update', function( values, handle ) {
              if(values[handle] > 0)
                sliderValueElement.innerHTML = '£'+(values[handle]/100).toFixed(2);
              else
                sliderValueElement.innerHTML = 'FREE?!';
            });
            /*slider.noUiSlider.on('end', function( values, handle ) {
              slider.noUiSlider.set([Math.round(values[handle]/10)*10]);
            });*/
          } else {
            slider.noUiSlider.updateOptions({
              range: {
                'min': [ 0 ],
                'max': [ parseInt(data[0].event_ticket_type_price) ]
              }
            });
            slider.noUiSlider.set([ Math.round((2/30)*parseInt(data[0].event_ticket_type_price))*10 ]);
          }
        },
        error: function(jqXHR, textStatus, errorThrown){
          var json = $.parseJSON(jqXHR.responseText);
          alert('Error occurred: '+(json && json.err ? json.err : errorThrown));
        }
      });
    }
    $('.sell-ticket').click(function(){
      var $ticket = $(this).closest('.ticket-buttons').prev();
      if(!$ticket.data('ids')){
        alert('Error reselling ticket. Try reloading.');
        return false;
      }
      $resellModal.data('ticket', $ticket[0]);
      updateResellModal($ticket.data('ids'));
      $resellModal.modal('show');
    });
    $('.transfer-ticket, .email-ticket, .delete-ticket').click(function(){
      alert('Coming soon');
      //$('#transferModal').modal('show');
    });

    $resellModal.find('.step-1 .btn-success').click(function(){
      $.ajax({
        url: 'https://api.tktpass.com/resell/'+$resellModal.data('ids').split(',')[0],
        method: 'POST',
        data: {price:document.getElementById('resell-slider').noUiSlider.get()},
        dataType: 'json',
        xhrFields: {
           withCredentials: true
        },
        success: function(data, textStatus, jqXHR){
          console.log('success', data);
          var $ticket = $($resellModal.modal('hide').data('ticket'));
          var ids = $resellModal.data('ids').split(',').splice(1);
          if(ids.length>0){
            $ticket.find('.ticket-quantity').html(ids.length);
            $ticket.attr('data-ids',ids.join(','));
            $ticket.data('ids',ids.join(','));
          } else $ticket.parent().remove();
        },
        error: function(jqXHR, textStatus, errorThrown){
          var json = $.parseJSON(jqXHR.responseText);
          alert('Error occurred: '+(json && json.err ? json.err : errorThrown));
        }
      });
    });

  });
})(this.jQuery, this, this.document);