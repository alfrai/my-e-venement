<div class="ui-helper-clearfix">
<ul class="sf_admin_actions_form" style="font-size: 13px;">
  <li class="sf_admin_action_list">
    <a class="fg-button ui-state-default fg-button-icon-left" href="<?php echo url_for($sf_request->getParameter('id') ? 'event/show?id='.$sf_request->getParameter('id') : '@event') ?>">
      <span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>
      <?php if ( $sf_request->getParameter('id') ): ?>
      <?php echo __('Event') ?>
      <?php else: ?>
      <?php echo __('Back to list',null,'sf_admin') ?>
      <?php endif ?>
    </a>
  </li>
  <?php /*
  <?php if (!( is_array($f = $sf_data->getRaw('sf_user')->getAttribute('event.filters', array(), 'admin_module')) && count($f) == 0 )): ?>
  <li class="sf_admin_action_reset_filters fg-button-mini fg-button ui-state-default fg-button-icon-left"><a href="<?php echo url_for('calendar/reset') ?>"><span class="ui-icon ui-icon-circle-close"></span><?php echo __('Reset filters') ?></a></a></li>
  <?php endif ?>
  */ ?>
  <li class="sf_admin_action_refetch_data fg-button-mini fg-button ui-state-default fg-button-icon-left"><a href="#" onclick="javascript: $('#fullcalendar .calendar').fullCalendar('refetchEvents'); return false;"><span class="ui-icon ui-icon-arrowrefresh-1-e"></span><?php echo __('Refresh') ?></a></li>
  <li class="sf_admin_action_jump_to_date"><form class="fg-button ui-state-default" method="get" action="#"><p><?php
    $w = new liWidgetFormJQueryDateText(array('culture' => $sf_user->getCulture()));
    echo $w->render('jumpToDate');
  ?><script type="text/javascript">$(document).ready(function(){
    $('.sf_admin_action_jump_to_date form').submit(function(){
      var form = this;
      $.each(['month','day'], function(i, id){
        if ( $(form).find('[name="jumpToDate['+id+']"]').val().length == 1 )
          $(form).find('[name="jumpToDate['+id+']"]').val('0'+$(form).find('[name="jumpToDate['+id+']"]').val());
      });
      
      var count = 0;
      $(this).find('input[type=text]').each(function(){
        if ( $(this).val() ) count++;
      });
      if ( count == 3 )
      {
        $('#fullcalendar .calendar').fullCalendar('gotoDate', tmp =
          $(this).find('[name="jumpToDate[year]"]').val()+'-'+
          $(this).find('[name="jumpToDate[month]"]').val()+'-'+
          $(this).find('[name="jumpToDate[day]"]').val()
        );
      }
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
  <li class="event_filters">
    <?php use_javascript('/cxFormExtraPlugin/js/cx_open_list.js'); ?>
    <?php use_stylesheet('/cxFormExtraPlugin/css/cx_open_list.css'); ?>
    <?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js'); ?>
    <?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css'); ?>
    <a style="display: none;" href="<?php echo url_for('event/onlyFilters') ?>"></a>
  </li>
</ul>
</div>
