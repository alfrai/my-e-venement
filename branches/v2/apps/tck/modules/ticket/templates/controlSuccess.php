<?php include_partial('flashes') ?>

<?php echo $form->renderFormTag('',array('class'=>'ui-widget-content ui-corner-all', 'id' => 'checkpoint')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Checkpoint') ?></h1>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul class="ui-corner-all ui-widget-content">
    <li class="checkpoint_id ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['checkpoint_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="checkpoint_id"><?php echo __('Checkpoint') ?></label>
      <?php echo $form['checkpoint_id'] ?>
    </li>
    <li class="ticket_id ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['ticket_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="ticket_id"><?php echo __('Ticket') ?></label>
      <?php echo $form['ticket_id'] ?>
    </li>
    <li class="submit">
      <label for="s"></label>
      <input type="submit" name="s" value="ok" />
    </li>
  </ul>
</form>

<script type="text/javascript">
  $(document).ready(function(){
    if ( $('#checkpoint .ui-state-error').length > 0 )
      $('#checkpoint .ui-state-error:first-child').find('input, select').focus();
    else
      $('#checkpoint input[name="control[ticket_id]"]').focus();
  });
</script>
