<?php if ( $sf_request->getParameter('cid', 0) != $product_category->id ): ?>
<?php echo link_to($product_category, 'store/index?cid='.$product_category->id) ?>
<?php endif ?>
