  <form action="<?php echo url_for('ticket/batchCancel') ?>" method="get" class="ui-widget-content ui-corner-all batch">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Simplified complete cancellation') ?></h2>
    </div>
    <p>
      <label for="id"><?php echo __('Transaction') ?></label>
      #<input type="text" name="id" value="" autocomplete="off" />
    </p>
    <p>
      <label for="payment_method"><?php echo __('Payment method') ?></label>
      <?php
        $select = new sfWidgetFormDoctrineChoice(array(
          'model' => 'PaymentMethod',
          'add_empty' => true,
          'query' => Doctrine::getTable('PaymentMethod')->createQuery('pm')->andWhere('pm.member_card_linked = false'),
          'order_by' => array('name',''),
        ));
        echo $select->render('payment_method_id');
      ?>
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('Batch cancel') ?>" />
    </p>
  </form>
