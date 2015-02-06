<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_javascript('/liFullcalendarPlugin/momentjs/moment.min.js') ?>
<?php use_javascript('/liFullcalendarPlugin/fullcalendar/fullcalendar.min.js') ?>
<?php use_javascript('/liFullcalendarPlugin/fullcalendar/lang-all.js') ?>
<?php use_stylesheet('/liFullcalendarPlugin/fullcalendar/fullcalendar.css','',array('media' => 'all')) ?>
<?php use_stylesheet('/liFullcalendarPlugin/fullcalendar/fullcalendar.print.css','',array('media' => 'print')) ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="calendar">
  </div>
<script type="text/javascript"><!--
if ( li == undefined )
  var li = {};

$(document).ready(function(){
  $('#fullcalendar .calendar, #more .calendar').fullCalendar({
    lang: '<?php echo $sf_user->getCulture() ?>',
    <?php if ( isset($start_date) && $start_date && strtotime($start_date) > 0 ): ?>
    defaultDate: '<?php echo $start_date ?>',
    //day: <?php echo date('d', strtotime($start_date)) ?>,
    //month: <?php echo date('m', strtotime($start_date))-1 ?>,
    //year: <?php echo date('Y', strtotime($start_date)) ?>,
    <?php endif ?>
    <?php if ( isset($defaultView) ): ?>
    defaultView: '<?php echo $defaultView ?>',
    <?php endif ?>
    minTime: '<?php echo sfConfig::get('app_listing_min_time','8') ?>:00:00',
    maxTime: '<?php echo sfConfig::get('app_listing_max_time','24') ?>:00:00',
    firstHour: '<?php echo sfConfig::get('app_listing_first_hour','15') ?>:00:00',
    theme: true,
    buttonText: {
      resourceDay:  '<?php echo __('Day/Location') ?>',
    },
    timeFormat: 'H:mm',
    axisFormat: 'H:mm',
    allDayText: "<?php echo __('All day long') ?>",
    allDayDefault: false,
    allDaySlot: false,
    header: { left: 'today prev,next', center: 'title', right: 'month,agendaWeek,resourceWeek,agendaDay,resourceDay' },
    titleFormat: { day: 'dddd LL' },
    
    <?php $resources = Doctrine::getTable('Location')->createQuery('l')->andWhere('l.place = TRUE')->orderBy('l.rank IS NULL, l.rank, l.name')->execute() ?>
    resources: [
      <?php foreach ( $resources as $res ): ?>
      { name: '<?php echo str_replace("'", "\\'", $res) ?>', id: 'resource-<?php echo $res->id ?>', readonly: true },
      <?php endforeach ?>
    ],
    
    eventTextColor: 'black',
    eventBackgroundColor: 'white',
    eventSources: [
      <?php foreach ( $urls as $url ): ?>
      {
        url: '<?php echo $url ?>',
        //color: 'LightGreen',
        error: function(){ alert('<?php echo __('Error loading the data from manifestations',null,'sf_admin') ?>'); }
      },
      <?php endforeach ?>
    ],
    lazyFetching: false,
    
    eventResize: function(event, dayDelta, minuteDelta, revertFunc){
      $.ajax({
        url: '<?php echo url_for('manifestation/slideDuration') ?>',
        data: { id: event.id, days: dayDelta, minutes: minuteDelta },
        type: 'post'
      })
      .done(function(){
        $('#fullcalendar, #more .manifestation_calendar').fullCalendar('refetchEvents');
      })
      .fail(function(){
        revertFunc();
        alert("<?php echo __("Error changing the manifestation's duration",null,'sf_admin') ?>");
      });
    },
    eventDrop: function(event, dayDelta, minuteDelta, revertFunc){
      $.ajax({
        url: '<?php echo url_for('manifestation/slideHappensAt') ?>',
        data: { id: event.id, days: dayDelta, minutes: minuteDelta },
        type: 'post'
      })
      .done(function(){
        $('#fullcalendar, #more .manifestation_calendar').fullCalendar('refetchEvents');
      })
      .fail(function(){
        revertFunc();
        alert('<?php echo __('Error moving the manifestation') ?>');
      });
    },
    eventClick: function(event, e){
      if ( event.hackurl != undefined )
      {
        if ( e.ctrlKey || e.which == 2 )
          window.open(event.hackurl);
        else
          window.location = event.hackurl;
      }
    },
    eventAfterRender: function(event, element){
      if ( event.css )
      $.each(event.css, function(index, value){
        $(element).css(index, value);
      });
      
      if ( event.hacktitle )
        $(element).prop('title', event.hacktitle);
    },
  });
  
  if ( typeof LI === "undefined" )
    LI = {};
  
  // HACKS
  $('#fullcalendar .fc-header .fc-button').click(function(){
    $('.fc-agenda-days thead .fc-agenda-axis').remove('> *');
    $('<div></div>')
      .css('width', '50px') // arbitrary, no better way found
      .appendTo($('.fc-agenda-days thead .fc-agenda-axis'));
  }).click();
});
--></script>
</div>
