  <a class="meta-data-url" href="<?php echo url_for('manifestation/statsMetaData?id='.$form->getObject()->id) ?>"></a>
  <a class="filling-data-url" href="<?php echo url_for('manifestation/statsFillingData?id='.$form->getObject()->id) ?>"></a>
  <script type="text/javascript"><!--
    $(document).ready(function(){
      $.get($('#sf_fieldset_statistics .meta-data-url').prop('href'), function(json){
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
      });
      
      $('#sf_fieldset_statistics .filling-complete .min + .max .nb').closest('td').hide();
      $('#sf_fieldset_statistics .filling-complete .min + .max .th').hide();
      $('#sf_fieldset_statistics .filling-complete .min .nb').closest('td').prop('rowspan', 2);
      $.get($('#sf_fieldset_statistics .filling-data-url').prop('href'), function(json){
        console.error(json);
        $('#sf_fieldset_statistics .filling .free .seated').text(json.seats.free.all.nb);
        
        // this is a super-powerful compression of the "data dispatcher", to avoid hidden bugs as much as we can
        $.each({ seats: 'st', gauges: 'at' }, function(type, tckprefix){
        $.each(['free', 'ordered', 'printed', 'total', 'not-free'], function(i, data){
        $.each({ online: 'og', all: 'ag' }, function(state, gaugeprefix){
          var nb;
          var calculated = {
            total: {
              nb: json[type].free[state].nb + json[type].ordered[state].nb + json[type].printed[state].nb,
              min: {
                money: json[type].free[state].min.money + json[type].ordered[state].money + json[type].printed[state].money,
                money_txt: LI.format_currency(json[type].free[state].min.money + json[type].ordered[state].money + json[type].printed[state].money, false)
              },
              max: {
                money: json[type].free[state].max.money + json[type].ordered[state].money + json[type].printed[state].money,
                money_txt: LI.format_currency(json[type].free[state].max.money + json[type].ordered[state].money + json[type].printed[state].money, false)
              },
            },
            'not-free': {
              nb: json[type].ordered[state].nb + json[type].printed[state].nb,
              money: json[type].ordered[state].money + json[type].printed[state].money,
              money_txt: LI.format_currency(json[type].ordered[state].money + json[type].printed[state].money, false)
            }
          }
          if ( data != 'total' && data != 'not-free' )
            nb = json[type][data][state].nb;
          else if ( data == 'total' )
            nb = calculated['total'].nb;
          else if ( data == 'not-free' )
            nb = calculated['not-free'].nb;
          
          // numbers
          $('#sf_fieldset_statistics .filling-complete .'+data+' .f-'+tckprefix+'-'+gaugeprefix+' .nb')
            .text(nb);
          $('#sf_fieldset_statistics .filling-complete .'+data+' .f-'+tckprefix+'-'+gaugeprefix+' .percent')
            .text(LI.format_currency(100 * nb / calculated['total'].nb, false, true, ''));
          
          // money
          $.each(['min', 'max'], function(id, key){
            if ( data != 'total' && data != 'not-free' )
            {
              var value = json[type][data][state][key];
              if ( value == undefined )
              {
                key = '';
                value = json[type][data][state];
              }
            }
            else if ( data == 'total' )
              value = calculated['total'][key];
            else if ( data == 'not-free' )
            {
              key = '';
              value = calculated['not-free'];
            }
            
            console.error(data);
            console.error(value);
            $('#sf_fieldset_statistics .filling-complete .'+data+(key ? '.'+key : '')+' .sos-'+tckprefix+'-'+gaugeprefix+' .money')
              .text(value.money_txt);
            $('#sf_fieldset_statistics .filling-complete .'+data+(key ? '.'+key : '')+' .sos-'+tckprefix+'-'+gaugeprefix+' .percent')
              .text(LI.format_currency(100 * value.money / ( key && data == 'free' ? calculated['total'][key].money : calculated['total'].max.money ), false, true, ''));
          });
        }); // type
        }); // data
        }); // state
        
        // hide the data related to seated ticketting if nothing is interesting
        setTimeout(function(){
          $('#sf_fieldset_statistics .seats').hide();
          $('#sf_fieldset_statistics .seats tbody td').each(function(){
            if ( parseInt($(this).text(),10) )
              $('#sf_fieldset_statistics .seats').show();
          });
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
