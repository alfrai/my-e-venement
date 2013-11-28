<h2 class="loading"><?php echo __('Loading...') ?></h2>
<script type="text/javascript">
  $.get('<?php echo url_for('manifestation/showSpectators?id='.$manifestation->id) ?>',function(data){
    $('#sf_fieldset_spectators').prepend($($.parseHTML(data)).find('#sf_fieldset_spectators > *')).find('.loading').remove();
    
    $('#sf_fieldset_spectators table tbody').each(function(){
      
      // create the workspace list
      workspaces = new Array();
      $(this).find('tr').each(function(){
        if ( $(this).find('.workspace').length > 0 && $.trim($(this).find('.workspace').html()) )
        if ( workspaces.indexOf($.trim($(this).find('.workspace').html())) == -1 )
          workspaces[workspaces.length] = $.trim($(this).find('.workspace').html());
      });
      
      // create the workspaces lines
      for ( i = 0 ; i < workspaces.length ; i++ )
        $(this).prepend('<tr class="workspace"><td colspan="2" class="name">'+workspaces[i]+'</td><td class="tickets">0</td><td class="price">0</td><td>-</td><td>-</td><td>-</td>');
        
      // ordering the table content
      totals = [0,0];
      trs = $(this).find('tr:not(.workspace)');
      for ( i = trs.length - 1 ; i >= 0 ; i-- )
      {
        workspaces = trs.eq(i).closest('tbody').find('tr.workspace');
        for ( j = 0 ; j < workspaces.length ; j++ )
        if ( workspaces.eq(j).find('.name').html() == trs.eq(i).find('.workspace').html() )
        {
          trs.eq(i).insertAfter(workspaces.eq(j));
          for ( k = trs.eq(i).find('.tickets .tickets').length - 1 ; k >= 0 ; k-- )
            workspaces.eq(j).find('.tickets').html(parseInt(workspaces.eq(j).find('.tickets').html(),10)+parseInt(trs.eq(i).find('.tickets .tickets').eq(k).find('.qty').html(),10));
          str = workspaces.eq(j).find('.price').html();
          workspaces.eq(j).find('.price').html((parseFloat(workspaces.eq(j).find('.price').html())+parseFloat(trs.eq(i).find('.price').html().replace(',','.'))).toFixed(2)+'&nbsp;€');
        }
      }
      
      // coloring the lines
      trs = $(this).find('tr');
      for ( i = 0 ; i < trs.length ; i++ )
      if ( i%2 == 0 )
        trs.eq(i).removeClass('overlined');
      else
        trs.eq(i).addClass('overlined');
      
      // removing useless workspace's names
      $(this).find('tr .workspace').hide();
    });
    
    <?php include_partial('show_print_part_js',array('tab' => 'spectators')) ?>
  });
</script>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_tickets_list_batch',array('form' => $form)) ?>
<?php endif ?>
