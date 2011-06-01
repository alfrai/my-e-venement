<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_manifestation">
  <div class="label ui-helper-clearfix">
    <label for="price_manifestation_manifestation"><?php echo __('Manifestation') ?></label>
  </div>
  <?php echo $form->getObject()->Manifestation ?>
  <span style="display: none"><?php echo $form['manifestation_id']->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?></span>
</div>
