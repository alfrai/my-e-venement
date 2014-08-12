<div class="ranks_<?php echo $name ?>">
  
  <?php if ( isset($label) && trim($label) && !isset($button) ): ?>
    <label><?php echo $label ?></label>:
  <?php else: ?>
    <label></label>
  <?php endif ?>
  
  <<?php echo !isset($button) ? 'input' : 'button' ?>
    <?php if ( !isset($button) ): ?>
    type="<?php echo isset($type) ? $type : 'text' ?>"
    <?php endif ?>
    
    name="auto_ranks[<?php echo $name ?>]"
    value="<?php echo isset($value) ? $value : '' ?>"
    
    <?php if ( !isset($size) ): ?>
    size="4"
    <?php elseif ( $size === false ): ?>
    <?php else: ?>
    size="<?php echo $size ?>"
    <?php endif ?>
    
    <?php if ( isset($class) ): ?>
      <?php $class = !is_array($sf_data->getRaw('class')) ? array($class) : $class ?>
      <?php $classes = ''; foreach ( $class as $c ) $classes .= $c.' '; ?>
      class="<?php echo $classes ?>"
    <?php endif ?>
  
  <?php echo !isset($button) ? '/>' : '>'.$sf_data->getRaw('label').'</button>' ?>
</div>
