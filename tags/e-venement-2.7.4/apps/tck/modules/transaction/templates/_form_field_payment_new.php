<?php echo $form->renderFormTag(url_for('transaction/complete?id='.$transaction->id), array(
  'autocomplete' => 'off',
  'target' => '_blank',
  'method' => 'get',
)) ?>
<?php echo $form->renderHiddenFields() ?>
<p class="field_created_at field">
<?php echo $form['created_at']->renderLabel() ?>
<?php echo $form['created_at'] ?>
</p>
<p class="field_value field">
<?php echo $form['value']->renderLabel() ?>
<?php echo $form['value']->render(array('class' => 'for-board')) ?>
</p>
<?php if ( $sf_user->hasCredential('tck-payment') ): ?>
<div class="field_payment_method_id field">
<?php echo $form['payment_method_id'] ?>
</div>
<?php endif ?>
<p class="submit">
<button name="s" value="" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button"><?php echo __('Add') ?></button>
</p>
</form>
