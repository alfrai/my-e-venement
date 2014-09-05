<?php include_partial('global/ariane', array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>
<?php include_partial('show_title', array('product' => $form->getObject())) ?>
<div class="product" id="product-<?php echo $form->getObject()->id ?>">
<?php include_partial('show_declinations', array(
  'product' => $form->getObject(),
  'declinations' => $form->getObject()->Declinations,
)) ?>
</div>
<?php include_partial('show_footer', array('product' => $form->getObject())) ?>

