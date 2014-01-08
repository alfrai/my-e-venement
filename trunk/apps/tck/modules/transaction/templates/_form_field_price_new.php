<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'method' => 'get',
  'target' => '_blank',
  'autocomplete' => 'off',
)) ?><p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form['qty']->render(array('pattern' => '-{0,1}\d+', 'title' => __('Positive or negative numbers only'), 'maxlength' => 4)) ?>
</p>
<?php use_javascript('tck-touchscreen-prices') ?>
</form>
