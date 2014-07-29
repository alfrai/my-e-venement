<?php $widget = $sf_data->getRaw('survey_query')->getWidget() ?>
<form action="#" method="get" onsubmit="javascript: return false;" class="srv-present-widget">
  <label for=""><?php echo $widget->getLabel() ?></label>
  <div class="widget <?php echo $widget->hasOption('choices') ? 'has-choices' : '' ?>">
    <?php echo $widget->render('glop'); ?>
  </div>
  <?php foreach ( $widget->getStylesheets() as $css ) use_stylesheet($css) ?>
  <?php foreach ( $widget->getJavascripts() as $js  ) use_javascript($js) ?>
</form>
