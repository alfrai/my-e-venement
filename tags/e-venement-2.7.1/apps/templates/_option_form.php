  <?php $form->addCSRFProtection() ?>
  <input type="hidden" name="<?php echo $form->getCSRFFieldName() ?>" value="<?php echo $form->getCSRFToken() ?>" />
  <?php foreach ( $form->widgets as $fname => $fieldset ): if ( $fname != 'out' ): ?>
  <fieldset class="<?php echo $fname ?> check">
    <div class="ui-corner-all ui-widget-content">
      <?php if ( $fname ): ?>
      <div class="fg-toolbar ui-widget-header ui-corner-all">
        <h2><?php echo __(strtoupper(substr($fname,0,1)).substr($fname,1),array(),'messages') ?></h2>
      </div>
      <?php endif ?>
      <?php foreach ( $fieldset as $name => $value ): ?>
      <div class="line ui-corner-all sf_admin_form_row sf_admin_field_<?php echo $form[$name]->getName() ?> <?php echo $form[$name]->hasError() ? 'ui-state-error' : '' ?>">
        <?php if ( is_array($value) ): ?><span class="helper">&nbsp;<?php echo $value['helper'] ?></span><?php endif ?>
        <?php echo $form[$name] ?>
        <?php echo $form[$name]->renderLabel() ?>
        <?php if ($form[$name]->hasError()): ?>
        <div class="errors">
          <span class="ui-icon ui-icon-alert floatleft"></span>
          <?php echo $form[$name]->renderError() ?>
        </div>
        <?php endif; ?>
        <div style="clear: both"></div>
      </div>
      <?php endforeach ?>
    </div>
  </fieldset>
  <?php endif; endforeach ?>
