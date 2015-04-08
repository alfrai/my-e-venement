<h2 class="loading"><?php echo __('Loading...') ?></h2>
<script type="text/javascript">
  if ( LI == undefined )
    var LI = {};
      
  $.get('<?php echo url_for('manifestation/showTickets?id='.$manifestation->id) ?>', LI.manifShowTickets = function(data){
    data = $.parseHTML(data);
    $('#sf_fieldset_tickets > *').remove();
    $('#sf_fieldset_tickets').prepend($(data).find('#sf_fieldset_tickets > *'));
    
    <?php include_partial('show_print_part_js',array('tab' => 'tickets', 'jsFunction' => 'LI.manifShowTickets')) ?>
  });
</script>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_tickets_list_batch',array('form' => $form)) ?>
<?php endif ?>
