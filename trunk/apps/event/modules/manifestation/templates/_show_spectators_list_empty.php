<script type="text/javascript">
  $.get('<?php echo url_for('manifestation/showSpectators?id='.$manifestation->id) ?>',function(data){
    $('#sf_fieldset_spectators').append($(data).find('#sf_fieldset_spectators > *'));
    $('#sf_fieldset_tickets').append($(data).find('#sf_fieldset_tickets > *'));
    
    <?php foreach ( array('sf_fieldset_spectators','#sf_fieldset_tickets') as $id ): ?>
    $('#<?php echo $id ?> .tab-print a').click(function(){
      $('body').addClass('<?php echo $id ?>');
      print();
      
      // time out permitting the system to prepare the print before restoring things
      setTimeout(function(){ $('body').removeClass('<?php echo $id ?>'); },500);
      
      return false;
    });
    <?php endforeach ?>
  });
</script>
