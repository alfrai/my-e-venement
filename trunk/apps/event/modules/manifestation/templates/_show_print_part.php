<p class="tab-print">
  <a class="fg-button fg-button-icon-left ui-state-default" href="#">
    <span class="ui-icon ui-icon-print"></span>
    <?php echo __('Print',array(),'menu') ?>
  </a>
  <script type="text/javascript">
    $(document).ready(function(){
      $('#sf_fieldset_<?php echo $tab ?> .tab-print a').click(function(){
        $('body').addClass('sf_fieldset_<?php echo $tab ?>');
        print();
        
        // time out permitting the system to prepare the print before restoring things
        setTimeout(function(){ $('body').removeClass('sf_fieldset_<?php echo $tab ?>'); },500);
        
        return false;
      });
    });
  </script>
</p>
