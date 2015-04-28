<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_show_transaction_id">
  <label for="hold_transaction_rank"><?php echo __('Transaction') ?></label>
  <div class="label ui-helper-clearfix">
  </div>
  <div class="widget">
    <span class="transaction_id">#<?php echo link_to($form->getObject()->transaction_id, 'transaction/edit?id='.$form->getObject()->transaction_id) ?></span>
    <?php if ( $form->getObject()->Transaction->contact_id ): ?>
    <span class="contact_id"><?php echo cross_app_link_to($form->getObject()->Transaction->Contact, 'rp', 'contact/edit?id='.$form->getObject()->Transaction->contact_id) ?></span>
    <?php endif ?>
    <?php if ( $form->getObject()->Transaction->professional_id ): ?>
    <span class="professional_id">
      <?php echo $form->getObject()->Transaction->Professional->name_type ?>
      <?php echo cross_app_link_to($form->getObject()->Transaction->Professional->Organism, 'rp', 'organism/edit?id='.$form->getObject()->Transaction->Professional->organism_id) ?>
    </span>
    <?php endif ?>
  </div>
</div>
