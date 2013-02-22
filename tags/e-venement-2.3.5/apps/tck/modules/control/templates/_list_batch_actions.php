<li class="sf_admin_batch_actions_choice">
  <?php if ( $sf_user->hasCredential('tck-control-admin') ): ?>
  <select name="batch_action">
    <option value=""><?php echo __('Choose an action', array(), 'sf_admin') ?></option>
          <option value="batchDelete"><?php echo __('Delete', array(), 'sf_admin') ?></option>      </select>
  <?php $form = new BaseForm(); if ($form->isCSRFProtected()): ?>
    <input type="hidden" name="<?php echo $form->getCSRFFieldName() ?>" value="<?php echo $form->getCSRFToken() ?>" />
  <?php endif; ?>

  <!--<input type="submit" value="<?php echo __('go', array(), 'sf_admin') ?>" class="fg-button ui-state-default ui-corner-right"/>-->
  <input type="submit" value="<?php echo __('go', array(), 'sf_admin') ?>" class="ui-button ui-state-default ui-corner-all"/>
  <!--<button type="submit" class="fg-button ui-state-default fg-button-icon-right ui-corner-all">
    <span class="ui-icon ui-icon-check"></span>
    <?php echo __('go', array(), 'sf_admin') ?>
  </button>-->
  <?php endif ?>
</li>
