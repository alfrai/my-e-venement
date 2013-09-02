<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_price">
  <div class="label ui-helper-clearfix">
    <label for="price_manifestation_price"><?php echo __('Price') ?></label>
  </div>
  <?php echo $form->getObject()->Price ?>
  <span style="display: none"><?php echo $form['price_id']->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?></span>
</div>
