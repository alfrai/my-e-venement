<div class="sf_admin_form_row">
  <label><?php echo __('Prices') ?>:</label>
  <ul>
  <?php foreach ( $workspace->Prices as $price ): ?>
    <li><?php echo $price ?></li>
  <?php endforeach ?>
  </ul>
</div>
