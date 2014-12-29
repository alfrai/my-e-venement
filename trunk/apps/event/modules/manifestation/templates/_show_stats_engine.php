  <a class="data-url" href="<?php echo url_for('manifestation/statsRawData?id='.$form->getObject()->id) ?>"></a>
  <script type="text/javascript"><!--
    $(document).ready(function(){
      $.get($('#sf_fieldset_statistics .data-url').prop('href'), function(json){
        $.each(json, function(bunch, data){
          $.each(data, function(id, value) {
            if ( typeof(value) == 'object' )
            {
              $.each(value, function(i, user){
                $('<td></td>').text(user.nb)
                  .appendTo($('#sf_fieldset_statistics .'+bunch+' .'+id));
                if ( $('#sf_fieldset_statistics .'+bunch+' thead .id-'+i).length == 0 )
                  $('<td></td>').addClass('id-'+i).text(user.user).addClass('ui-state-default').addClass('ui-th-column')
                    .appendTo($('#sf_fieldset_statistics .'+bunch+' thead tr'));
              });
            }
            else
              $('#sf_fieldset_statistics .'+bunch+' .'+id+' td').text(value);
          });
        });
        
        // hide the data related to seated ticketting if nothing is interesting
        setTimeout(function(){
          $('#sf_fieldset_statistics .gauges').hide();
          $('#sf_fieldset_statistics .gauges tbody td').each(function(){
            if ( parseInt($(this).text(),10) )
              $('#sf_fieldset_statistics .gauges').show();
          });
          $('#sf_fieldset_statistics .filling .free .seated').text($('#sf_fieldset_statistics .gauges .total td').text());
        },500);
      });
      
      $.get($('.sf_admin_field_workspaces_list .gauge-gfx:first').prop('href'), function(json){
        $('#sf_fieldset_statistics .filling .total .total').text(json.total);
        $('#sf_fieldset_statistics .filling .total .seated').text(json.seats);
        $('#sf_fieldset_statistics .filling .total .not-seated').text(json.total-json.seats);
        $('#sf_fieldset_statistics .filling .not-free .total').text(json.total-json.free);
        $('#sf_fieldset_statistics .filling .printed .total').text(json.booked.printed);
        $('#sf_fieldset_statistics .filling .ordered .total').text(json.booked.ordered);
        $('#sf_fieldset_statistics .filling .free .total').text(json.free);
        
        // needs upstream data...
        var inter = setInterval(function(){
          console.error('interval');
          if ( $('#sf_fieldset_statistics .filling .free .seated').text() == '-' )
            return;
          var freeseats = parseInt($('#sf_fieldset_statistics .filling .free .seated').text(),10);
          $('#sf_fieldset_statistics .filling .not-free .seated').text(json.seats - freeseats);
          $('#sf_fieldset_statistics .filling .not-free .not-seated').text(json.total - json.free - json.seats + freeseats);
          $('#sf_fieldset_statistics .filling .free .not-seated').text(json.free-freeseats);
          clearInterval(inter);
        },500);
      });
    });
  --></script>
