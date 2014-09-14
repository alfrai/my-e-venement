<?php use_javascript('helper') ?>
<?php use_javascript('manifestation-price-gauges') ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php
  // ordering prices for every gauge in the same way
  $order = array();
  foreach ( $manifestation->Gauges as $gauge )
  foreach ( $gauge->PriceGauges as $pg )
  if (!( isset($order[$pg->price_id]) && $order[$pg->price_id] > $pg->value ))
    $order[$pg->price_id] = $pg->value;
  arsort($order);
  $others = array();
  foreach ( $sf_user->getGuardUser()->Prices as $price )
  if (!( isset($order[$price->id]) ))
    $others[$price->id] = $price->name;
  asort($others);
  $order = array_keys($order + $others);
?>
<table>
  <body>
    <?php foreach ( $manifestation->Gauges as $gauge ): ?>
    <?php
      $prices = array();
      foreach ( $gauge->PriceGauges as $pg )
        $prices[$pg->price_id] = $pg->getRawValue();
      foreach ( $sf_user->getGuardUser()->Prices as $price )
      if ( !isset($prices[$price->id]) )
      {
        $pg = new PriceGauge;
        $pg->price_id = $price->id;
        $pg->gauge_id = $gauge->id;
        $prices[$price->id] = $pg;
      }
    ?>
    <tr>
      <th><?php echo $gauge ?></th>
      <?php foreach ( $order as $id ): ?>
      <?php if ( isset($prices[$id]) ): ?>
      <?php $form = new PriceGaugeForm($prices[$id]); ?>
        <td data-submit-url="<?php echo url_for('manifestation/addGaugePrice') ?>" title="<?php echo $gauge ?>">
          <?php if ( in_array($gauge->workspace_id, $prices[$id]->Price->Workspaces->getPrimaryKeys()) ): ?>
          <table><?php echo $form ?></table>
          <?php else: ?>
          -
          <?php endif ?>
        </td>
      <?php endif ?>
      <?php endforeach ?>
    </tr>
    <?php endforeach ?>
  </tbody>
  <thead>
    <tr>
      <th></th>
      <?php $prices = array(); foreach ( $sf_user->getGuardUser()->Prices as $price ) $prices[$price->id] = $price; ?>
      <?php foreach ( $order as $id ): ?>
      <td><?php echo $prices[$id]; unset($prices[$id]) ?></td>
      <?php endforeach ?>
      <?php foreach ( $prices as $price ): ?>
      <td><?php echo $price ?></td>
      <?php endforeach ?>
    </tr>
  </thead>
</table>
<?php if (!( isset($edit) && $edit )): ?>
<div class="read-only"></div>
<?php endif ?>
