<h2 class="loading"><?php echo __('Loading...') ?></h2>
<script type="text/javascript">
  $.get('<?php echo url_for('manifestation/showTickets?id='.$manifestation->id) ?>',function(data){
    $('#sf_fieldset_tickets').prepend($($.parseHTML(data)).find('#sf_fieldset_tickets > *')).find('.loading').remove();
    
    <?php include_partial('show_print_part_js',array('tab' => 'tickets')) ?>
  });
</script>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_tickets_list_batch',array('form' => $form)) ?>
<?php endif ?>
