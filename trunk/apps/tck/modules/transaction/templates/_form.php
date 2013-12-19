<?php $display = sfConfig::get('tdp_transaction_display',array()) ?>

<?php foreach ( $display as $name => $d ): ?>
  <div class="li_fieldset" id="li_fieldset_<?php echo $name ?>">
  <?php foreach ( $d as $field => $options ): ?>
    <div id="li_transaction_field_<?php echo $field ?>" class="ui-widget-content ui-corner-all<?php echo isset($options['class']) ? ' '.$options['class'] : '' ?>">
    <?php if ( isset($form[$field]) && $form[$field] instanceof sfForm ): ?>
      <?php foreach ( $form[$field]->getJavascripts() as $js  ) use_javascript($js);  ?>
      <?php foreach ( $form[$field]->getStylesheets() as $css => $media ) use_stylesheet($css, '', 'media="'.$media.'"'); ?>
    <?php endif ?>
    <?php
      try { include_partial('form_field_'.$field, array('field' => $field, 'form' => isset($form[$field]) ? $form[$field] : NULL, 'transaction' => $transaction,)); }
      catch ( sfRenderException $e )
      { include_partial('form_field_generic', array('field' => $field, 'form' => isset($form[$field]) ? $form[$field] : NULL, 'transaction' => $transaction,)); }
    ?>&nbsp;
    </div>
  <?php endforeach ?>
  </div>
<?php endforeach ?>
<div style="clear: both"></div>
