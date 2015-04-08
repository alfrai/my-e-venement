      $('#sf_fieldset_<?php echo $tab ?> .tab-print a.refresh').click(function(){
        $.get($(this).prop('href'), <?php echo $jsFunction ?>);
        return false;
      });
      
      $('#sf_fieldset_<?php echo $tab ?> .tab-print a.print').click(function(){
        $('body').addClass('sf_fieldset_<?php echo $tab ?>');
        print();
        
        // time out permitting the system to prepare the print before restoring things
        setTimeout(function(){ $('body').removeClass('sf_fieldset_<?php echo $tab ?>'); },500);
        
        return false;
      });
