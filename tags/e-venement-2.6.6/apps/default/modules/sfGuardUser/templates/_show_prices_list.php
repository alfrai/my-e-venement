<div class="sf_admin_form_row">
  <label><?php echo __('Prices list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->Prices as $p ): ?>
    <li><?php echo $p ?></li>
    <?php endforeach ?>
  </ul>
</div>

