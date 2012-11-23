<?php use_helper('Number') ?>
<div class="sf_admin_form_row">
  <label><?php echo __('Value') ?>:</label>
  <?php echo format_currency($member_card->value,'€') ?>
</div>
