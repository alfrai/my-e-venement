<div class="sf_admin_form_row sf_admin_form_field_transaction_id">
  <span class="transaction_id">#<input type="text" name="transaction_id" value="" readonly="readonly" /></span>
  <a href="#" class="remove_transaction_id"></a>
  <button
    class="ajax"
    data-url="<?php echo url_for('hold/getTransactionId?id='.$form->getObject()->id) ?>"
    name="transfert_to_transaction"
  >
    <?php echo __('New transaction') ?>
  </button>
  <div class="label ui-helper-clearfix">
    <div class="help">
      <span class="ui-icon ui-icon-help floatleft"></span>
      <?php echo __('Book released seats immediatly') ?>
    </div>
  </div>
</div>
