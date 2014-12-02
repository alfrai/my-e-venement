<div class="sf_admin_form_row">
  <label><?php echo __('Manifestations') ?>:</label>
  <ul>
  <?php foreach ( $workspace->Manifestations as $manifestation ): ?>
    <li><?php echo $manifestation ?></li>
  <?php endforeach ?>
  </ul>
</div>
