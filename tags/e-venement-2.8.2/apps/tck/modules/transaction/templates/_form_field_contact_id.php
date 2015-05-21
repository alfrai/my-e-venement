<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'target' => '_blank',
  'method' => 'get',
)) ?>
<p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field]->renderLabel() ?>
  </span><?php echo $form[$field] ?></span>
  <a class="create-contact li_touchscreen_new" target="_blank" href="<?php echo cross_app_url_for('rp','contact/new') ?>?close"><?php echo image_tag('/sfDoctrinePlugin/images/new.png') ?></a>
</p>
</form>
<div class="data">
<?php if ( intval($form->getDefault('contact_id')) > 0 ): ?>
  <a href="<?php echo cross_app_url_for('rp', 'contact/show?id='.$form->getDefault('contact_id')) ?>" target="_blank">
    <?php echo $form['contact_id']->getWidget()->getVisibleValue($form->getDefault('contact_id')) ?>
  </a>
<?php endif ?>
</div>
<div class="picto">
  <?php echo $sf_data->getRaw('transaction')->Contact->groups_picto ?>
</div>
