<?php include_partial('flashes') ?>

<?php echo $form->renderFormTag('',array('class'=>'ui-widget-content ui-corner-all', 'id' => 'checkpoint')) ?>
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
    <li class="comment ui-corner-all sf_admin_form_row sf_admin_text <?php echo $form['ticket_id']->hasError() ? 'ui-state-error' : '' ?>">
      <label for="ticket_comment"><?php echo __('Comment') ?></label>
      <?php echo $form['comment'] ?>
    </li>
    <li class="submit">
      <label for="s"></label>
      <input type="submit" name="s" value="Ok" />
    </li>
  </ul>
</form>

<script type="text/javascript">
  $(document).ready(function(){
    if ( !$('#checkpoint #control_ticket_id').val() )
    {
      $('#checkpoint input[name="control[ticket_id]"]').focus();
      if ( $('#checkpoint #control_checkpoint_id option').length == 2 )
        $('#checkpoint #control_checkpoint_id option:last-child').attr('selected','selected');
      else if ( '<?php echo $sf_user->getAttribute('control.checkpoint_id') ?>' != '' )
        $('#checkpoint #control_checkpoint_id option[value=<?php echo $sf_user->getAttribute('control.checkpoint_id') ?>]')
          .attr('selected','selected');
    }
    else
    {
      if ( $('#checkpoint #control_checkpoint_id option').length == 2 )
        $('#checkpoint #control_checkpoint_id option:last-child').attr('selected','selected');
      else
        $('#checkpoint #control_checkpoint_id').focus();
    }
    
    $('#checkpoint #control_checkpoint_id').keypress(function(e){
      if ( e.which == 13 )
        $('#checkpoint').submit();
    });
    
    $(document).keydown(function(){
      if ( $('#checkpoint input[name="control[ticket_id]"]:focus, #control_comment:focus').length == 0 )
        $('#checkpoint input[name="control[ticket_id]"]').focus();
    });
});
</script>

