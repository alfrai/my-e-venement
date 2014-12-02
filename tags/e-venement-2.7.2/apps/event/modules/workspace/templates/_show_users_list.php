<div class="sf_admin_form_row">
  <label><?php echo __('Users') ?>:</label>
  <ul>
  <?php foreach ( $workspace->Users as $user ): ?>
    <li><?php echo $user ?></li>
  <?php endforeach ?>
  </ul>
</div>
