<div class="seated_plan_batch_field <?php echo $batch ?>_<?php echo $name ?>">
  
  <?php if ( isset($label) && trim($label) && !isset($button) ): ?>
    <label><?php echo $label ?></label>:
  <?php else: ?>
    <label></label>
  <?php endif ?>
  
  <<?php echo !isset($button) ? 'input' : 'button' ?>
    <?php if ( !isset($button) ): ?>
    type="<?php echo $type = isset($type) ? $type : 'text' ?>"
    <?php endif ?>
    
    name="auto_<?php echo $batch ?>[<?php echo $name ?>]"
    value="<?php echo isset($value) ? $value : '' ?>"
    
    <?php if ( !isset($size) ): ?>
    size="4"
    <?php elseif ( $size === false ): ?>
    <?php else: ?>
    size="<?php echo $size ?>"
    <?php endif ?>
    
    <?php if ( isset($attributes) ): ?>
    <?php foreach ( $attributes as $attribute => $content ): ?>
      <?php echo $attribute ?>="<?php echo $content ?>"
    <?php endforeach ?>
    <?php endif ?>
    
    <?php $class = isset($class) ? $sf_data->getRaw('class') : array() ?>
    <?php $class = !is_array($class) ? array($class) : $class ?>
    <?php if ( isset($type) && $type == 'submit' || isset($button) ) $class = $class + array('fg-button', 'ui-state-default', 'fg-button-icon-left') ?>
    <?php $classes = ''; foreach ( $class as $c ) $classes .= $c.' '; ?>
    <?php if ( $classes ): ?>
      class="<?php echo $classes ?>"
    <?php endif ?>
  
  <?php echo !isset($button) ? '/>' : '>'.$sf_data->getRaw('label').'</button>' ?>

  <?php if ( isset($with_submit) && $with_submit ): ?>
    <button
      type="submit"
      class="fg-button ui-state-default fg-button-icon-left"
      name="auto_<?php echo $batch ?>[<?php echo $name ?>_submit]"
      value="submit">
      <span class="ui-icon ui-icon-circle-check"></span>
      <?php echo isset($submit_label) ? $submit_label : __('Validate', null, 'sf_admin') ?>
    </button>
  <?php endif ?>
  
  <?php if ( isset($helper) && $helper ): ?>
  <div class="label ui-helper-clearfix">
    <div class="help">
      <span class="ui-icon ui-icon-help floatleft"></span>
      <?php echo $helper ?>
    </div>
  </div>
  <?php endif ?>
</div>
