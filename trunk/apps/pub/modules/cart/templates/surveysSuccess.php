<?php include_partial('survey_assets') ?>
<?php foreach ( $forms as $form ): ?>
  <?php include_partial('survey_form', array('form' => $form)) ?>
<?php endforeach ?>
<a href="<?php echo url_for('cart/order') ?>" class="survey-next"></a>
