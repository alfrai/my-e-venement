<?php echo $form->renderFormTag('',array('class'=>'ui-widget-content ui-corner-all','id'=>'criterias')) ?>
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __('Criterias') ?></h2>
    <?php echo $form->renderHiddenFields() ?>
  </div>
  <ul>
    <li class="dates" <?php if ( $ledger == 'both' && $form['manifestations']->getValue() ): ?>style="display: none"<?php endif ?>>
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
    <?php if ( $ledger == 'sales' ): ?>
    <li>
      <label for="tck_value_date_payment"><?php echo __('Display tickets from payment') ?>:</label>
      <?php echo $form['tck_value_date_payment'] ?>
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
      <?php include_partial('criterias_actions',array('ledger' => $ledger)) ?>
    </li>
  </ul>
</form>
