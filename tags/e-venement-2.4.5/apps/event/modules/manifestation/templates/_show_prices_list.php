<?php use_helper('Number') ?>
<div class="sf_admin_form_row sf_admin_field_prices_list">
  <label><?php echo __('Prices list') ?>:</label>
  <table class="ui-corner-all ui-widget-content"><tbody>
    <?php if ( $manifestation->Prices->count() == 0 ): ?>
      <tr><td><?php echo __('No registered price') ?></td></tr>
    <?php else: ?>
    <?php
      $prices = array();
      foreach ( $manifestation->PriceManifestations as $price )
        $prices[str_pad($price->value, 11, 0, STR_PAD_LEFT).$price->Price->name] = $price;
      ksort($prices);
      $prices = array_reverse($prices);
    ?>
    <?php foreach ( $prices as $price ): ?>
    <tr>
      <td class="name"><?php echo $price->Price ?></td>
      <td class="description"><?php echo $price->Price->description ?></td>
      <td class="value"><?php echo format_currency($price->value,'€') ?></td>
    </tr>
    <?php endforeach ?>
    <?php endif ?>
  </table>
</div>
