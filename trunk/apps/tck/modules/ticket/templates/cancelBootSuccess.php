<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all" id="cancel-tickets">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Cancelling tickets') ?></h1>
  </div>
  <form action="" method="get" class="ui-widget-content ui-corner-all cancel">
    <p>
      <label for="ticket_id"><?php echo __('Ticket') ?></label>
      #<input type="text" style="width: 120px" name="ticket_id" value="" autocomplete="off" title="ex: 289,401-407,512" />
    </p>
    <p>
      <label for=""></label>
      &nbsp;&nbsp;<input type="submit" name="" value="<?php echo __('cancel') ?>" />
    </p>
  </form>
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
  <form action="<?php echo url_for('ticket/batchCancel') ?>" method="get" class="ui-widget-content ui-corner-all batch">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
      <h2><?php echo __('Simplified complete cancel...') ?></h2>
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
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $('form input[name=ticket_id]').focus();
    $('form.cancel').submit(function(){
      if ( confirm("<?php echo __("Are you sure?",null,'sf_admin') ?> - "+$('input[type=text]').val()) )
        return true;
      else
      {
        $('#transition .close').click();
        return false;
      }
    });
    $('form.batch').submit(function(){
      if ( confirm("<?php echo __("Are you sure? You are going to replace all your payments in the original and (if it exists) cancelling transactions...") ?>") )
        return true;
      else
      {
        $('#transition .close').click();
        return false;
      }
    });
    setTimeout(function(){
      $('.sf_admin_flashes').fadeOut();
    },4000);
  });
</script>
