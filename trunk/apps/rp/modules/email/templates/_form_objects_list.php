<?php $fieldName = strtolower($str['collection']).'_list'; ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_<?php echo $fieldName ?>">
  <div class="label ui-helper-clearfix">
    <label for="email_<?php echo $fieldName ?>"><?php echo $str['title'] ?></label>
  </div>
  <div class="setbyfilter">
    <?php if ( isset($form[$fieldName]) ): ?>
      <?php echo $form[$fieldName] ?>
    <?php else: ?>
      <h3><?php echo __('Set by filter').' ('.$str['nb'].')' ?></h3>
      <ol><?php foreach ( $form->getObject()->get($str['collection']) as $object ): ?>
        <li><?php echo $object ?></li>
      <?php endforeach ?></ol>
    <?php endif ?>
  </div>
</div>
