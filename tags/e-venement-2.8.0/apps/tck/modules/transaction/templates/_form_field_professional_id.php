<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'target' => '_blank',
  'method' => 'get',
)) ?>
<p>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field]->renderLabel() ?>
  </span><?php echo $form[$field] ?></span>
</p>
</form>
<div class="picto">
  <?php echo $sf_data->getRaw('transaction')->Professional->groups_picto ?>
  <?php echo $sf_data->getRaw('transaction')->Professional->Organism->groups_picto ?>
</div>
