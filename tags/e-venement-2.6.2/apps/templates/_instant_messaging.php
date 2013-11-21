<?php if ( sfConfig::get('project_messaging_enable',false) && is_object($sf_user->getJabber()) && $sf_user->getJabber()->count() > 0 ): ?>
  <?php $jabber_id = explode('@',$sf_user->getJabber(0)->jabber_id) ?>
    <script type="text/javascript">
      jQuery.ajaxSetup({cache: true});
      jQuery.getScript("/liJappixPlugin/php/get.php?l=fr&t=js&g=mini.xml", function() {
        MINI_GROUPCHATS = ["<?php echo implode('", "',sfConfig::get('project_messaging_chatrooms',array())) ?>"];
        MINI_SUGGEST_GROUPCHATS = ["<?php echo implode('", "',sfConfig::get('project_messaging_chatrooms',array())) ?>"];
        launchMini(true, false, "<?php echo $jabber_id[1] ?>", "<?php echo $jabber_id[0] ?>", "<?php echo $sf_user->getJabber(0)->password ?>");
      });
    </script>
<?php endif ?>
