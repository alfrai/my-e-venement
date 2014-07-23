<?php use_javascript('jquery','first') ?>
<?php use_javascript('calendar') ?>
<?php include_partial('global/assets') ?>

<div class="ui-widget">
  <div class="ui-widget-header ui-corner-all fg-toolbar"><h1><?php echo __('Agenda') ?></h1></div>
  <div class="ui-widget-content ui-corner-all">
    <?php include_partial('actions', array('export_url' => url_for('event/calendar'.($only_pending ? '?only_pending=true' : '')))) ?>
    <div id="fullcalendar">
      <?php include_partial('show_calendar',array(
        'defaultView' => sfConfig::get('app_listing_default_view','month'),
        'urls' => array(
          url_for('manifestation/list'.($only_pending ? '?only_pending=true' : '')),
        ),
      )) ?>
    </div>
  </div>
</div>
