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
    <li class="submit">
      <input type="submit" name="s" value="ok" />
      <?php
        if ( $sf_user->hasCredential('tck-ledger-'.($ledger == 'cash' ? 'sales' : 'cash') )
          link_to(__('Switch ledger...'), $ledger == 'cash' ? 'ledger/sales' : 'ledger/cash')
      ?>
    </li>
  </ul>
</form>
