<?php echo $form->renderFormTag(url_for('cart/commitSurvey'), array('target' => '_blank', 'class' => 'pub-survey')) ?>
  <?php echo $form ?>
  <?php
    foreach ( $form->getStylesheets() as $css )
      use_stylesheet($css);
    foreach ( $form->getJavascripts() as $js )
      use_javascript($js);
  ?>
  <p><input type="submit" name="submit" value="<?php echo __('Validate', null, 'sf_admin') ?>" /></p>
</form>
