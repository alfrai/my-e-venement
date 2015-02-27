<?php if ( $sf_request->getParameter('cid', 0) != $product_category->id ): ?>
<?php if ( $product_category->Children->count() > 0 ): ?>
<ul>
  <?php foreach ( $product_category->Children as $child ): ?>
  <li><?php echo link_to($child, 'store/index?cid='.$child->id) ?></li>
  <?php endforeach ?>
</ul>
<?php endif ?>
<?php endif ?>
