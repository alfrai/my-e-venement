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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_javascript('/liFullcalendarPlugin/fullcalendar.min.js') ?>
<?php use_stylesheet('/liFullcalendarPlugin/fullcalendar.css') ?>
<?php use_stylesheet('/liFullcalendarPlugin/fullcalendar.print.css','',array('media' => 'print')) ?>
<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="manifestation_calendar">
  </div>
<script type="text/javascript"><!--
$(document).ready(function(){
  $('#fullcalendar, #more .manifestation_calendar').fullCalendar({
    firstDay: 1,
    theme: true,
    monthNames: [ 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ],
    monthNamesShort: [ 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc' ],
    dayNames: [ 'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi' ],
    dayNamesShort: [ 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ],
    buttonText: {
      today:    "aujourd'hui",
      month:    'mois',
      week:     'semaine',
      day:      'jour'
    },
    titleFormat: { month: 'MMMM yyyy', week: "d[ MMM][ yyyy]{ - d MMM yyyy}", day: 'dddd d MMM yyyy' },
    columnFormat: { week: 'ddd d/M', day: 'dddd d/M' },
    timeFormat: 'H(:mm)',
    allDayText: 'Journée entière',
    allDayDefault: false,
    header: { left: 'today prev,next', center: 'title', right: 'month,agendaWeek,agendaDay' },
    
    eventTextColor: 'black',
    eventBackgroundColor: 'white',
    eventSources: [
      <?php foreach ( $urls as $url ): ?>
      {
        url: '<?php echo $url ?>',
        //color: 'LightGreen',
        error: function(){ alert('<?php echo __('Error loading the data from manifestations') ?>'); }
      },
      <?php endforeach ?>
    ],
    
    eventResize: function(event, dayDelta, minuteDelta){
      $.ajax({
        url: '<?php echo url_for('manifestation/slideDuration') ?>',
        data: { id: event.id, days: dayDelta, minutes: minuteDelta },
        type: 'post'
      })
      .done(function(){
      })
      .fail(function(){
        alert("<?php echo __("Error changing the event's duration") ?>");
      });
    },
    eventDrop: function(event, dayDelta, minuteDelta, revertFunc){
      $.ajax({
        url: '<?php echo url_for('manifestation/slideHappensAt') ?>',
        data: { id: event.id, days: dayDelta, minutes: minuteDelta },
        type: 'post'
      })
      .done(function(){
      })
      .fail(function(){
        alert('<?php echo __('Error moving the event') ?>');
      });
    },
    eventClick: function(event){
      window.open(event.hackurl);
    }
  });
});
--></script>
</div>
