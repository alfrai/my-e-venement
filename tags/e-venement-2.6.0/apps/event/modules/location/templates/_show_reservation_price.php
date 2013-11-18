<?php use_helper('Number') ?>

<div class="sf_admin_form_row" title="<?php echo __('Reservation costs') ?>">
  <label><?php echo __('Costs') ?>:</label>
  <?php echo format_currency($location->reservation_price,'€') ?>
</div>

