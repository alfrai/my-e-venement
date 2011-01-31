<?php use_javascript('jquery','first') ?>
<?php use_javascript('calendar') ?>
<?php include_partial('global/assets') ?>

<div class="ui-widget">
<div class="ui-widget-header ui-corner-all fg-toolbar"><h1><?php echo __('Agenda') ?></h1></div>
<script type="text/javascript">
  // get back the root url, and then the phpicalendar url
  var relative_url_phpicalendar = '<?php echo $sf_request->getRelativeUrlRoot().'/'.sfConfig::get('app_phpicalendar_web_dir'); ?>/';
</script>
<iframe
  id="calendar"
  src="http://localhost/e-venement-2/phpicalendar/month.php?cal=nocal&getdate=<?php echo date('Ymd',$calnow) ?>"
  class="ui-resizable ui-widget-content ui-corner-all"
>
</iframe>
</div>
