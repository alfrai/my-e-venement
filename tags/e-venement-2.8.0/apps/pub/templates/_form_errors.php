<?php if ( $form->getErrorSchema()->count() > 0 ): ?>
<div class="errors"><?php echo $form->getErrorSchema() ?></div>
<?php endif; $errors = $form->getErrorSchema()->getErrors() ?>
