<?php include_partial('global/ariane',array('active' => 1)) ?>
<?php use_helper('Number') ?>
<h1><?php echo __('Choose your membership card') ?> :</h1>
<form action="<?php echo url_for('card/order') ?>" method="post" autocomplete="off" class="member_card_types">
  <?php include_partial('index_table',array('member_card_types' => $member_card_types, 'transaction' => $sf_user->getTransaction(), 'mct' => $mct, )) ?>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Ok') ?>" /></p>
</form>

<?php include_partial('index_footer') ?>
<?php include_partial('index_js') ?>
