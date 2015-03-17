<?php $errors = $form->getErrorSchema()->getErrors() ?>
<ul class="errors">
<?php foreach ( $errors as $key => $error ) if ( !in_array($key, $form->getWidgetSchema()->getPositions()) ): ?>
  <li><?php echo __($error) ?></li>
<?php endif ?>
</ul>
  <?php echo $form->renderHiddenFields() ?>
  <?php foreach ( $form->getWidgetSchema()->getPositions() as $name ): ?>
  <?php if ( !($form[$name]->getWidget() instanceof sfWidgetFormInputHidden) ): ?>
  <<?php echo $name != 'special_groups_list' ? 'p' : 'div' ?> class="<?php echo $name ?> field <?php if ( isset($errors[$name]) ) echo 'error' ?>">
    <?php echo $form[$name]->renderLabel() ?>
    <span class="<?php echo $name ?>"><?php echo $form[$name] ?></span>
    <span class="error"><?php if ( isset($errors[$name]) ) echo __($errors[$name]) ?></span>
  </<?php echo $name != 'special_groups_list' ? 'p' : 'div' ?>>
    <?php elseif ( $name == 'special_groups_list' ): ?>
  <?php endif ?>
  <?php endforeach ?>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
