<p class="tab-export">
  <a class="fg-button fg-button-icon-left ui-state-default ui-priority-secondary" href="<?php echo url_for('manifestation/export?status=printed&id='.$manifestation_id) ?>" target="_blank" title="<?php echo __('Export') ?>">
    <span class="ui-icon ui-icon-person"></span>
    <?php echo __('With ticket') ?>
  </a>
  <a class="fg-button fg-button-icon-left ui-state-default ui-priority-secondary" href="<?php echo url_for('manifestation/export?status=ordered&id='.$manifestation_id) ?>" target="_blank" title="<?php echo __('Export') ?>">
    <span class="ui-icon ui-icon-person"></span>
    <?php echo __('Reservations') ?>
  </a>
  <?php if ( sfConfig::get('project_tickets_count_demands',false) ): ?>
  <a class="fg-button fg-button-icon-left ui-state-default ui-priority-secondary" href="<?php echo url_for('manifestation/export?status=asked&id='.$manifestation_id) ?>" target="_blank" title="<?php echo __('Export') ?>">
    <span class="ui-icon ui-icon-person"></span>
    <?php echo __('Demands') ?>
  </a>
  <?php endif ?>
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
