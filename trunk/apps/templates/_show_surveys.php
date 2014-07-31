<?php if ( $sf_user->hasCredential('TODO') ): ?>

<?php use_javascript('srv-answers-ajax') ?>
<?php use_stylesheet('srv-answers-ajax') ?>

<?php if ( !$field ) $field = 'manifestation_id'; ?>
<?php $filters = 'filters[apply_to_'.$field.']='.$form->getObject()->id ?>

<div id="srv-answers">
  <a href="<?php echo cross_app_url_for('srv', 'answer/index?'.$filters) ?>" data-filters-url="<?php echo $filters ?>"></a>
</div>

<?php endif ?>
