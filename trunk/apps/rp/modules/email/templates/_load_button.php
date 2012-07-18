<?php $name = 'load'; $label = 'Load and replace content'; $help = ''; ?>
  <div class="<?php echo isset($class) ? $class : '' ?><?php $form[$name]->hasError() and print ' ui-state-error ui-corner-all' ?>">
    <div class="label ui-helper-clearfix">
      <?php echo $form[$name]->renderLabel($label) ?>

      <?php if ($help || $help = $form[$name]->renderHelp()): ?>
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          <?php echo __(strip_tags($help), array(), 'messages') ?>
        </div>
      <?php endif; ?>
    </div>

    <?php echo $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?>

    <?php if ($form[$name]->hasError()): ?>
      <div class="errors">
        <span class="ui-icon ui-icon-alert floatleft"></span>
        <?php echo $form[$name]->renderError() ?>
      </div>
    <?php endif; ?>
  </div>

<div class="sf_admin_form_row sf_admin_button sf_admin_form_field_load_button">
  <button type="submit" name="email-load-button" value="load" id="email-load-button" class="fg-button ui-state-default fg-button-icon-left">
    <span class="ui-icon ui-icon-circle-check"></span>
    Charger
  </button>
</div>

