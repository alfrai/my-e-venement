<?php use_javascript('srv-answers-ajax') ?>
<?php use_stylesheet('srv-answers-ajax') ?>
<div id="srv-answers">
  <?php $filters = 'filters[survey_id]='.$form->getObject()->id ?>
  <a href="<?php echo url_for('answer/index?'.$filters) ?>" data-filters-url="<?php echo $filters ?>">
</div>
