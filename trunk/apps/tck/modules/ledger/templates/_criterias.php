<?php echo $form->renderFormTag('',array('class'=>'ui-widget-content ui-corner-all','id'=>'criterias')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __('Criterias') ?></h2>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul>
    <li class="dates">
      <label for="dates"><?php echo __('Dates:') ?></label>
      <?php echo $form['dates'] ?>
    </li>
    <li class="users">
      <label for="users"><?php echo __('Users:') ?></label>
      <?php echo $form['users'] ?>
    </li>
    <?php if ( $ledger == 'sales' ): ?>
    <li>
      <label for="not-yet-printed"><?php echo __('Display not-yet-printed tickets') ?>:</label>
      <?php echo $form['not-yet-printed'] ?>
    </li>
    <?php endif ?>
    <li class="workspaces">
      <label for="workspaces"><?php echo __('Workspaces') ?>:</label>
      <?php echo $form['workspaces'] ?>
    </li>
    <?php if ( $ledger == 'both' ): ?>
    <li class="manifestations">
      <label for="manifestations"><?php echo __('Manifestations:') ?></label>
      <?php echo $form['manifestations'] ?>
    </li>
    <?php endif ?>
    <li class="submit">
      <input type="submit" name="s" value="ok" />
      <?php if ( $sf_user->hasCredential('tck-ledger-'.($ledger == 'cash' ? 'sales' : 'cash')) ): ?>
      <?php echo link_to(__('Switch ledger...'), $ledger == 'cash' ? 'ledger/sales' : 'ledger/cash') ?>
      <br/>
      <?php echo link_to(__('Detailed Ledger',array(),'menu'), 'ledger/both') ?>
      <?php endif ?>
    </li>
  </ul>
</form>
