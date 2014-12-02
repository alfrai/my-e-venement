<?php $emails = $form->getObject()->$field ?>
<div class="sf_admin_form_row">
  <label><?php echo __($label) ?>:</label>
  <?php if ( $emails ): ?>
  <?php foreach ( explode(',',$emails) as $email ): ?>
  <a href="mailto:<?php echo htmlspecialchars(trim($email)) ?>"><?php echo htmlspecialchars($email) ?></a>
  <?php endforeach ?>
  <span class="nb">(<?php echo count(explode(',',$emails)) ?>)</span>
  <?php endif ?>
</div>
