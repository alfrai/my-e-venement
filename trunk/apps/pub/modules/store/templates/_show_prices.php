<?php use_helper('Number') ?>
<?php use_helper('Slug') ?>
<table class="prices">
<?php if ( $declination->Product->PriceProducts->count() > 0 ): ?>
<tbody>
<?php foreach ( $declination->Product->PriceProducts as $pp ): ?>
<?php if ( $pp->Price->PricePOS->count() > 0 ): ?>
  <?php
    // calculating the quantity of products already in the cart
    $products = array();
    foreach ( $sf_user->getTransaction()->BoughtProducts as $bp )
    if ( $bp->product_declination_id == $declination->id )
      $products[] = $bp;
  ?>
  <tr data-price-id="<?php echo $pp->price_id ?>">
    <td class="price">
      <?php echo $pp->Price->description ? $pp->Price->description : $pp->Price ?>
    </td>
    <td class="value"><?php echo format_currency($pp->value,'€') ?></td>
    <td class="quantity"><?php echo sfConfig::get('app_store_max_per_product', 9) - count($products) ?></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
<?php endif ?>
<?php endforeach ?>
</tbody>
<?php endif ?>
<tfoot>
  <tr>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
  <tr>
    <td colspan="4" class="submit">
      <input type="submit" name="submit" value="<?php echo __('Cart') ?>" />
    </td>
  </td>
</tfoot>
<thead>
  <tr>
    <td class="price"><?php echo __('Price') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="quantity"><?php echo __('Quantity') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>
