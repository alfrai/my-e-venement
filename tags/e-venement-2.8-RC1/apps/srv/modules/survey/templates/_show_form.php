<?php include_partial('flashes') ?>

<?php echo $form->renderFormTag(url_for('survey/commit')) ?>
  <h2><?php echo $form->getObject()->description ?></h2>
  <?php echo $form ?>
  <p><input type="submit" name="submit" value="<?php echo __('Validate', null, 'sf_admin') ?>" /></p>
</form>

<?php foreach ( $form->getStylesheets() as $css ): ?>
<?php use_stylesheet($css) ?>
<?php endforeach ?>
<?php foreach ( $form->getJavascripts() as $js ): ?>
<?php use_javascript($js) ?>
<?php endforeach ?>
