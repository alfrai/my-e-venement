<form action="#" method="get" onsubmit="javascript: return false;" class="srv-present-widget">
  <?php echo $sf_data->getRaw('survey_query')->renderLabel() ?>
  <div class="widget"><?php echo $sf_data->getRaw('survey_query')->render(); ?></div>
</form>
