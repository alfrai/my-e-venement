<?php use_helper('Number') ?>
<?php use_helper('Slug') ?>
<?php
  // calculating the quantity of products already in the cart
  $prices = $products = array();
  foreach ( $sf_user->getTransaction()->BoughtProducts as $bp )
  {
    if ( $bp->Declination->product_id == $declination->product_id )
      $products[] = $bp;
    if ( $bp->product_declination_id == $declination->id )
    {
      if ( !isset($prices[$bp->price_id]) )
        $prices[$bp->price_id] = 0;
      $prices[$bp->price_id]++;
    }
  }
?>
<table class="prices">
<?php if ( $declination->Product->PriceProducts->count() > 0 ): ?>
<tbody>
<?php foreach ( $declination->Product->PriceProducts as $pp ): ?>
<?php if ( $pp->Price->PricePOS->count() > 0 ): ?>
  <tr data-price-id="<?php echo $pp->price_id ?>">
    <td class="price">
      <?php echo $pp->Price->description ? $pp->Price->description : $pp->Price ?>
    </td>
    <td class="value"><?php echo format_currency($pp->value,'€') ?></td>
    <td class="quantity">
      <form method="post" action="<?php echo url_for('store/mod') ?>" target="_blank" class="price_qty">
        <input type="hidden" name="store[declination_id]" value="<?php echo $declination->id ?>" />
        <input type="hidden" name="store[price_id]" value="<?php echo $pp->Price->id ?>" />
        <select name="store[qty]">
          <?php foreach ( range(0, sfConfig::get('app_store_max_per_product', 9) - count($products)) as $val ): ?>
            <option <?php echo isset($prices[$pp->price_id]) && $prices[$pp->price_id] == $val ? 'selected="selected"' : '' ?>>
              <?php echo $val ?>
            </option>
          <?php endforeach ?>
        </select>
      </form>
    </td>
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
      <form method="get" action="<?php echo url_for('cart/show') ?>" class="cart">
        <input type="submit" name="submit" value="<?php echo __('Cart') ?>" />
      </form>
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
