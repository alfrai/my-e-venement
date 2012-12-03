<?php use_helper('Number') ?>
<table class="prices">
<?php if ( $gauge->Manifestation->PriceManifestations->count() > 0 ): ?>
<tbody>
<?php foreach ( $gauge->Manifestation->PriceManifestations as $pm ): ?>
  <?php $form->setPriceId($pm->price_id)->setQuantity($pm->Price->Tickets->count()) ?>
  <tr>
    <td class="price">
      <?php echo $pm->Price->description ?>
      <?php echo $form->renderHiddenFields() ?>
    </td>
    <td class="value"><?php echo format_currency($pm->value,'€') ?></td>
    <td class="quantity"><?php echo $form['quantity'] ?></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
<?php endforeach ?>
<tbody>
<?php endif ?>
<tfoot>
  <tr>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,'€') ?></td>
  </tr>
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
