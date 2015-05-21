<div class="sf_admin_form_row sf_admin_boolean sf_admin_form_field_can_be_empty">
  <?php if ( !$form->getObject()->isNew() ): ?>
    <label for="survey_query_show_survey"><?php echo __('Survey') ?>:</label>
    <?php echo $form->getObject()->Survey ?>
  <?php endif ?>
</div>
