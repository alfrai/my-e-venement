<?php use_helper('Number') ?>
<form action="<?php echo url_for('card/order') ?>" method="post" autocomplete="off">
  <?php include_partial('index_table',array('member_card_types' => $member_card_types, 'transaction' => $sf_user->getTransaction(), 'mct' => $mct, )) ?>
  <p><input type="submit" name="submit" value="<?php echo __('Ok') ?>" /></p>
</form>

<?php include_partial('index_js') ?>
