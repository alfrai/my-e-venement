<?php include_partial('global/ariane',array('active' => 2)) ?>
<?php use_helper('Number') ?>
<h1><?php echo __('Choose your membership card') ?> :</h1>
<form action="<?php echo url_for('card/order') ?>" method="post" autocomplete="off">
  <?php include_partial('index_table',array('member_card_types' => $member_card_types, 'transaction' => $sf_user->getTransaction(), 'mct' => $mct, )) ?>
  <p><input type="submit" name="submit" value="<?php echo __('Ok') ?>" /></p>
</form>

<?php include_partial('index_footer') ?>
<?php include_partial('index_js') ?>
