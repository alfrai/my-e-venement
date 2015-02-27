<?php include_partial('global/ariane',array('active' => 1)) ?>
<?php include_partial('global/flashes') ?>

<?php foreach ( $pager->getResults() as $product_category ): ?>
<?php if ( $product_category->id == $sf_request->getParameter('cid') ): ?>
<h1><?php echo __('Category %%parent%% → %%name%%', array(
  '%%parent%%' => $product_category->product_category_id ? link_to($product_category->Parent, 'store/index?cid='.$product_category->Parent->id) : '',
  '%%name%%' => $product_category
)) ?></h1>
<?php endif ?>
<?php endforeach ?>

<?php include_partial('list_footer', array('pager' => $pager)) ?>
