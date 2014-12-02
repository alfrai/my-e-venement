<?php include_partial('global/ariane',array('active' => 0)) ?>
<?php include_partial('edit_header') ?>
<?php if ( $form->getErrorSchema()->count() > 0 ): ?>
<ul class="errors">
  <?php foreach ( $form->getErrorSchema()->getErrors() as $name => $error ): ?>
  <?php if ( !isset($form[$name]) ): ?>
    <li class="error error-<?php echo $error->getCode() ?>">
      <?php echo __($error) ?>
    </li>
  <?php endif ?>
  <?php endforeach ?>
</ul>
<?php endif; $errors = $form->getErrorSchema()->getErrors() ?>
<?php echo $form->renderFormTag(url_for('contact/update'), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
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
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Update') ?>" /></p>
</form>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
