    <script type="text/javascript"><!--
      $(document).ready(function(){
        $.get('<?php echo url_for('ticket/manifs?limit='.$config['manifs_max_display']) ?>',ts_manifestations);
        $('#ts-manifestations').css('min-height',$('#ts-prices').height()+5+'px'); // for make'up
      });
      
      function ts_manifestations(data)
      {
        data = $.parseHTML(data);
        
        $('#ts-manifestations > *').remove();
        $(data).find('.manifestations_add > li').prependTo('#ts-manifestations');
        ts_manifestations_select();
        
        // pagination
        current_page = parseInt($(data).find('[name=manifs-page]').val(),10);
        $('#ts-manifestations').append('<li class="new-line"></li><li class="previous pager"><a href="<?php echo url_for('ticket/manifs?limit='.$config['manifs_max_display']) ?>&page='+(current_page-1)+'">&lt;&lt;</a></li><li class="pager beginning"><a href="<?php echo url_for('ticket/manifs?limit='.$config['manifs_max_display']) ?>">o</a></li><li class="pager next"><a href="<?php echo url_for('ticket/manifs?limit='.$config['manifs_max_display']) ?>&page='+(current_page+1)+'">&gt;&gt;</a></li>');
        $('#ts-manifestations .pager a').unbind().click(function(){
          $.get($(this).attr('href'),ts_manifestations);
          return false;
        });
      }
      
      function ts_manifestations_select()
      {
        $('#ts-tickets a, #ts-manifestations a').unbind().click(function(){ $(this).closest('li').click(); return false; });
        $('#ts-tickets li, #ts-manifestations li').unbind().click(function(){
          $('#ts-tickets .selected, #ts-manifestations .selected').removeClass('selected')
            .find('input[type=radio][checked]').removeAttr('checked');
          $(this).addClass('selected')
            .find('input[type=radio]').attr('checked',true);
          ticket_display_prices();
          ts_prices();
          ts_tickets();
        });
        $('#ts-manifestations li').click(function(){ $('#ts-tickets .ts-tickets-list').remove(); });
      }
    --></script>
    <ul id="ts-manifestations">
    </ul>
