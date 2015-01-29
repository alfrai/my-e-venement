<div class="sf_admin_form_row sf_admin_form_field_transaction_id">
  <span class="transaction_id">#<input type="text" name="transaction_id" value="" readonly="readonly" /></span>
  <a href="#" class="remove_transaction_id"></a>
  <button data-url="<?php echo url_for('hold/getTransactionId?id='.$form->getObject()->id) ?>" name="transfert_to_transaction"><?php echo __('Book released seats immediatly') ?></button>
</div>
