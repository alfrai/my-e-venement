<script type="text/javascript">
  $.get('<?php echo url_for('manifestation/showTickets?id='.$manifestation->id) ?>',function(data){
    $('#sf_fieldset_tickets').append($(data).find('#sf_fieldset_tickets > *'));
    
    $('#sf_fieldset_tickets .tab-print a').click(function(){
      $('body').addClass('sf_fieldset_tickets');
      print();
      
      // time out permitting the system to prepare the print before restoring things
      setTimeout(function(){ $('body').removeClass('sf_fieldset_tickets'); },500);
      
      return false;
    });
  });
</script>
