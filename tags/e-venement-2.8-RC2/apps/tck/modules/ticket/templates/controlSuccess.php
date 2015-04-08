<?php include_partial('flashes') ?>

<?php echo $form->renderFormTag('',array('class'=>'ui-widget-content ui-corner-all', 'id' => 'checkpoint', 'target' => '_blank')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint') ?></h1>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul class="ui-corner-all ui-widget-content">
    <li class="ticket_id ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['ticket_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="ticket_id"><?php echo __('Ticket') ?></label>
      <?php echo $form['ticket_id'] ?>
      <?php if ( sfConfig::get('app_tickets_id') == 'id' ): ?>
      <?php include_partial('global/capslock') ?>
      <?php endif ?>
    </li>
    <li class="checkpoint_id ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['checkpoint_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="checkpoint_id"><?php echo __('Checkpoint') ?></label>
      <?php echo $form['checkpoint_id'] ?>
    </li>
    <li class="control-comment ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['ticket_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="ticket_comment"><?php echo __('Comment') ?></label>
      <?php echo $form['comment'] ?>
    </li>
    <li class="submit">
      <label for="s"></label>
      <input type="submit" name="s" value="Ok" />
    </li>
  </ul>
  <div style="display: none;"
    class="settings"
    data-checkpoint-id="<?php echo $sf_user->getAttribute('control.checkpoint_id') ?>"
    data-transaction-label="<?php echo __('Transaction') ?>"
    data-ticket-label="<?php echo __('Ticket') ?>"
    data-cancel-label="<?php echo __('Cancellation') ?>"
    data-cancel-confirmation="<?php echo __('Are you sure?', null, 'sf_admin') ?>"
    data-cancel-success="<?php echo __('Control deleted.') ?>"
    data-cancel-error="<?php echo __('You cannot remove this control, sorry.') ?>"
  ></div>
</form>
