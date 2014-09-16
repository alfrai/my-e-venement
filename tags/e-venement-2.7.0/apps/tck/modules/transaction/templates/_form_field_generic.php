<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'target' => '_blank',
  'method' => 'get',
)) ?>
<p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field]->renderLabel() ?>
  </span><?php echo $form[$field] ?></span>
</p></form>
