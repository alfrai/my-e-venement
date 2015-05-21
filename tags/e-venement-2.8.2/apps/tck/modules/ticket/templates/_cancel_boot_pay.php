  <form action="<?php echo url_for('ticket/pay') ?>" method="get" class="ui-widget-content ui-corner-all pay">
    <p>
      <label for="id"><?php echo __('Pay back for') ?></label>
      #<input type="text" name="id" value="<?php echo $pay ?>" autocomplete="off" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('pay') ?>" />
    </p>
  </form>
