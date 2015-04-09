<div class="go-to-cart">
  <a href="<?php echo url_for('transaction/show?id='.$sf_user->getTransaction()->id) ?>">
    <button><?php echo __('Cart') ?></button>
  </a>
</div>
<?php include_partial('event/list_footer') ?>
