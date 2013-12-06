<?php include_partial('default/assets') ?>
<?php use_helper('CrossAppLink') ?>

<div id="global-search">
  <div class="ui-widget-content" id="contacts">
    <a href="<?php echo cross_app_url_for('rp', 'contact/search?s='.$search) ?>"></a>
  </div>
  <div class="ui-widget-content" id="organisms">
    <a href="<?php echo cross_app_url_for('rp', 'organism/search?s='.$search) ?>"></a>
  </div>
  <div class="ui-widget-content" id="events">
    <a href="<?php echo cross_app_url_for('event', 'event/search?s='.$search) ?>"></a>
  </div>
  <div class="ui-widget-content" id="locations">
    <a href="<?php echo cross_app_url_for('event', 'location/search?s='.$search) ?>"></a>
  </div>
  <div class="ui-widget-content" id="resources">
    <a href="<?php echo cross_app_url_for('event', 'resource/search?s='.$search) ?>"></a>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $('#global-search .ui-widget-content').each(function(){
      var elt = this;
      if ( $(this).find('a').length > 0 )
      $.get($(this).find('a').prop('href'),function(data){
        $(elt).html($($.parseHTML(data)).find('.sf_admin_list'));
      });
    });
  });
</script>
