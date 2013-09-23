<?php foreach ( $tickets as $key => $value ): ?>
<?php if ( $key != 'name' ): ?>
<?php echo '<span class="tickets"><span class="qty">'.$value.'</span><span class="price_name">'.$key.'</span>'.($show_workspaces ? ' <span class="workspace">'.$tickets['name'].'</span>' : '').'</span>'; ?>
<?php endif ?>
<?php endforeach ?>
