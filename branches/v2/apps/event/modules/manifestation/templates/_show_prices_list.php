<div class="sf_admin_form_row sf_admin_field_prices_list">
  <label><?php echo __('Prices list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Prices->count() == 0 ): ?>
      <li><?php echo __('No registered price') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Prices as $price ): ?>
    <li class="ui-corner-all">
      <?php echo $price->getFullName() ?>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>
