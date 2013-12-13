<?php foreach ( sfConfig::get('tdp_transaction_selling',array()) as $id => $detail ): ?>
<div id="li_transaction_<?php echo $id ?>" class="bunch">
  <h2 class="ui-widget-header ui-corner-all"><?php echo $detail['title'] ?></h2>
  <?php include_partial('form_field_content_bunch', array(
    'form' => $form,
    'transaction' => $transaction,
    'detail' => $detail,
    'id' => $id,
  )) ?>
</div>
<?php endforeach ?>

