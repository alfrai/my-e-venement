<?php
  $control = new BatchControlForm();
  $control->setDefaults(array(
    'manifestation_id' => $form->getObject()->id,
    'type' => 'control',
  ));
  echo $control->renderFormTag(cross_app_url_for('tck','ticket/batchControl'),array('class' => 'control'));
?>
  <p>
    <?php echo $control['checkpoint_id'] ?>
    <?php echo $control->renderHiddenFields(); ?>
    <input type="submit" name="submit" value="<?php echo __('Control all...') ?>" onclick="return confirm('<?php echo __('Are you sure?',null,'sf_admin') ?>');" />
  </p>
</form>

<?php
  $control = new BatchControlForm();
  $control->setDefaults(array(
    'manifestation_id' => $form->getObject()->id,
    'type' => 'cancel',
  ));
  echo $control->renderFormTag(cross_app_url_for('tck','ticket/batchControl'),array('class' => 'control'));
?>
  <p>
    <?php echo $control['checkpoint_id'] ?>
    <?php echo $control->renderHiddenFields(); ?>
    <input type="submit" name="submit" value="<?php echo __('Uncheck all tickets...') ?>" onclick="return confirm('<?php echo __('Are you sure?',null,'sf_admin') ?>');" />
  </p>
</form>
