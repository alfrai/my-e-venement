  <?php echo $form->renderHiddenFields() ?>
  <?php foreach ( $form->getWidgetSchema()->getPositions() as $name ): ?>
  <?php if ( !($form[$name]->getWidget() instanceof sfWidgetFormInputHidden) ): ?>
  <p class="<?php echo $name ?> field <?php if ( isset($errors[$name]) ) echo 'error' ?>">
    <?php echo $form[$name]->renderLabel() ?>
    <span class="<?php echo $name ?>"><?php echo $form[$name] ?></span>
    <span class="error"><?php if ( isset($errors[$name]) ) echo __($errors[$name]) ?></span>
  </p>
  <?php endif ?>
  <?php endforeach ?>
