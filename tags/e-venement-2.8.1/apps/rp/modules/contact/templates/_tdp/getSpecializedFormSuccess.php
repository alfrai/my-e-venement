<div class="specialized-form">
  <?php echo form_tag_for($form, '@contact') ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field] ?>
</form>
