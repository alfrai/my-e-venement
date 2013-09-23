<?php foreach ( array('email') as $fieldname ): ?>
  <p class="<?php echo $fieldname ?>">
    <?php echo $form[$fieldname]->renderLabel() ?>
    <?php echo $form[$fieldname] ?>
    <span class="error"><?php if ( isset($errors[$fieldname]) ) echo __($errors[$fieldname]) ?></span>
  </p>
<?php endforeach ?>
  <p class="submit">
    <label></label>
    <input type="submit" value="<?php echo __('Send') ?>" name="submit" />
    <?php $form->renderHiddenFields() ?>
  </p>

