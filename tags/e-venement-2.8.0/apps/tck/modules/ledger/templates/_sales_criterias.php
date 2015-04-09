<?php $criterias = $form->getValues() ?>
<?php if ( $criterias['not-yet-printed'] || $criterias['tck_value_date_payment'] ): ?>
<div class="ui-widget-content ui-corner-all criterias" id="extra-criterias">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Extra criterias") ?></h2>
  </div>
  <ul>
    <?php if ( $criterias['not-yet-printed'] ): ?>
    <li><?php echo __('Display not-yet-printed tickets') ?></li>
    <?php endif ?>
    <?php if ( $criterias['tck_value_date_payment'] ): ?>
    <li><?php echo __('Display tickets from payment date') ?></li>
    <?php endif ?>
  </ul>
</div>
<?php endif ?>

