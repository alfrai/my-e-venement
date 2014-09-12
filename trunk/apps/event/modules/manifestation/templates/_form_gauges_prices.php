<?php use_javascript('helper') ?>
<?php use_javascript('manifestation-price-gauges') ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php $manifestation = $form->getObject(); ?>
<div class="sf_admin_form_row sf_admin_table sf_admin_form_field_gauges_prices">
<div class="label ui-helper-clearfix">
  <label for="gauge_prices"><?php echo __('Prices list') ?></label>
</div>
<div class="help">
  <span class="ui-icon ui-icon-help floatleft"></span>
  <?php echo __('WARNING: those prices will be updated/deleted only if you press the <enter> key on each modification') ?>
</div>
<div class="ui-widget-content ui-corner-all">
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
          <?php if ( in_array($gauge->workspace_id, $price->Price->Workspaces->getPrimaryKeys()) ): ?>
          <table><?php echo $form ?></table>
          <?php else: ?>
          -
          <?php endif ?>
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
</div>
