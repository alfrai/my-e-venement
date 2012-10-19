<p class="tab-export">
  <a class="fg-button fg-button-icon-left ui-state-default ui-priority-secondary" href="<?php echo url_for('manifestation/export?id='.$manifestation_id) ?>">
    <span class="ui-icon ui-icon-person"></span>
    <?php echo __('Export') ?>
  </a>
</p>
<?php /*
<script type="text/javascript"><!--
  $('.tab-export a').mouseenter(function(){ $(this).addClass('ui-state-hover'); });
  $('.tab-export a').mouseleave(function(){ $(this).removeClass('ui-state-hover'); });
  $('.tab-export a').click(function(){
    $.get($(this).attr('href'),function(){
      $('.sf_admin_flashes').prepend(
        $('<div class="notice ui-state-highlight ui-corner-all"><span class="ui-icon ui-icon-info floatleft"></span>&nbsp;<?php echo __($sf_user->getFlash('notice'), array(), 'sf_admin') ?></div>')
      );
      setTimeout(function(){
        $('.sf_admin_flashes .notice:first').remove();
      },3000);
    });
    return false;
  });
--></script>
*/ ?>
