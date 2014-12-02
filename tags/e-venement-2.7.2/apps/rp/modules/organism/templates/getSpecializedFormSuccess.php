<div class="specialized-form">
  <?php echo form_tag_for($form, '@organism') ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form[$field] ?>
  <p style="display: none;"><input type="hidden" name="specialized-form" value="true" /></p>
</form>
