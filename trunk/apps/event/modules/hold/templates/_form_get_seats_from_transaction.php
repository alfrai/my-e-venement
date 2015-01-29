<div class="sf_admin_form_row sf_admin_form_field_get_back_seats_from_transaction_id">
  <span class="transaction_id">#<input class="source" type="text" name="get_back_seats_from_transaction_id" value="" /></span>
  <button
    class="ajax"
    data-url="<?php echo url_for('hold/getBackSeatsFromTransactionId?id='.$form->getObject()->id) ?>"
    name="get_back_seats"
  >
    <?php echo __('Get back seats from this transaction') ?>
  </button>
  <div class="label ui-helper-clearfix">
    <div class="help">
      <span class="ui-icon ui-icon-help floatleft"></span>
      <?php echo __("The given transaction needs to be opened, the only concerned tickets are the unsold tickets related to this hold's manifestation.") ?>
    </div>
  </div>
</div>
