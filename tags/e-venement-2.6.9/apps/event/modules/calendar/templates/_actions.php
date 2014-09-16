<div class="ui-helper-clearfix">
<ul class="sf_admin_actions_form" style="font-size: 13px;">
  <li class="sf_admin_action_list">
    <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for($sf_request->getParameter('id') ? 'event/show?id='.$sf_request->getParameter('id') : '@event') ?>">
      <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
      <?php echo __('Back to list',null,'sf_admin') ?>
    </a>
  </li>
  <li class="sf_admin_action_refetch_data fg-button-mini fg-button ui-state-default fg-button-icon-left"><a href="#" onclick="javascript: $('#fullcalendar').fullCalendar('refetchEvents'); return false;"><span class="ui-icon ui-icon-arrowrefresh-1-e"></span><?php echo __('Refresh') ?></a></li>
  <li class="sf_admin_action_jump_to_date"><form class="fg-button ui-state-default" method="get" action="#"><p><?php
    $w = new liWidgetFormJQueryDateText(array('culture' => $sf_user->getCulture()));
    echo $w->render('jumpToDate');
  ?><script type="text/javascript">$(document).ready(function(){
    $('.sf_admin_action_jump_to_date').submit(function(){
      var count = 0;
      $(this).find('input[type=text]').each(function(){ if ( $(this).val() ) count++; });
      if ( count == 3 )
        $('#fullcalendar .calendar').fullCalendar('gotoDate', $(this).find('[name="jumpToDate[year]"]').val(), parseInt($(this).find('[name="jumpToDate[month]"]').val())-1, $(this).find('[name="jumpToDate[day]"]').val());
      $('#transition .close').click();
      return false;
    });
    $('.sf_admin_action_jump_to_date input[type=text]').change(function(){ $(this).closest('form').submit(); });
  });</script><input type="submit" name="submit" value="<?php echo __('Go') ?>" /></p></form>
  </li>
  <li class="sf_admin_action_edit">
    <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo $export_url ?>" target="_blank">
      <span class="ui-icon ui-icon-circle-plus"></span>
      <?php echo __('Export') ?>
    </a>
  </li>
</ul>
</div>
