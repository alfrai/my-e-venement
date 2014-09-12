<?php use_javascript('helper') ?>
<?php use_javascript('manifestation-price-gauges') ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php $manifestation = $form->getObject(); ?>
<div class="sf_admin_form_row sf_admin_table sf_admin_form_field_gauges_prices">
<table>
  <body>
    <?php foreach ( $manifestation->Gauges as $gauge ): ?>
    <?php
      $prices = array();
      foreach ( $gauge->PriceGauges as $pg )
        $prices[$pg->price_id] = $pg;
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
      <?php foreach ( $prices as $price ): ?>
      <?php $form = new PriceGaugeForm($price); ?>
        <td data-submit-url="<?php echo url_for('manifestation/addGaugePrice') ?>" title="<?php echo $gauge ?>">
          <table><?php echo $form ?></table>
        </td>
      <?php endforeach ?>
    </tr>
    <?php endforeach ?>
  </tbody>
  <thead>
    <tr>
      <th></th>
      <?php foreach ( $sf_user->getGuardUser()->Prices as $price ): ?>
      <td><?php echo $price ?></td>
      <?php endforeach ?>
    </tr>
  </thead>
</table>
</div>
