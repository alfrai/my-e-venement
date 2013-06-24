<?php $elt = sfConfig::get('app_options_design',false) && sfConfig::get(sfConfig::get('app_options_design').'_active',false) ? 'span' : 'div'; ?>
<?php if ( $sf_user->hasCredential($credential) ): ?>
  <<?php echo $elt ?> class="<?php echo $class ?><?php $form[$name]->hasError() and print ' ui-state-error ui-corner-all' ?>">
    <?php if ( $sf_context->getActionName() == 'edit' ): ?>
    <div class="label ui-helper-clearfix">
    <?php endif ?>
    
      <?php echo $form[$name]->renderLabel($label) ?>

      <?php if ($help || $help = $form[$name]->renderHelp()): ?>
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          <?php echo __(strip_tags($help), array(), 'messages') ?>
        </div>
      <?php endif; ?>
    
    <?php if ( $sf_context->getActionName() == 'edit' ): ?>
    </div>
    <?php endif ?>

    <?php
      if ( $sf_context->getActionName() == 'edit'
        || in_array(sfConfig::get('app_options_design',false),array('tdp'))
        && sfConfig::get(sfConfig::get('app_options_design').'_active',false) )
      {
        echo $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes);
      }
      else if ( $form[$name]->getWidget() instanceof sfWidgetFormChoiceBase )
      {
        $choices = $form[$name]->getWidget()->getChoices();
        echo $choices[$form[$name]->getValue()] ? $choices[$form[$name]->getValue()] : '&nbsp;';
      }
      else
      {
        echo $form[$name]->getValue() ? $form[$name]->getValue() : '&nbsp;';
      }
    ?>
    
    <?php if ($form[$name]->hasError()): ?>
      <div class="errors">
        <span class="ui-icon ui-icon-alert floatleft"></span>
        <?php echo $form[$name]->renderError() ?>
      </div>
    <?php endif; ?>
  </<?php echo $elt ?>>
<?php endif ?>
