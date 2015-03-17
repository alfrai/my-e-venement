<?php $arr = array(); ?>
<?php foreach ( $form->getObject()->Feeders as $feeder ): ?>
  <?php $arr[] = link_to($feeder, 'hold/edit?id='.$feeder->id) ?>
<?php endforeach ?>
<?php if ( count($arr) > 0 ): ?>
<div class="sf_admin_form_row sf_admin_foreignkey sf_admin_form_field_show_feeders">
  <label for="feeders"><?php echo __('Feeders') ?></label>
  <div class="label ui-helper-clearfix"></div>
  <ul class="widget">
    <li><?php echo implode('</li><li>', $arr) ?></li>
  </ul>
</div>
<?php endif ?>


