<?php foreach ( $tickets as $key => $value ): ?>
<?php if ( $key != 'name' ): ?>
  <span class="tickets"><span class="qty"><?php echo $value ?></span>
  <span class="price_name"><?php echo $key ?></span>
  <?php if ( $show_workspaces ): ?><span class="workspace workspace-<?php ?>"><?php echo $tickets['name'] ?></span><?php endif ?>
</span>
<?php endif ?>
<?php endforeach ?>
