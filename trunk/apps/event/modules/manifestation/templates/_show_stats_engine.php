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
        },500);
      });
    });
  --></script>

