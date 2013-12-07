<?php include_partial('default/assets') ?>
<?php use_helper('CrossAppLink') ?>

<div id="global-search">
  <div class="main ui-widget-header ui-corner-all"><h1><?php echo __('Global Search') ?></h1></div>
  <form class="ui-widget-content ui-corner-all" id="search" method="get" action="">
    <p>
      <input type="text" name="search" value="<?php echo $search ?>" />
      <input type="submit" name="go" value="<?php echo __('Search') ?>" />
    </p>
  </form>
  <div class="ui-widget-content ui-corner-all" id="contacts">
    <a href="<?php echo cross_app_url_for('rp', 'contact/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
  </div>
  <div class="ui-widget-content ui-corner-all" id="organisms">
    <a href="<?php echo cross_app_url_for('rp', 'organism/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
  </div>
  <div class="ui-widget-content ui-corner-all" id="events">
    <a href="<?php echo cross_app_url_for('event', 'event/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
  </div>
  <div class="ui-widget-content ui-corner-all" id="locations">
    <a href="<?php echo cross_app_url_for('event', 'location/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
  </div>
  <div class="ui-widget-content ui-corner-all" id="resources">
    <a href="<?php echo cross_app_url_for('event', 'resource/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
  </div>
  <div class="ui-widget-content ui-corner-all" id="transactions">
    <a href="<?php echo cross_app_url_for('tck', 'summary/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
    <span class="new-title"><?php echo __('Transactions') ?></span>
  </div>
  <div class="ui-widget-content ui-corner-all" id="grp">
    <a href="<?php echo cross_app_url_for('grp', 'professional/search?s='.$search) ?>"></a>
    <?php echo image_tag('wait.png', 'class="wait"') ?>
    <span class="new-title"><?php echo __('Schools & Groups') ?></span>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $('#global-search .ui-widget-content').each(function(){
      var elt = this;
      if ( $(this).find('a').length > 0 )
      $.get($(this).find('a').prop('href'),function(data){
        data = $.parseHTML(data);
        $(data).find('caption h1').html($(elt).find('.new-title').html());
        $(elt).html($(data).find('.sf_admin_list'));
        $(elt).find('.sf_admin_list thead a').removeAttr('href');
      });
    });
  });
</script>
